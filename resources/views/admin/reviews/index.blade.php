@extends('admin.layouts.app')
@section('title', __('app.admin_reviews_title'))

@section('content')
<x-admin.page :title="__('app.admin_reviews_title')" :subtitle="__('app.admin_reviews_subtitle')">

    <x-admin.card :title="__('app.admin_reviews_card_all')" icon="fa-star" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-left">{{ __('app.admin_reviews_th_review') }}</th>
                        <th class="p-3">{{ __('app.admin_reviews_th_rating') }}</th>
                        <th class="p-3">{{ __('app.admin_reviews_th_product') }}</th>
                        <th class="p-3">{{ __('app.admin_reviews_th_customer') }}</th>
                        <th class="p-3">{{ __('app.admin_reviews_th_status') }}</th>
                        <th class="p-3">{{ __('app.admin_reviews_th_date') }}</th>
                        <th class="p-3">{{ __('app.admin_reviews_th_actions') }}</th>
                    </tr>
                </thead>
                <tbody x-data="reviewsPage()">
                    @forelse($reviews as $r)
                    @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$r->status] ?? 'gray'; @endphp
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50 align-top">
                        <td class="p-3 max-w-[360px]">
                            @if($r->title)<div class="font-bold text-gray-800 dark:text-gray-200">{{ $r->title }}</div>@endif
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-3">{{ $r->body }}</p>
                            @if($r->admin_reply)
                            <div class="mt-2 p-2 bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-300 rounded-lg text-xs">
                                <i class="fa-solid fa-reply mr-1"></i> {{ $r->admin_reply }}
                            </div>
                            @endif
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="text-amber-500">@for($i=0;$i<$r->rating;$i++)★@endfor<span class="text-gray-300 dark:text-gray-600">@for($i=0;$i<5-$r->rating;$i++)★@endfor</span></span>
                        </td>
                        <td class="p-3 text-center text-xs">
                            <a href="{{ route('product.show', $r->product->slug) }}" target="_blank" class="text-primary-600 hover:underline font-bold">{{ \Illuminate\Support\Str::limit($r->product->name, 30) }}</a>
                        </td>
                        <td class="p-3 text-xs">
                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $r->user?->name ?? $r->reviewer_name ?? __('app.admin_reviews_guest') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">{{ $r->user?->email ?? $r->reviewer_email }}</div>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-1 text-xs bg-{{ $sc }}-50 dark:bg-{{ $sc }}-950/30 text-{{ $sc }}-700 dark:text-{{ $sc }}-400 rounded-full font-bold">
                                {{ ['pending'=>__('app.admin_reviews_status_pending'),'approved'=>__('app.admin_reviews_status_approved'),'rejected'=>__('app.admin_reviews_status_rejected')][$r->status] ?? $r->status }}
                            </span>
                        </td>
                        <td class="p-3 text-center text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $r->created_at->format('Y-m-d') }}</td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <div class="flex flex-col gap-1 items-stretch">
                                @if($r->status !== 'approved')
                                <button @click="setStatus({{ $r->id }}, 'approved')" class="px-3 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">{{ __('app.admin_reviews_btn_approve') }}</button>
                                @endif
                                @if($r->status !== 'rejected')
                                <button @click="setStatus({{ $r->id }}, 'rejected')" class="px-3 py-1 text-xs bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold">{{ __('app.admin_reviews_btn_reject') }}</button>
                                @endif
                                <button @click="openReply({{ $r->id }}, `{{ addslashes($r->admin_reply ?? '') }}`)" class="px-3 py-1 text-xs bg-gray-900 dark:bg-dark-700 text-white rounded-lg font-bold">{{ __('app.admin_reviews_btn_reply') }}</button>
                                <button @click="del({{ $r->id }})" class="px-3 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-300 rounded-lg font-bold">{{ __('app.admin_reviews_btn_delete') }}</button>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="replyTo==={{ $r->id }}" x-cloak class="border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-dark-800/50">
                        <td colspan="7" class="p-4">
                            <textarea x-model="replyText" rows="3" class="w-full p-3 bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm" placeholder="{{ __('app.admin_reviews_reply_placeholder') }}"></textarea>
                            <div class="flex gap-2 mt-2">
                                <button @click="sendReply({{ $r->id }})" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-bold">{{ __('app.admin_reviews_btn_save_reply') }}</button>
                                <button @click="replyTo=null" class="px-4 py-2 bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-300 rounded-lg text-sm">{{ __('app.admin_reviews_btn_cancel') }}</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-12 text-center text-gray-400">
                        <i class="fas fa-star text-3xl mb-3 block"></i>
                        {{ __('app.admin_reviews_empty') }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $reviews->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card :title="__('app.admin_reviews_stats_title')" icon="fa-chart-simple">
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl">
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400">{{ __('app.admin_reviews_status_pending') }}</span>
                    <span class="text-lg font-black text-amber-700 dark:text-amber-400">{{ $counts['pending'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl">
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">{{ __('app.admin_reviews_status_approved') }}</span>
                    <span class="text-lg font-black text-emerald-700 dark:text-emerald-400">{{ $counts['approved'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-rose-50 dark:bg-rose-950/30 rounded-xl">
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400">{{ __('app.admin_reviews_status_rejected') }}</span>
                    <span class="text-lg font-black text-rose-700 dark:text-rose-400">{{ $counts['rejected'] }}</span>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card :title="__('app.admin_reviews_filter_title')" icon="fa-filter">
            <form method="GET" class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_reviews_filter_search_label') }}</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('app.admin_reviews_filter_search_placeholder') }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">{{ __('app.admin_reviews_filter_status_label') }}</label>
                    <select name="status" class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">{{ __('app.admin_reviews_filter_all_statuses') }}</option>
                        <option value="pending" @selected(request('status')==='pending')>{{ __('app.admin_reviews_status_pending') }}</option>
                        <option value="approved" @selected(request('status')==='approved')>{{ __('app.admin_reviews_status_approved') }}</option>
                        <option value="rejected" @selected(request('status')==='rejected')>{{ __('app.admin_reviews_status_rejected') }}</option>
                    </select>
                </div>
                <button class="w-full h-11 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-primary-500/20 transition-colors">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> {{ __('app.admin_reviews_filter_apply') }}
                </button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>

<script>
function reviewsPage(){return{
    replyTo:null, replyText:'',
    openReply(id, text){ this.replyTo = id; this.replyText = text || ''; },
    async req(url, method, body){
        try{
            const r = await fetch(url,{method,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:body?JSON.stringify(body):null});
            const j = await r.json();
            this.toast(j.message||'Done', j.ok);
            if(j.ok) setTimeout(()=>location.reload(), 500);
        } catch(e){ this.toast('An error occurred', false); }
    },
    toast(msg, ok){
        const t=document.createElement('div');
        t.className=`fixed top-4 right-4 z-50 px-5 py-3 rounded-xl text-white font-bold shadow-lg ${ok?'bg-emerald-600':'bg-rose-600'}`;
        t.textContent=msg; document.body.appendChild(t); setTimeout(()=>t.remove(),2000);
    },
    setStatus(id, status){ this.req(`/admin/reviews/${id}/status`, 'PATCH', {status}); },
    sendReply(id){ if(!this.replyText.trim()){this.toast('Write a reply',false); return;} this.req(`/admin/reviews/${id}/reply`, 'POST', {admin_reply:this.replyText}); },
    del(id){ if(!confirm('Permanently delete this review?')) return; this.req(`/admin/reviews/${id}`, 'DELETE'); }
}}
</script>
@endsection
