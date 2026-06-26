<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ---- Product stats (single aggregated query, cached briefly) ----
        $productStats = Cache::remember('admin.dashboard.product_stats', 60, function () {
            return Product::query()->selectRaw(
                'COUNT(*) as total_products,'
                . ' COALESCE(SUM(stock), 0) as total_stock,'
                . ' COALESCE(SUM(COALESCE(sale_price, price) * stock), 0) as total_stock_value,'
                . ' SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count,'
                . ' SUM(CASE WHEN stock > 0 AND stock <= 5 THEN 1 ELSE 0 END) as low_stock_count'
            )->first();
        });

        $totalProducts   = (int) ($productStats->total_products ?? 0);
        $totalStock      = (int) ($productStats->total_stock ?? 0);
        $totalStockValue = (float) ($productStats->total_stock_value ?? 0);
        $outOfStockCount = (int) ($productStats->out_of_stock_count ?? 0);
        $lowStockCount   = (int) ($productStats->low_stock_count ?? 0);

        $totalCategories = Cache::remember('admin.dashboard.total_categories', 300, fn () => Category::count());

        $categoryStats = Cache::remember('admin.dashboard.category_stats', 300, function () {
            return Product::query()
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('categories.name as category_name', DB::raw('count(*) as count'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('count')
                ->limit(8)
                ->get();
        });

        // ---- Orders / sales / customers (guard against missing tables on fresh DB) ----
        $totalSales      = 0.0;
        $completedOrders = 0;
        $totalCustomers  = 0;
        $recentOrders    = collect();
        $revenueSeries   = array_fill(0, 6, 0);
        $ordersSeries    = array_fill(0, 6, 0);
        $revenueLabels   = ['يناير','فبراير','مارس','أبريل','مايو','يونيو'];

        if (Schema::hasTable('orders')) {
            $paidStatuses = ['paid', 'shipped', 'delivered'];

            $totalSales = (float) Order::whereIn('status', $paidStatuses)
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('total');

            $completedOrders = (int) Order::where('status', 'delivered')->count();

            $recentOrders = Order::query()
                ->with('user:id,name')
                ->latest()
                ->limit(6)
                ->get(['id', 'order_number', 'user_id', 'total', 'status', 'created_at']);

            // آخر 6 أشهر: الأرباح المحققة + الطلبات المحجوزة
            $monthsStart = now()->startOfMonth()->subMonths(5);
            $revRows = Order::whereIn('status', $paidStatuses)
                ->where('created_at', '>=', $monthsStart)
                ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as m, SUM(total) as t")
                ->groupBy('m')->pluck('t', 'm');
            $ordRows = Order::where('created_at', '>=', $monthsStart)
                ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as m, COUNT(*) as c")
                ->groupBy('m')->pluck('c', 'm');

            $revenueSeries = [];
            $ordersSeries  = [];
            $revenueLabels = [];
            $arMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            for ($i = 0; $i < 6; $i++) {
                $d = $monthsStart->copy()->addMonths($i);
                $k = $d->format('Y-m');
                $revenueSeries[] = (float) ($revRows[$k] ?? 0);
                $ordersSeries[]  = (int)   ($ordRows[$k] ?? 0);
                $revenueLabels[] = $arMonths[(int)$d->format('n') - 1];
            }
        } else {
            $ordersSeries = array_fill(0, 6, 0);
            $revenueSeries = array_fill(0, 6, 0);
            $revenueLabels = ['يناير','فبراير','مارس','أبريل','مايو','يونيو'];
        }

        if (Schema::hasTable('users')) {
            $totalCustomers = (int) User::count();
        }

        $lowStockProducts = Product::query()
            ->with('category:id,name')
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'name', 'stock', 'category_id']);

        $recentProducts = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sale_price', 'stock', 'category_id', 'created_at'])
            ->with(['category:id,name'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalStock',
            'totalStockValue',
            'outOfStockCount',
            'lowStockCount',
            'totalCategories',
            'categoryStats',
            'recentProducts',
            'totalSales',
            'completedOrders',
            'totalCustomers',
            'recentOrders',
            'lowStockProducts',
            'revenueSeries',
            'ordersSeries',
            'revenueLabels'
        ));
    }
}
