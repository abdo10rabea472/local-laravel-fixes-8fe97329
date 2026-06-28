<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class OffersController extends Controller
{
    public function index()
    {
        $products = Product::with(['category:id,name', 'images:id,product_id,path'])
            ->where('status', true)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->orderByRaw('((price - sale_price) / price) DESC')
            ->paginate(20);

        $coupons = collect();
        if (Schema::hasTable('coupons')) {
            $coupons = Coupon::active()
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        }

        return view('pages.offers', compact('products', 'coupons'));
    }
}
