<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerNotificationMail;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query()
            ->withCount('orders')
            ->with('customerGroup:id,name,badge_color')
            ->latest();

        if ($s = $request->string('q')->trim()->value()) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }
        if ($g = $request->integer('group')) {
            $q->where('customer_group_id', $g);
        }
        if ($request->filled('status')) {
            $q->where('is_active', $request->string('status')->value() === 'active');
        }

        $customers = $q->paginate(20)->withQueryString();
        $groups = CustomerGroup::orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'groups'));
    }

    public function show(User $customer)
    {
        $customer->load([
            'customerGroup',
            'orders' => fn ($q) => $q->withCount('items')->latest()->limit(50),
            'reviews.product:id,slug,name',
        ]);
        $totalSpent = $customer->totalSpent();
        $groups = CustomerGroup::orderBy('name')->get();
        return view('admin.customers.show', compact('customer', 'totalSpent', 'groups'));
    }

    public function update(Request $request, User $customer)
    {
        $data = $request->validate([
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'admin_notes' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:30',
        ]);
        $customer->update($data);
        return $this->resp($request, true, 'تم حفظ البيانات.');
    }

    public function toggleActive(Request $request, User $customer)
    {
        $customer->update(['is_active' => ! $customer->is_active]);
        return $this->resp($request, true, $customer->is_active ? 'تم تفعيل العميل.' : 'تم حظر العميل.');
    }

    public function sendEmail(Request $request, User $customer)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
        ]);
        try {
            Mail::to($customer->email)->send(new CustomerNotificationMail($data['subject'], $data['body'], $customer->name));
            return $this->resp($request, true, 'تم إرسال البريد بنجاح.');
        } catch (\Throwable $e) {
            Log::warning('Customer mail failed', ['err' => $e->getMessage()]);
            return $this->resp($request, false, 'فشل إرسال البريد: ' . $e->getMessage());
        }
    }

    private function resp(Request $r, bool $ok, string $msg)
    {
        if ($r->expectsJson() || $r->ajax()) {
            return response()->json(['ok' => $ok, 'message' => $msg]);
        }
        return back()->with($ok ? 'success' : 'error', $msg);
    }
}
