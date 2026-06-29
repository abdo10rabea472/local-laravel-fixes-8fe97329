@extends('admin.layouts.app')
@section('title', __('app.admin_subscribers_page_title'))

@section('content')
<x-admin.page :title="__('app.admin_subscribers_page_title')" :subtitle="__('app.admin_subscribers_page_subtitle')">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-admin.card padding="p-5">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">{{ __('app.admin_subscribers_total') }}</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($total) }}</p>
        </x-admin.card>
        <x-admin.card padding="p-5">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">{{ __('app.admin_subscribers_stat_active') }}</p>
            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($active) }}</p>
        </x-admin.card>
    </div>

    <x-admin.card :title="__('app.admin_subscribers_card_all')" icon="fa-envelope-open-text" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="p-3 text-left">{{ __('app.admin_subscribers_col_email') }}</th>
                        <th class="p-3">{{ __('app.admin_subscribers_col_status') }}</th>
                        <th class="p-3">{{ __('app.admin_subscribers_col_subscribed_at') }}</th>
                        <th class="p-3">{{ __('app.admin_subscribers_col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($subscribers as $s)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-3 font-mono text-gray-900 dark:text-white">{{ $s->email }}</td>
                        <td class="p-3 text-center">
                            @if($s->active)
                                <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full font-bold">{{ __('app.admin_subscribers_status_active') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-rose-50 dark:bg-rose-950/30 text-rose-600 rounded-full font-bold">{{ __('app.admin_subscribers_status_inactive') }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-center text-xs text-gray-500">{{ $s->subscribed_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.subscribers.toggle', $s) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-amber-600 hover:underline text-xs font-bold">{{ \$s->active ? __('app.admin_subscribers_btn_disable') : __('app.admin_subscribers_btn_enable') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.subscribers.destroy', $s) }}" class="inline ml-2" onsubmit="return confirm('{{ __(\'app.admin_subscribers_confirm_delete\') }}')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-bold">{{ __('app.admin_subscribers_btn_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-12 text-center text-gray-400">
                        <i class="fa-regular fa-envelope-open text-3xl mb-3 block"></i>
                        {{ __('app.admin_subscribers_empty') }}
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($subscribers->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $subscribers->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card :title="__('app.admin_subscribers_card_send_article')" icon="fa-paper-plane">
            <form method="POST" action="{{ route('admin.subscribers.send-article') }}" class="space-y-3" id="sendArticleForm"
                  onsubmit="return confirmSend();">
                @csrf
                <div class="relative">
                    <input type="text" id="postSearch" autocomplete="off" placeholder="{{ __('app.admin_subscribers_search_post_placeholder') }}"
                           class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    <div id="postResults" class="hidden absolute z-20 mt-1 w-full max-h-64 overflow-auto bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg"></div>
                </div>
                <input type="hidden" name="blog_post_id" id="blog_post_id">

                <div class="flex items-center gap-2 text-[11px] text-gray-400">
                    <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                    <span>{{ __('app.admin_subscribers_or_paste_url') }}</span>
                    <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                </div>
                <input type="url" name="post_url" id="postUrl" placeholder="https://site.com/blog/article-slug"
                       class="w-full h-11 px-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none" dir="ltr">


                <div id="selectedPost" class="hidden p-3 bg-violet-50 dark:bg-violet-950/30 border border-violet-200 dark:border-violet-800 rounded-xl text-xs">
                    <div class="font-bold text-violet-900 dark:text-violet-200" id="selectedTitle"></div>
                    <a id="selectedUrl" href="#" target="_blank" class="text-violet-600 dark:text-violet-400 break-all underline"></a>
                </div>

                <button type="submit" id="sendBtn" disabled
                        class="w-full h-12 inline-flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg shadow-violet-500/20">
                    <i class="fa-solid fa-paper-plane"></i> <span id="sendBtnText">Send to Subscribers</span>
                </button>

                @if(session('success'))
                    <p class="text-xs text-emerald-600 font-bold">{{ session('success') }}</p>
                @endif
                @if(session('error'))
                    <p class="text-xs text-rose-600 font-bold leading-relaxed">{{ session('error') }}</p>
                @endif
            </form>

            <script>
            (function(){
                const input = document.getElementById('postSearch');
                const results = document.getElementById('postResults');
                const hidden = document.getElementById('blog_post_id');
                const sel = document.getElementById('selectedPost');
                const selT = document.getElementById('selectedTitle');
                const selU = document.getElementById('selectedUrl');
                const btn = document.getElementById('sendBtn');
                const btnT = document.getElementById('sendBtnText');
                let t;

                input.addEventListener('input', () => {
                    clearTimeout(t);
                    const q = input.value.trim();
                    if (q.length < 2) { results.classList.add('hidden'); return; }
                    t = setTimeout(async () => {
                        const r = await fetch("{{ route('admin.subscribers.posts-search') }}?q=" + encodeURIComponent(q));
                        const items = await r.json();
                        results.innerHTML = items.length
                            ? items.map(p => `<button type="button" data-id="${p.id}" data-title="${p.title.replace(/"/g,'&quot;')}" data-url="${p.url}" class="block w-full text-left px-3 py-2 text-xs hover:bg-violet-50 dark:hover:bg-violet-950/30 border-b border-gray-100 dark:border-gray-800">${p.title}<div class="text-[10px] text-gray-400 truncate">${p.url}</div></button>`).join('')
                            : '<div class="p-3 text-xs text-gray-400 text-center">No results</div>';
                        results.classList.remove('hidden');
                        results.querySelectorAll('button').forEach(b => {
                            b.addEventListener('click', () => {
                                hidden.value = b.dataset.id;
                                selT.textContent = b.dataset.title;
                                selU.href = b.dataset.url;
                                selU.textContent = b.dataset.url;
                                sel.classList.remove('hidden');
                                btn.disabled = false;
                                btnT.textContent = 'Send "' + b.dataset.title.slice(0,40) + '"';
                                input.value = b.dataset.title;
                                results.classList.add('hidden');
                            });
                        });
                    }, 250);
                });
                document.addEventListener('click', e => {
                    if (!results.contains(e.target) && e.target !== input) results.classList.add('hidden');
                });
                window.confirmSend = function(){
                    const url = document.getElementById('postUrl').value.trim();
                    if (!hidden.value && !url) { alert('Choose a post or paste a URL'); return false; }
                    return confirm('This will send the article to all active subscribers. Continue?');
                };
                document.getElementById('postUrl').addEventListener('input', e => {
                    btn.disabled = !(hidden.value || e.target.value.trim());
                });

            })();
            </script>
        </x-admin.card>


        <x-admin.card :title="__('app.admin_subscribers_card_quick_actions')" icon="fa-bolt">
            <a href="{{ route('admin.subscribers.export') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-download"></i> {{ __('app.admin_subscribers_btn_export') }}
            </a>
        </x-admin.card>


        <x-admin.card :title="__('app.admin_subscribers_card_search')" icon="fa-search">
            <form method="GET" class="space-y-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('app.admin_subscribers_search_email_placeholder') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <button class="w-full h-11 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-sm font-bold">{{ __('app.admin_subscribers_btn_search') }}</button>
            </form>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
