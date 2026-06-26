<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $ordersCount = $user->orders()->count();
        $totalSpent = $user->totalSpent();
        $recentOrders = $user->orders()->withCount('items')->limit(5)->get();
        $reviewsCount = $user->reviews()->count();
        return view('account.dashboard', compact('user','ordersCount','totalSpent','recentOrders','reviewsCount'));
    }

    public function orders()
    {
        $orders = Auth::user()->orders()->withCount('items')->paginate(15);
        return view('account.orders', compact('orders'));
    }

    public function order(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 404);
        $order->load(['items.product:id,slug,name', 'history']);
        return view('account.order-show', compact('order'));
    }

    public function reviews()
    {
        $reviews = Auth::user()->reviews()->with('product:id,slug,name')->paginate(15);
        return view('account.reviews', compact('reviews'));
    }

    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'body' => 'required|string|min:5|max:2000',
        ]);

        $user = Auth::user();
        // Only allow reviewing products the user has actually purchased
        $hasPurchased = Order::where('user_id', $user->id)
            ->whereIn('status', ['paid','shipped','delivered'])
            ->whereHas('items', fn ($q) => $q->where('product_id', $data['product_id']))
            ->exists();

        if (! $hasPurchased) {
            return back()->with('error', 'يمكنك مراجعة المنتجات التي اشتريتها فقط.');
        }

        Review::updateOrCreate(
            ['product_id' => $data['product_id'], 'user_id' => $user->id],
            [
                'reviewer_name' => $user->name,
                'reviewer_email' => $user->email,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'شكراً! ستظهر مراجعتك بعد الموافقة.');
    }
}
