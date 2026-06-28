@extends('layouts.front')

@section('content')
<section class="py-12 bg-slate-50 min-h-[60vh]">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-slate-800 mb-6"><i class="fas fa-heart text-rose-500"></i> قائمة المفضلة</h1>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-lg">{{ session('success') }}</div>
        @endif

        @if($items->count() === 0)
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i class="fas fa-heart-broken text-6xl text-slate-300 mb-4"></i>
                <p class="text-slate-600 mb-4">قائمة المفضلة فارغة</p>
                <a href="{{ route('products.index') }}" class="inline-block bg-violet-600 text-white px-6 py-2.5 rounded-lg hover:bg-violet-700">تصفح المنتجات</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($items as $item)
                @php $p = $item->product; @endphp
                @if($p)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                    <a href="{{ route('product.show', $p->slug) }}" class="block aspect-square bg-slate-100">
                        @if($p->images->first())
                            <img src="{{ asset('storage/'.$p->images->first()->path) }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fas fa-image text-4xl"></i></div>
                        @endif
                    </a>
                    <div class="p-4">
                        <a href="{{ route('product.show', $p->slug) }}" class="font-semibold text-slate-800 line-clamp-2 hover:text-violet-600">{{ $p->name }}</a>
                        <div class="mt-2 flex justify-between items-center">
                            <span class="text-violet-700 font-bold">{{ number_format($p->sale_price ?? $p->price, 2) }} ر.س</span>
                            <form method="POST" action="{{ route('wishlist.destroy', $item->id) }}">
                                @csrf @method('DELETE')
                                <button class="text-rose-500 hover:text-rose-700"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    </div>
</section>
@endsection
