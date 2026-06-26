@php $user = auth()->user(); @endphp
<aside class="lg:sticky lg:top-32 lg:w-64 mb-6 lg:mb-0 z-30 self-start">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-br from-violet-600 to-indigo-700 p-5 text-white">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur border border-white/25 grid place-items-center font-black text-lg shrink-0">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="font-black truncate">{{ $user->name }}</p>
                    <p class="text-[11px] text-violet-100 truncate">{{ $user->email }}</p>
                    @if($user->customerGroup)
                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] rounded-full bg-amber-300 text-violet-900 font-black">{{ $user->customerGroup->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        <nav class="p-3 space-y-1 text-sm">
            @php $items = [
                ['account.dashboard','fa-gauge-high','لوحة التحكم'],
                ['account.orders','fa-receipt','طلباتي'],
                ['account.returns.index','fa-rotate-left','مرتجعاتي'],
                ['account.reviews','fa-star','مراجعاتي'],
                ['profile.edit','fa-user-pen','بيانات الحساب'],
            ]; @endphp
            @foreach($items as [$r,$i,$l])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all
                    {{ request()->routeIs($r) ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md shadow-violet-500/30' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <i class="fa-solid {{ $i }} w-5 {{ request()->routeIs($r) ? '' : 'text-violet-500' }}"></i> {{ $l }}
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-2 pt-2">@csrf
                <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-right">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5"></i> تسجيل الخروج
                </button>
            </form>
        </nav>
    </div>
</aside>
