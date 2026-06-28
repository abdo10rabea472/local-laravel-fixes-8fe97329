<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Analytics dashboard — sales / revenue / top products. */
    public function analytics(Request $request)
    {
        $range = (int) $request->integer('days', 30);
        $range = in_array($range, [7,30,90,365], true) ? $range : 30;
        $from = now()->subDays($range)->startOfDay();

        $cacheKey = "admin.reports.analytics.$range";

        $data = Cache::remember($cacheKey, 120, function () use ($from, $range) {
            $paidStatuses = ['paid','shipped','delivered'];

            // KPI snapshot in 1 query
            $kpi = Order::query()
                ->where('created_at','>=',$from)
                ->selectRaw('
                    COUNT(*) as orders_count,
                    SUM(CASE WHEN status IN ("paid","shipped","delivered") THEN total ELSE 0 END) as revenue,
                    SUM(CASE WHEN status IN ("paid","shipped","delivered") THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN status = "pending"   THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_count,
                    SUM(CASE WHEN status = "refunded"  THEN 1 ELSE 0 END) as refunded_count
                ')->first();

            // Daily revenue series
            $daily = Order::query()
                ->where('created_at','>=',$from)
                ->whereIn('status', $paidStatuses)
                ->selectRaw('DATE(created_at) as d, SUM(total) as revenue, COUNT(*) as orders')
                ->groupBy('d')->orderBy('d')->get();

            $series = [];
            for ($i = $range - 1; $i >= 0; $i--) {
                $d = now()->subDays($i)->toDateString();
                $row = $daily->firstWhere('d', $d);
                $series[] = [
                    'date' => $d,
                    'revenue' => (float) ($row->revenue ?? 0),
                    'orders' => (int) ($row->orders ?? 0),
                ];
            }

            // Top selling products
            $topProducts = DB::table('order_items')
                ->join('orders','orders.id','=','order_items.order_id')
                ->whereIn('orders.status', $paidStatuses)
                ->where('orders.created_at','>=',$from)
                ->selectRaw('order_items.product_name as name, SUM(order_items.quantity) as qty, SUM(order_items.line_total) as revenue')
                ->groupBy('order_items.product_name')
                ->orderByDesc('qty')
                ->limit(10)
                ->get();

            // Status breakdown for donut chart
            $statusBreakdown = Order::query()
                ->where('created_at','>=',$from)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')->get();

            return compact('kpi','series','topProducts','statusBreakdown');
        });

        $data['range'] = $range;
        return view('admin.reports.analytics', $data);
    }

    /** Inventory: low + out of stock and movement counts. */
    public function inventory(Request $request)
    {
        $threshold = $request->integer('threshold');

        $low = Product::query()
            ->select(['id','name','sku','stock','low_stock_threshold','price','sale_price'])
            ->with('category:id,name')
            ->where('stock','>',0)
            ->when($threshold,
                fn($q) => $q->where('stock','<=', $threshold),
                fn($q) => $q->whereColumn('stock','<=','low_stock_threshold'))
            ->orderBy('stock')
            ->paginate(25, ['*'], 'low')
            ->withQueryString();

        $out = Product::query()
            ->select(['id','name','sku','stock','low_stock_threshold','price','sale_price'])
            ->with('category:id,name')
            ->where('stock', 0)
            ->orderBy('name')
            ->paginate(25, ['*'], 'out')
            ->withQueryString();

        $stats = Cache::remember('admin.reports.inventory.stats', 120, function () {
            return [
                'total'    => Product::count(),
                'out'      => Product::where('stock', 0)->count(),
                'low'      => Product::whereColumn('stock','<=','low_stock_threshold')->where('stock','>',0)->count(),
                'value'    => (float) Product::sum(DB::raw('stock * COALESCE(sale_price, price)')),
                'units'    => (int) Product::sum('stock'),
                'movements_30d' => StockMovement::where('created_at','>=', now()->subDays(30))->count(),
            ];
        });

        // Top movement products in last 30 days
        $topMovers = StockMovement::query()
            ->select('product_id', DB::raw('SUM(ABS(quantity_change)) as movement'))
            ->where('created_at','>=', now()->subDays(30))
            ->groupBy('product_id')
            ->orderByDesc('movement')
            ->limit(10)
            ->with('product:id,name,sku,stock')
            ->get();

        return view('admin.reports.inventory', compact('low','out','stats','topMovers','threshold'));
    }

    /** Coupons usage and revenue impact. */
    public function coupons(Request $request)
    {
        $coupons = Coupon::query()
            ->withCount('redemptions')
            ->withSum('redemptions as total_discount', 'discount_amount')
            ->withSum('redemptions as total_revenue', 'order_total')
            ->orderByDesc('redemptions_count')
            ->paginate(25);

        $totals = Cache::remember('admin.reports.coupons.totals', 120, function () {
            return DB::table('coupon_redemptions')->selectRaw('
                COUNT(*) as redemptions,
                COALESCE(SUM(discount_amount), 0) as discount,
                COALESCE(SUM(order_total), 0)    as revenue
            ')->first();
        });

        // Top coupons by revenue impact
        $top = Coupon::query()
            ->withCount('redemptions')
            ->withSum('redemptions as total_discount', 'discount_amount')
            ->orderByDesc('redemptions_count')
            ->limit(8)
            ->get();

        return view('admin.reports.coupons', compact('coupons','totals','top'));
    }

    /** تقرير المبيعات التفصيلي مع فلترة + تصدير CSV. */
    public function sales(Request $request)
    {
        $from = $request->date('from') ?: now()->subDays(30)->startOfDay();
        $to   = $request->date('to')   ?: now()->endOfDay();
        $status = $request->string('status')->value();

        $paidStatuses = ['paid','shipped','delivered'];

        $base = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($status, fn($q) => $q->where('status', $status), fn($q) => $q->whereIn('status', $paidStatuses));

        // KPIs
        $kpi = (clone $base)->selectRaw('
            COUNT(*) as orders,
            COALESCE(SUM(subtotal),0)        as subtotal,
            COALESCE(SUM(discount_total),0)  as discount,
            COALESCE(SUM(shipping_total),0)  as shipping,
            COALESCE(SUM(tax_total),0)       as tax,
            COALESCE(SUM(total),0)           as revenue,
            COALESCE(AVG(total),0)           as aov
        ')->first();

        // سلسلة يومية
        $daily = (clone $base)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('d')->orderBy('d')->get();

        // طرق الدفع
        $byPayment = (clone $base)
            ->selectRaw('COALESCE(payment_method, "—") as method, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('method')->orderByDesc('revenue')->get();

        // أفضل المنتجات في الفترة
        $topProducts = DB::table('order_items')
            ->join('orders','orders.id','=','order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->when($status,
                fn($q) => $q->where('orders.status', $status),
                fn($q) => $q->whereIn('orders.status', $paidStatuses))
            ->selectRaw('order_items.product_name as name,
                         SUM(order_items.quantity)   as qty,
                         SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('revenue')
            ->limit(15)->get();

        // تصدير CSV
        if ($request->get('export') === 'csv') {
            $filename = 'sales-'.now()->format('Ymd-His').'.csv';
            $rows = $daily;
            return response()->streamDownload(function () use ($rows) {
                $h = fopen('php://output', 'w');
                fwrite($h, "\xEF\xBB\xBF");
                fputcsv($h, ['التاريخ','عدد الطلبات','الإيرادات']);
                foreach ($rows as $r) fputcsv($h, [$r->d, $r->orders, number_format((float)$r->revenue, 2, '.', '')]);
                fclose($h);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        return view('admin.reports.sales', [
            'kpi' => $kpi, 'daily' => $daily, 'byPayment' => $byPayment,
            'topProducts' => $topProducts,
            'from' => $from->toDateString(), 'to' => $to->toDateString(),
            'status' => $status, 'statuses' => Order::STATUSES,
        ]);
    }
}

