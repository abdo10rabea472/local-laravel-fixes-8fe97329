<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::withCount('users')->orderBy('name')->get();
        return view('admin.customer-groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        CustomerGroup::create($data);
        return back()->with('success', 'تم إنشاء المجموعة.');
    }

    public function update(Request $request, CustomerGroup $group)
    {
        $data = $this->validateData($request);
        $group->update($data);
        return back()->with('success', 'تم تحديث المجموعة.');
    }

    public function destroy(CustomerGroup $group)
    {
        $group->delete();
        return back()->with('success', 'تم حذف المجموعة.');
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'name' => 'required|string|max:100',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'badge_color' => 'required|in:violet,emerald,sky,amber,rose,slate,indigo',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
