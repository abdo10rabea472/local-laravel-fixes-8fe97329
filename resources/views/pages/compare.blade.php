@extends('layouts.front')

@section('content')
<section class="py-12 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
            <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-scale-balanced text-indigo-500"></i> مقارنة المنتجات</h1>
            @if($products->count())
                <form method="POST" action="{{ route('compare.clear') }}">@csrf @method('DELETE')
                    <button class="text-sm text-red-600 hover:text-red-700"><i class="fas fa-times"></i> مسح الكل</button>
                </form>
            @endif
        </div>

        @foreach(['success','error','info'] as $type)
            @if(session($type))
                <div class="mb-4 p-3 rounded-lg {{ ['success'=>'bg-emerald-50 text-emerald-700','error'=>'bg-red-50 text-red-700','info'=>'bg-blue-50 text-blue-700'][$type] }}">{{ session($type) }}</div>
            @endif
        @endforeach

        @if($products->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i class="fas fa-scale-balanced text-6xl text-slate-300 mb-4"></i>
                <p class="text-slate-600 mb-4">لم تقم بإضافة منتجات للمقارنة بعد</p>
                <a href="{{ route('products.index') }}" class="inline-block bg-violet-600 text-white px-6 py-2.5 rounded-lg hover:bg-violet-700">تصفح المنتجات</a>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="p-4 text-right">المقارنة</th>
                            @foreach($products as $p)
                                <th class="p-4 min-w-[200px]">
                                    <div class="aspect-square bg-slate-100 rounded-lg mb-2 overflow-hidden">
                                        @if($p->images->first())
                                            <img src="{{ asset('storage/'.$p->images->first()->path) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <a href="{{ route('product.show', $p->slug) }}" class="text-slate-800 hover:text-violet-600 block">{{ $p->name }}</a>
                                    <form method="POST" action="{{ route('compare.remove') }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                                        <button class="text-xs text-red-500 hover:text-red-700"><i class="fas fa-times"></i> إزالة</button>
                                    </form>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach([
                            'السعر' => fn($p) => '<span class="text-violet-700 font-bold">'.number_format($p->sale_price ?? $p->price, 2).' ر.س</span>',
                            'التصنيف' => fn($p) => e($p->category?->name ?? '-'),
                            'التوفر' => fn($p) => $p->stock > 0 ? '<span class="text-emerald-600">متوفر ('.$p->stock.')</span>' : '<span class="text-red-500">نفذ</span>',
                            'SKU' => fn($p) => e($p->sku ?? '-'),
                            'الوصف' => fn($p) => e(\Illuminate\Support\Str::limit($p->short_description ?? '', 120)),
                        ] as $label => $fn)
                            <tr>
                                <td class="p-4 font-semibold text-slate-700 bg-slate-50">{{ $label }}</td>
                                @foreach($products as $p)
                                    <td class="p-4 text-center text-slate-700">{!! $fn($p) !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
