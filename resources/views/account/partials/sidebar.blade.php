@php $user = auth()->user(); @endphp
<aside class="lg:fixed lg:right-4 lg:top-28 lg:w-64 bg-white rounded-2xl border border-slate-200 p-5 mb-6 lg:mb-0">
    <div class="flex items-center gap-3 mb-5 pb-5 border-b border-slate-100">
        <div class="w-12 h-12 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center font-black text-lg">{{ mb_substr($user->name, 0, 1) }}</div>
        <div class="min-w-0">
            <p class="font-bold text-slate-800 truncate">{{ $user->name }}</p>
            <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
            @if($user->customerGroup)
                <span class="inline-block mt-1 px-2 py-0.5 text-[10px] rounded-full bg-{{ $user->customerGroup->badge_color }}-50 text-{{ $user->customerGroup->badge_color }}-700 font-bold">{{ $user->customerGroup->name }}</span>
            @endif
        </div>
    </div>
    <nav class="space-y-1 text-sm">
        @php $items = [
            ['account.dashboard','fa-gauge-high','لوحة التحكم'],
            ['account.orders','fa-receipt','طلباتي'],
            ['account.reviews','fa-star','مراجعاتي'],
            ['profile.edit','fa-user-pen','بيانات الحساب'],
        ]; @endphp
        @foreach($items as [$r,$i,$l])
            <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs($r) ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid {{ $i }} w-5"></i> {{ $l }}
            </a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold text-right">
                <i class="fa-solid fa-arrow-right-from-bracket w-5"></i> تسجيل الخروج
            </button>
        </form>
    </nav>
</aside>
