@extends('admin.layouts.app')
@section('title', $coupon->exists ? 'تعديل كود خصم' : 'إنشاء كود خصم')

@section('content')
<form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
      x-data="{ scope: '{{ old('scope', $coupon->scope ?? 'all') }}' }">
    @csrf
    @if($coupon->exists) @method('PUT') @endif

    <x-admin.page
        :title="$coupon->exists ? 'تعديل كود الخصم' : 'إنشاء كود خصم جديد'"
        subtitle="إعداد كوبون خصم بقيمة محددة ونطاق ومدة صلاحية."
        :back="route('admin.coupons.index')"
    >
        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-400 p-4 rounded-xl">
                @foreach($errors->all() as $e)<p class="text-sm">{{ $e }}</p>@endforeach
            </div>
        @endif

        <x-admin.card title="بيانات الكود الأساسية" icon="fa-ticket">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">كود الخصم *</label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm uppercase font-mono focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">نوع الخصم</label>
                    <select name="type" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="percent" @selected(old('type', $coupon->type)==='percent')>نسبة %</option>
                        <option value="fixed" @selected(old('type', $coupon->type)==='fixed')>مبلغ ثابت</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">قيمة الخصم *</label>
                    <input type="number" step="0.01" min="0" name="value" value="{{ old('value', $coupon->value) }}" required
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحد الأقصى للاستخدام</label>
                    <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">رسالة وصفية (اختياري)</label>
                <textarea name="description" rows="2"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('description', $coupon->description) }}</textarea>
            </div>
        </x-admin.card>

        <x-admin.card title="القيود والشروط" icon="fa-shield-halved">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحد الأدنى للطلب</label>
                    <input type="number" step="0.01" min="0" name="min_order_total" value="{{ old('min_order_total', $coupon->min_order_total) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">الحد الأقصى للخصم</label>
                    <input type="number" step="0.01" min="0" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">تاريخ البداية</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">تاريخ الانتهاء</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="نطاق الكود" icon="fa-bullseye">
            <select name="scope" x-model="scope" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <option value="all">جميع المنتجات</option>
                <option value="products">منتجات محددة</option>
                <option value="categories">أقسام محددة</option>
            </select>

            <div x-show="scope === 'products'" x-cloak class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">اختر المنتجات</label>
                <select name="product_ids[]" multiple size="8" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(in_array($p->id, old('product_ids', $selectedProducts)))>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="scope === 'categories'" x-cloak class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">اختر الأقسام</label>
                <select name="category_ids[]" multiple size="8" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm">
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(in_array($c->id, old('category_ids', $selectedCategories)))>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-admin.card>

        <x-slot:side>
            <x-admin.card title="حالة الكود" icon="fa-toggle-on">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))
                           class="rounded text-primary-600 focus:ring-primary-500">
                    تفعيل الكوبون فوراً
                </label>

                <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <button type="submit" class="w-full h-12 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                        <i class="fas fa-save ml-1"></i> {{ $coupon->exists ? 'حفظ التعديلات' : 'إنشاء الكود' }}
                    </button>
                    <a href="{{ route('admin.coupons.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        إلغاء
                    </a>
                </div>
            </x-admin.card>
        </x-slot:side>
    </x-admin.page>
</form>
@endsection
