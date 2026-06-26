<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $filter = $request->string('filter')->toString(); // all|low|out
        $search = trim((string) $request->get('q', ''));

        $query = Product::query()
            ->select(['id','name','sku','stock','low_stock_threshold','price','sale_price','category_id'])
            ->with('category:id,name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($filter === 'low') {
            $query->whereColumn('stock', '<=', 'low_stock_threshold')->where('stock', '>', 0);
        } elseif ($filter === 'out') {
            $query->where('stock', 0);
        }

        $products = $query->orderBy('stock')->paginate(25)->withQueryString();

        $stats = [
            'total'   => Product::count(),
            'out'     => Product::where('stock', 0)->count(),
            'low'     => Product::whereColumn('stock', '<=', 'low_stock_threshold')->where('stock', '>', 0)->count(),
            'value'   => (float) Product::sum(DB::raw('stock * COALESCE(sale_price, price)')),
        ];

        return view('admin.stock.index', compact('products', 'stats', 'filter', 'search'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'stock' => ['required','integer','min:0'],
            'low_stock_threshold' => ['nullable','integer','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        if ((int) $data['stock'] !== (int) $product->stock) {
            $this->stock->setAbsolute(
                $product,
                (int) $data['stock'],
                'manual',
                $data['note'] ?? null,
                'admin',
                Auth::guard('admin')->id()
            );
        }

        if (array_key_exists('low_stock_threshold', $data) && $data['low_stock_threshold'] !== null) {
            $product->update(['low_stock_threshold' => $data['low_stock_threshold']]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $product->refresh();
            return response()->json([
                'ok' => true,
                'stock' => $product->stock,
                'low_stock_threshold' => $product->low_stock_threshold,
            ]);
        }

        return back()->with('success', 'تم تحديث المخزون بنجاح.');
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'updates' => ['required','array','min:1'],
            'updates.*.id' => ['required','integer','exists:products,id'],
            'updates.*.stock' => ['required','integer','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        $adminId = Auth::guard('admin')->id();
        $changed = 0;

        DB::transaction(function () use ($data, $adminId, &$changed) {
            $ids = collect($data['updates'])->pluck('id')->all();
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            foreach ($data['updates'] as $row) {
                $p = $products[$row['id']] ?? null;
                if (!$p) continue;
                if ((int) $p->stock === (int) $row['stock']) continue;
                $this->stock->setAbsolute(
                    $p,
                    (int) $row['stock'],
                    'bulk_update',
                    $data['note'] ?? 'تحديث جماعي',
                    'admin',
                    $adminId
                );
                $changed++;
            }
        });

        return response()->json(['ok' => true, 'changed' => $changed]);
    }

    public function history(Request $request)
    {
        $productId = $request->integer('product_id');
        $type = $request->string('type')->toString();

        $movements = StockMovement::query()
            ->with('product:id,name,sku')
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        $products = Product::select('id','name')->orderBy('name')->limit(500)->get();

        return view('admin.stock.history', compact('movements', 'products', 'productId', 'type'));
    }
}
