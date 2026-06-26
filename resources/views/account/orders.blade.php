@extends('account.layout')
@section('account_content')
@php
    $all = $orders->items();
    $stats = [
        'total'     => $orders->total(),
        'pending'   => collect($all)->where('status','pending')->count(),
        'shipped'   => collect($all)->whereIn('status',['shipped','paid'])->count(),
        'delivered' => collect($all)->where('status','delivered')->count(),
    ];
    $statusLabels = [
        ''=>'كل الحالات','pending'=>'قيد الانتظار','paid'=>'مدفوع',
        'shipped'=>'تم الشحن','delivered'=>'تم التوصيل','cancelled'=>'ملغي','refunded'=>'مسترد',
    ];
@endphp

<div class="space-y-6">
    {{-- Page title --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-violet-600"></i> طلباتي
            </h1>
            <p class="text-sm text-slate-500 mt-1">تابع حالة طلباتك وفواتيرك السابقة.</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-violet-600 text-white text-sm font-bold hover:bg-violet-700 transition shadow-sm shadow-violet-500/30">
            <i class="fa-solid fa-bag-shopping"></i> تسوّق المزيد
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @php $cards = [
            ['الكل',$stats['total'],'violet','fa-list'],
            ['قيد الانتظار',$stats['pending'],'amber','fa-clock'],
            ['قيد الشحن',$stats['shipped'],'sky','fa-truck'],
            ['تم التوصيل',$stats['delivered'],'emerald','fa-check-double'],
        ]; @endphp
        @foreach($cards as [$lbl,$val,$c,$ic])
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">{{ $lbl }}</p>
                <p class="text-xl font-black text-{{ $c }}-600 mt-1">{{ $val }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-{{ $c }}-50 text-{{ $c }}-600 grid place-items-center">
                <i class="fa-solid {{ $ic }}"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 flex flex-wrap items-center gap-2">
        <div class="flex-1 min-w-[200px] relative">
            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث برقم الطلب…"
                   class="w-full h-11 pr-9 pl-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-violet-500 focus:bg-white focus:outline-none">
        </div>
        <select name="status" class="h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-violet-500 focus:bg-white focus:outline-none">
            @foreach($statusLabels as $k=>$v)
                <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="h-11 px-5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition">
            <i class="fa-solid fa-filter ml-1"></i> فلتر
        </button>
        @if(request('q') || request('status'))
        <a href="{{ route('account.orders') }}" class="h-11 px-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 inline-flex items-center">
            <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
    </form>

    {{-- Orders list --}}
    @forelse($orders as $o)
        @php $c = $o->statusBadgeColor(); @endphp
        <a href="{{ route('account.orders.show', $o) }}"
           class="group block bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-violet-300 transition overflow-hidden">
            <div class="p-4 sm:p-5 flex flex-wrap items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 grid place-items-center text-lg shrink-0">
                    <i class="fa-solid fa-box"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-mono font-black text-slate-900">#{{ $o->order_number }}</span>
                        <span class="px-2 py-0.5 text-[10px] rounded-full font-black bg-{{ $c }}-50 text-{{ $c }}-700">
                            {{ $o->statusLabel() }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                        <span><i class="fa-regular fa-calendar ml-1"></i>{{ $o->created_at->format('Y-m-d H:i') }}</span>
                        <span><i class="fa-solid fa-cubes ml-1"></i>{{ $o->items_count }} عنصر</span>
                    </div>
                </div>
                <div class="text-left shrink-0">
                    <p class="text-[11px] text-slate-500 font-bold">الإجمالي</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($o->total, 2) }} <span class="text-xs text-slate-500">{{ $o->currency }}</span></p>
                </div>
                <div class="shrink-0 text-violet-600 group-hover:translate-x-[-4px] transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </div>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
            <div class="w-20 h-20 rounded-2xl bg-violet-50 text-violet-600 grid place-items-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 class="font-black text-slate-900 text-lg mb-1">لا توجد طلبات بعد</h3>
            <p class="text-sm text-slate-500 mb-5">ابدأ التسوق وستظهر طلباتك هنا.</p>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 h-11 px-6 rounded-xl bg-violet-600 text-white font-bold hover:bg-violet-700 transition">
                <i class="fa-solid fa-bag-shopping"></i> ابدأ التسوّق
            </a>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div>{{ $orders->links() }}</div>
    @endif
</div>
@endsection
