<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Build the base query scoped to the current visitor:
     * - logged in: by user_id
     * - guest: by session_id
     */
    private function scope()
    {
        $q = CartItem::query();
        if (Auth::check()) {
            $q->where('user_id', Auth::id());
        } else {
            $q->whereNull('user_id')->where('session_id', session()->getId());
        }
        return $q;
    }

    private function newAttributes(int $productId, int $quantity): array
    {
        return [
            'user_id' => Auth::id(),
            'session_id' => Auth::check() ? null : session()->getId(),
            'product_id' => $productId,
            'quantity' => $quantity,
        ];
    }

    public function index(): JsonResponse
    {
        $items = $this->scope()
            ->with(['product' => fn ($q) => $q->select('id', 'name', 'slug', 'price', 'sale_price', 'stock')->with(['images' => fn ($q) => $q->orderBy('sort_order')->limit(1)])])
            ->get()
            ->filter(fn ($i) => $i->product)
            ->map(fn ($i) => $this->shape($i))
            ->values();

        return response()->json([
            'items' => $items,
            'count' => $items->sum('quantity'),
            'subtotal' => round($items->sum(fn ($i) => $i['price'] * $i['quantity']), 2),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:999',
        ]);
        $qty = (int) ($data['quantity'] ?? 1);

        $product = Product::select('id', 'stock')->find($data['product_id']);
        if (! $product) {
            return response()->json(['ok' => false, 'message' => 'Product not found'], 404);
        }

        $existing = $this->scope()->where('product_id', $product->id)->first();
        $newQty = ($existing?->quantity ?? 0) + $qty;
        if ((int) $product->stock > 0 && $newQty > (int) $product->stock) {
            $newQty = (int) $product->stock;
        }

        if ($existing) {
            $existing->update(['quantity' => $newQty]);
        } else {
            CartItem::create($this->newAttributes($product->id, $newQty));
        }

        return $this->index();
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:0|max:999',
        ]);

        $item = $this->scope()->where('product_id', $data['product_id'])->first();
        if (! $item) {
            return $this->index();
        }

        if ((int) $data['quantity'] <= 0) {
            $item->delete();
        } else {
            $product = Product::select('id', 'stock')->find($data['product_id']);
            $qty = (int) $data['quantity'];
            if ($product && (int) $product->stock > 0 && $qty > (int) $product->stock) {
                $qty = (int) $product->stock;
            }
            $item->update(['quantity' => $qty]);
        }

        return $this->index();
    }

    public function remove(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
        ]);
        $this->scope()->where('product_id', $data['product_id'])->delete();
        return $this->index();
    }

    public function clear(): JsonResponse
    {
        $this->scope()->delete();
        return $this->index();
    }

    /**
     * Merge a localStorage cart (one-time migration on first AJAX load).
     * Accepts: [{id, quantity}]
     */
    public function merge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        foreach ($data['items'] ?? [] as $line) {
            $product = Product::select('id', 'stock')->find($line['id']);
            if (! $product) continue;
            $existing = $this->scope()->where('product_id', $product->id)->first();
            $qty = ($existing?->quantity ?? 0) + (int) $line['quantity'];
            if ((int) $product->stock > 0 && $qty > (int) $product->stock) {
                $qty = (int) $product->stock;
            }
            if ($existing) {
                $existing->update(['quantity' => $qty]);
            } else {
                CartItem::create($this->newAttributes($product->id, $qty));
            }
        }
        return $this->index();
    }

    private function shape(CartItem $item): array
    {
        $p = $item->product;
        $price = (float) ($p->sale_price ?? $p->price);
        $firstImg = $p->images->first();
        $image = $firstImg ? $firstImg->getUrl('thumb') : '';

        return [
            'id' => (int) $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => $price,
            'quantity' => (int) $item->quantity,
            'image' => $image,
            'stock' => (int) $p->stock,
        ];
    }
}
