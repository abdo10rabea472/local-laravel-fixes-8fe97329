@extends('account.layout')

@section('account_content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-violet-50/50 to-transparent">
            <h1 class="font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-rotate-left text-violet-600"></i> {{ __('app.acc_returns_title') }}
            </h1>
            <span class="text-xs font-bold text-slate-500">{{ __('app.acc_returns_count', ['count' => $returns->total()]) }}</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-right">{{ __('app.acc_rma_number') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('app.acc_order') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('app.acc_amount') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('app.acc_status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('app.acc_date') }}</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($returns as $r)
                    <tr class="hover:bg-violet-50/40 transition">
                        <td class="px-4 py-3 font-mono font-bold text-violet-700">{{ $r->rma_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r->order?->order_number }}</td>
                        <td class="px-4 py-3 font-bold">{{ number_format($r->refund_amount, 2) }} {{ __('app.cat_egp') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $r->statusColor() }}-100 text-{{ $r->statusColor() }}-700">{{ $r->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('account.returns.show', $r) }}" class="inline-flex items-center gap-1 text-violet-600 font-bold text-xs hover:underline">{{ __('app.acc_view') }} <i class="fa-solid fa-arrow-left text-[9px]"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12">
                        <div class="w-16 h-16 rounded-2xl bg-violet-50 text-violet-600 grid place-items-center text-2xl mx-auto mb-3"><i class="fa-solid fa-rotate-left"></i></div>
                        <p class="text-slate-500">{{ __('app.acc_no_returns') }}<br><a href="{{ route('account.orders') }}" class="text-violet-600 font-bold hover:underline">{{ __('app.acc_request_from_orders') }}</a></p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
