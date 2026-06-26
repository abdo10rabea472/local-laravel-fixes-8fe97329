@extends('admin.layouts.app')
@section('title', 'أكواد الخصم')

@section('content')
<x-admin.page title="أكواد الخصم" subtitle="إدارة جميع كوبونات الخصم المتاحة في المتجر.">
    <x-admin.card title="كل الكوبونات" icon="fa-ticket" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-right">الكود</th>
                        <th class="p-3">القيمة</th>
                        <th class="p-3">النطاق</th>
                        <th class="p-3">الصلاحية</th>
                        <th class="p-3">الاستخدام</th>
                        <th class="p-3">الحالة</th>
                        <th class="p-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $c)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-3 font-mono font-bold text-primary-600">{{ $c->code }}</td>
                        <td class="p-3 text-center font-bold text-gray-900 dark:text-white">{{ $c->type === 'percent' ? $c->value . '%' : number_format($c->value, 2) . ' EGP' }}</td>
                        <td class="p-3 text-center text-xs text-gray-600 dark:text-gray-400">{{ ['all'=>'الكل','products'=>'منتجات','categories'=>'أقسام'][$c->scope] }}</td>
                        <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">
                            {{ $c->starts_at?->format('Y-m-d') ?: '—' }} → {{ $c->ends_at?->format('Y-m-d') ?: '∞' }}
                        </td>
                        <td class="p-3 text-center text-xs">{{ $c->used_count }} / {{ $c->usage_limit ?: '∞' }}</td>
                        <td class="p-3 text-center">
                            @if($c->is_active)
                                <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full font-bold">مفعل</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 rounded-full font-bold">معطل</span>
                            @endif
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.coupons.edit', $c) }}" class="text-primary-600 hover:underline text-xs font-bold">تعديل</a>
                            <form action="{{ route('admin.coupons.toggle', $c) }}" method="POST" class="inline" data-ajax-toggle>
                                @csrf @method('PATCH')
                                <button data-toggle-state="{{ $c->is_active ? 'on' : 'off' }}" data-toggle-on="تعطيل" data-toggle-off="تفعيل" class="text-amber-600 hover:underline text-xs font-bold mx-2">{{ $c->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                            </form>
                            <form action="{{ route('admin.coupons.destroy', $c) }}" method="POST" class="inline" data-ajax-confirm="حذف الكود؟" data-ajax-remove>
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-bold">حذف</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-12 text-center text-gray-400">
                        <i class="fas fa-ticket text-3xl mb-3 block"></i>
                        لا توجد أكواد بعد.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $coupons->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="إجراءات سريعة" icon="fa-bolt">
            <a href="{{ route('admin.coupons.create') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                <i class="fa-solid fa-plus"></i> إنشاء كود جديد
            </a>
        </x-admin.card>

        <x-admin.card title="فلترة وبحث" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">بحث بالكود</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="مثال: WELCOME10"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحالة</label>
                    <select name="status" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(request('status')==='active')>مفعل</option>
                        <option value="inactive" @selected(request('status')==='inactive')>غير مفعل</option>
                    </select>
                </div>
                <button type="submit" class="w-full h-11 bg-gray-900 dark:bg-dark-700 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                    <i class="fas fa-filter ml-1"></i> تطبيق
                </button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
