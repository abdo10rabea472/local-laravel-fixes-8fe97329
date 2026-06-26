@extends('admin.layouts.app')

@section('title', 'إدارة المنتجات')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-3xl shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">قائمة منتجات المتجر</h3>
            <p class="text-xs text-slate-500 mt-1">إدارة المنتجات مع التصنيفات والصور المتعددة</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold text-sm px-6 py-3 rounded-2xl shadow-lg inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> إضافة منتج
        </a>
    </div>

    <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="text-xs font-bold text-slate-500">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم أو SKU" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500">التصنيف</label>
                <select name="category_id" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    <option value="">الكل</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="h-11 bg-slate-800 text-white font-bold rounded-2xl">تصفية</button>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-right p-4">المنتج</th>
                        <th class="text-right p-4">SKU</th>
                        <th class="text-right p-4">التصنيف</th>
                        <th class="text-right p-4">السعر</th>
                        <th class="text-right p-4">المخزون</th>
                        <th class="text-right p-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    @php $img = $product->images->first(); @endphp
                    <tr class="border-t border-slate-100">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                @if($img)
                                    <img src="{{ $img->getUrl('thumb') }}" class="w-12 h-12 rounded-xl object-cover" alt="">
                                @endif
                                <div>
                                    <div class="font-bold">{{ $product->name }}</div>
                                    @if($product->featured)<span class="text-[10px] text-amber-600 font-bold">مميز</span>@endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-xs">{{ $product->sku ?? '—' }}</td>
                        <td class="p-4">{{ $product->category?->name ?? '—' }}</td>
                        <td class="p-4">{{ number_format($product->sale_price ?? $product->price, 2) }} ج.م</td>
                        <td class="p-4">{{ $product->stock }}</td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-violet-600"><i class="fa-solid fa-pen"></i></a>
                                <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="text-slate-500"><i class="fa-solid fa-eye"></i></a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-ajax-confirm="حذف المنتج؟" data-ajax-remove>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-500">لا توجد منتجات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $products->links() }}</div>
    </div>
</div>
@endsection
