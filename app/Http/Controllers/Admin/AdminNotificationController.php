<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Review;
use Illuminate\Support\Facades\Schema;

class AdminNotificationController extends Controller
{
    /** بث آخر الإشعارات (طلبات/مخزون/مرتجعات/تقييمات) للجرس في الـ Topbar. */
    public function feed()
    {
        $items = [];

        // طلبات جديدة (آخر 24 ساعة) قيد الانتظار
        if (Schema::hasTable('orders')) {
            $pendingOrders = Order::where('status', 'pending')
                ->where('created_at', '>=', now()->subDay())
                ->latest()->limit(5)->get(['id', 'order_number', 'total', 'currency', 'created_at']);
            foreach ($pendingOrders as $o) {
                $items[] = [
                    'type'  => 'order',
                    'icon'  => 'fa-shopping-cart',
                    'color' => 'emerald',
                    'title' => "طلب جديد {$o->order_number}",
                    'meta'  => number_format($o->total, 0) . ' ' . $o->currency,
                    'time'  => $o->created_at->diffForHumans(),
                    'url'   => route('admin.orders.show', $o->id),
                    'ts'    => $o->created_at->timestamp,
                ];
            }
        }

        // مخزون منخفض
        $low = Product::where('stock', '>', 0)->where('stock', '<=', 5)
            ->orderBy('stock')->limit(5)->get(['id', 'name', 'stock']);
        foreach ($low as $p) {
            $items[] = [
                'type'  => 'stock',
                'icon'  => 'fa-triangle-exclamation',
                'color' => 'amber',
                'title' => "مخزون منخفض: {$p->name}",
                'meta'  => "متبقي {$p->stock}",
                'time'  => '',
                'url'   => route('admin.products.edit', $p->id),
                'ts'    => now()->timestamp - 1,
            ];
        }

        // مرتجعات جديدة
        if (Schema::hasTable('return_requests')) {
            $returns = ReturnRequest::where('status', 'pending')
                ->latest()->limit(3)->get(['id', 'order_id', 'created_at']);
            foreach ($returns as $r) {
                $items[] = [
                    'type'  => 'return',
                    'icon'  => 'fa-rotate-left',
                    'color' => 'rose',
                    'title' => "طلب إرجاع #{$r->id}",
                    'meta'  => 'بانتظار المراجعة',
                    'time'  => $r->created_at->diffForHumans(),
                    'url'   => route('admin.returns.show', $r->id),
                    'ts'    => $r->created_at->timestamp,
                ];
            }
        }

        // تقييمات جديدة
        if (Schema::hasTable('reviews')) {
            $reviews = Review::where('approved', false)->latest()->limit(3)
                ->get(['id', 'rating', 'product_id', 'created_at']);
            foreach ($reviews as $rv) {
                $items[] = [
                    'type'  => 'review',
                    'icon'  => 'fa-star',
                    'color' => 'sky',
                    'title' => "تقييم جديد ({$rv->rating}★) بانتظار الموافقة",
                    'meta'  => '',
                    'time'  => $rv->created_at->diffForHumans(),
                    'url'   => route('admin.reviews.index'),
                    'ts'    => $rv->created_at->timestamp,
                ];
            }
        }

        usort($items, fn ($a, $b) => $b['ts'] <=> $a['ts']);
        $items = array_slice($items, 0, 8);

        return response()->json([
            'count' => count($items),
            'items' => $items,
        ]);
    }
}
