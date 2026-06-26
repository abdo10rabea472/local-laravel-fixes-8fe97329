<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $q = Review::with(['product:id,slug,name', 'user:id,name,email'])->latest();
        if ($status = $request->string('status')->value()) {
            if (in_array($status, ['pending','approved','rejected'], true)) {
                $q->where('status', $status);
            }
        }
        if ($s = $request->string('q')->trim()->value()) {
            $q->where(function ($w) use ($s) {
                $w->where('title','like',"%{$s}%")
                  ->orWhere('body','like',"%{$s}%")
                  ->orWhere('reviewer_name','like',"%{$s}%")
                  ->orWhere('reviewer_email','like',"%{$s}%");
            });
        }
        $reviews = $q->paginate(20)->withQueryString();
        $counts = [
            'pending' => Review::where('status','pending')->count(),
            'approved' => Review::where('status','approved')->count(),
            'rejected' => Review::where('status','rejected')->count(),
        ];
        return view('admin.reviews.index', compact('reviews','counts'));
    }

    public function updateStatus(Request $request, Review $review)
    {
        $data = $request->validate(['status' => 'required|in:pending,approved,rejected']);
        $review->update($data);
        return $this->resp($request, true, 'تم تحديث حالة المراجعة.');
    }

    public function reply(Request $request, Review $review)
    {
        $data = $request->validate(['admin_reply' => 'required|string|max:2000']);
        $review->update(['admin_reply' => $data['admin_reply'], 'replied_at' => now()]);
        return $this->resp($request, true, 'تم حفظ الرد.');
    }

    public function destroy(Request $request, Review $review)
    {
        $review->delete();
        return $this->resp($request, true, 'تم حذف المراجعة.');
    }

    private function resp(Request $r, bool $ok, string $msg)
    {
        if ($r->expectsJson() || $r->ajax()) {
            return response()->json(['ok' => $ok, 'message' => $msg]);
        }
        return back()->with($ok ? 'success' : 'error', $msg);
    }
}
