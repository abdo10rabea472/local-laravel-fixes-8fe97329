@extends('admin.layouts.app')

@section('title', 'شركات الشحن')

@section('content')
<div class="p-6 space-y-6" x-data="{ showForm: false, editing: null }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">شركات الشحن</h1>
        <button @click="showForm = true; editing = null" class="px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700">
            <i class="fa-solid fa-plus ml-2"></i> إضافة شركة شحن
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Form Modal --}}
    <div x-show="showForm" x-transition class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showForm = false">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-slate-800 mb-4" x-text="editing ? 'تعديل شركة شحن' : 'إضافة شركة شحن'"></h2>
            <form :action="editing ? `/admin/shipping-carriers/${editing.id}` : '{{ route('admin.shipping-carriers.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editing">@method('PUT')</template>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">اسم الشركة *</label>
                    <input type="text" name="name" :value="editing?.name" required class="w-full rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">الكود الفريد *</label>
                    <input type="text" name="code" :value="editing?.code" required class="w-full rounded-xl border-slate-200" placeholder="dhl, aramex, bosta...">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">رابط التتبع (استخدم {tracking} للرقم)</label>
                    <input type="text" name="tracking_url_template" :value="editing?.tracking_url_template" class="w-full rounded-xl border-slate-200" placeholder="https://example.com/track/{tracking}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">هاتف الاتصال</label>
                        <input type="text" name="contact_phone" :value="editing?.contact_phone" class="w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">إيميل</label>
                        <input type="email" name="contact_email" :value="editing?.contact_email" class="w-full rounded-xl border-slate-200">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">التكلفة الافتراضية</label>
                        <input type="number" step="0.01" name="default_cost" :value="editing?.default_cost ?? 0" class="w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ترتيب العرض</label>
                        <input type="number" name="sort_order" :value="editing?.sort_order ?? 0" class="w-full rounded-xl border-slate-200">
                    </div>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" :checked="editing ? editing.is_active : true" class="rounded">
                    <span class="text-sm font-semibold text-slate-700">مفعّل</span>
                </label>
                <div class="flex gap-2 pt-4 border-t">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-violet-600 text-white font-semibold hover:bg-violet-700">حفظ</button>
                    <button type="button" @click="showForm = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-right">الاسم</th>
                    <th class="px-4 py-3 text-right">الكود</th>
                    <th class="px-4 py-3 text-right">هاتف</th>
                    <th class="px-4 py-3 text-right">التكلفة</th>
                    <th class="px-4 py-3 text-right">الحالة</th>
                    <th class="px-4 py-3 text-right">الطلبات</th>
                    <th class="px-4 py-3 text-right">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($carriers as $c)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono">{{ $c->code }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $c->contact_phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($c->default_cost, 2) }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.shipping-carriers.toggle', $c) }}">
                                @csrf @method('PATCH')
                                <button class="px-3 py-1 rounded-full text-xs font-bold {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $c->is_active ? 'مفعّل' : 'موقوف' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $c->orders()->count() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button @click="editing = @js($c); showForm = true" class="px-3 py-1 rounded-lg bg-sky-100 text-sky-700 text-xs font-semibold hover:bg-sky-200">تعديل</button>
                                <form method="POST" action="{{ route('admin.shipping-carriers.destroy', $c) }}" onsubmit="return confirm('حذف؟')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1 rounded-lg bg-rose-100 text-rose-700 text-xs font-semibold hover:bg-rose-200">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-12 text-slate-400">لا توجد شركات شحن بعد. أضف الأولى لتظهر في صفحة الطلبات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
