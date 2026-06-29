@extends('admin.settings.layout', ['activeTab' => 'languages'])

@section('title', 'Translations — '.$language->name)

@section('settings-content')
@php
    $totalKeys = array_sum($counts ?? []);
    $baseQs = ['group' => $selected, 'q' => $q, 'per_page' => $perPage];
@endphp
<div class="space-y-6"
     x-data="aiTranslator({
        languageCode: '{{ $language->code }}',
        endpoint: '{{ route('admin.settings.languages.translations.ai_one', $language) }}',
        csrf: '{{ csrf_token() }}',
        currentGroup: '{{ $selected }}',
     })">

    @if(session('success'))<div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm font-bold">{{ session('error') }}</div>@endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-slate-100 flex-wrap gap-3">
            <div>
                <h2 class="text-lg font-black text-slate-800">
                    <i class="fa-solid fa-language mr-2 text-violet-600"></i>
                    {{ $language->name }} <span class="text-slate-400">({{ $language->code }})</span>
                </h2>
                <p class="text-sm text-slate-500">
                    {{ __('app.admin_settings_translations_source_label') }}
                    <span class="font-mono">{{ $defaultCode }}</span>
                    · {{ count($groupNames) }} {{ __('app.admin_settings_translations_groups_label') }}
                    · {{ $totalKeys }} keys
                </p>
            </div>
            <a href="{{ route('admin.settings.languages.index') }}" class="text-sm text-slate-500 hover:text-slate-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> {{ __('app.admin_settings_translations_back') }}
            </a>
        </div>

        @if(empty($groupNames))
            <div class="p-8 text-center text-slate-400">
                {{ __('app.admin_settings_translations_no_files') }}
                <span class="font-mono">resources/lang/{{ $defaultCode }}/</span>
            </div>
        @else

        {{-- Sidebar layout: groups list + content --}}
        <div class="grid grid-cols-12 min-h-[600px]">
            {{-- Groups sidebar --}}
            <aside class="col-span-12 md:col-span-3 lg:col-span-2 border-e border-slate-100 bg-slate-50 max-h-[80vh] overflow-y-auto">
                <div class="p-3 border-b border-slate-100 sticky top-0 bg-slate-50 z-10">
                    <input type="text" id="group-filter" placeholder="Filter groups…"
                           class="w-full h-9 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                </div>
                <ul class="py-1" id="group-list">
                    @foreach($groupNames as $g)
                        <li data-name="{{ $g }}">
                            <a href="{{ route('admin.settings.languages.translations', array_merge(['language' => $language->id], ['group' => $g, 'per_page' => $perPage])) }}"
                               class="flex items-center justify-between gap-2 px-3 py-2 text-xs font-bold rounded-lg mx-2 my-0.5
                                      {{ $selected === $g ? 'bg-violet-600 text-white' : 'text-slate-700 hover:bg-white' }}">
                                <span class="font-mono truncate">{{ $g }}</span>
                                <span class="opacity-70">{{ $counts[$g] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="p-2 border-t border-slate-100">
                    <a href="{{ route('admin.settings.languages.translations', ['language' => $language->id, 'group' => '__new__']) }}#new"
                       class="block text-center px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold">
                        <i class="fa-solid fa-plus mr-1"></i>
                        {{ __('app.admin_settings_translations_btn_add_new') }}
                    </a>
                </div>
            </aside>

            {{-- Content --}}
            <section class="col-span-12 md:col-span-9 lg:col-span-10">
                @if($selected === '__new__')
                    <form method="POST" action="{{ route('admin.settings.languages.translations.save', $language) }}" id="new-form">
                        @csrf
                        <div class="p-5 space-y-3 bg-emerald-50/40 min-h-[400px]" x-data="newKeysPanel('{{ $groupNames[0] ?? '' }}')">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800">{{ __('app.admin_settings_translations_add_keys_title') }}</h3>
                                    <p class="text-xs text-slate-500">{{ __('app.admin_settings_translations_add_keys_subtitle') }}</p>
                                </div>
                                <button type="button" @click="addRow()" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">
                                    {{ __('app.admin_settings_translations_btn_add_row') }}
                                </button>
                            </div>
                            <div class="grid grid-cols-12 gap-2 text-[11px] font-bold text-slate-500 uppercase px-1">
                                <div class="col-span-2">{{ __('app.admin_settings_translations_col_group') }}</div>
                                <div class="col-span-3">{{ __('app.admin_settings_translations_col_key') }}</div>
                                <div class="col-span-3">{{ __('app.admin_settings_translations_col_english') }}</div>
                                <div class="col-span-3">{{ $language->native_name }}</div>
                                <div class="col-span-1"></div>
                            </div>
                            <template x-for="(row, i) in newRows" :key="i">
                                <div class="grid grid-cols-12 gap-2 items-start">
                                    <input type="text" :name="`new[${i}][group]`" x-model="row.group" placeholder="home" list="group-suggestions"
                                           class="col-span-2 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono">
                                    <input type="text" :name="`new[${i}][key]`" x-model="row.key" placeholder="my_new_key"
                                           class="col-span-3 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono">
                                    <textarea :name="`new[${i}][en]`" x-model="row.en" rows="1" dir="ltr" placeholder="English text…"
                                              class="col-span-3 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm"></textarea>
                                    <textarea :name="`new[${i}][value]`" x-model="row.value" rows="1" dir="auto"
                                              :placeholder="`Translation in {{ $language->native_name }}…`"
                                              class="col-span-3 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm"></textarea>
                                    <button type="button" @click="removeRow(i)"
                                            class="col-span-1 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 text-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </template>
                            <datalist id="group-suggestions">
                                @foreach($groupNames as $g) <option value="{{ $g }}"></option> @endforeach
                            </datalist>
                        </div>
                        <div class="p-4 border-t border-slate-100 flex justify-end gap-2 bg-slate-50">
                            <a href="{{ route('admin.settings.languages.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700">{{ __('app.admin_settings_translations_btn_cancel') }}</a>
                            <button class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">
                                {{ __('app.admin_settings_translations_btn_save') }}
                            </button>
                        </div>
                    </form>
                @else
                    <form method="GET" class="flex flex-wrap items-center gap-2 p-3 border-b border-slate-100 bg-slate-50">
                        <input type="hidden" name="group" value="{{ $selected }}">
                        <input type="text" name="q" value="{{ $q }}" placeholder="{{ __('app.admin_settings_translations_search_placeholder') }}"
                               class="flex-1 min-w-[200px] h-9 px-3 bg-white border border-slate-200 rounded-xl text-sm">
                        <select name="per_page" class="h-9 px-2 bg-white border border-slate-200 rounded-xl text-sm">
                            @foreach([25,50,100,200] as $pp)
                                <option value="{{ $pp }}" @selected($perPage===$pp)>{{ $pp }} / page</option>
                            @endforeach
                        </select>
                        <button class="h-9 px-3 bg-slate-700 text-white rounded-xl text-xs font-bold">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                        <span class="text-xs text-slate-500 ms-auto">
                            <span class="font-bold text-slate-700">{{ $rendered['untranslated'] }}</span> untranslated of
                            <span class="font-bold">{{ $counts[$selected] ?? 0 }}</span>
                        </span>
                    </form>

                    <div class="flex flex-wrap gap-2 px-4 py-2 border-b border-slate-100 items-center">
                        <button type="button" form="save-form" @click.prevent="aiTranslateVisible(false)" x-show="!running" :disabled="running"
                                class="h-8 px-3 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold disabled:opacity-50">
                            <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> {{ __('app.admin_settings_translations_btn_translate_empty') }}
                        </button>
                        <button type="button" @click.prevent="aiTranslateVisible(true)" x-show="!running"
                                class="h-8 px-3 rounded-lg bg-white border border-violet-300 text-violet-700 text-xs font-bold">
                            <i class="fa-solid fa-arrows-rotate mr-1"></i> {{ __('app.admin_settings_translations_btn_retranslate') }}
                        </button>
                        <button type="button" @click.prevent="cancel()" x-show="running" x-cloak
                                class="h-8 px-3 rounded-lg bg-rose-600 text-white text-xs font-bold">
                            <i class="fa-solid fa-stop mr-1"></i> {{ __('app.admin_settings_translations_btn_stop') }}
                        </button>
                        <template x-if="running">
                            <span class="text-xs text-violet-700"><i class="fa-solid fa-spinner fa-spin mr-1"></i>
                                <span x-text="progress.done"></span>/<span x-text="progress.total"></span> — <span class="font-mono" x-text="progress.current"></span>
                            </span>
                        </template>
                        <template x-if="!running && lastMessage">
                            <span class="text-xs text-emerald-700"><i class="fa-solid fa-check mr-1"></i><span x-text="lastMessage"></span></span>
                        </template>
                        <template x-if="lastError">
                            <span class="text-xs text-rose-700 ms-auto"><i class="fa-solid fa-triangle-exclamation mr-1"></i><span x-text="lastError"></span></span>
                        </template>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.languages.translations.save', $language) }}" id="save-form">
                        @csrf
                        <div class="divide-y divide-slate-100">
                            @forelse($rendered['keys'] as $key)
                                @php
                                    $source = is_array($rendered['source'][$key] ?? null) ? json_encode($rendered['source'][$key]) : ($rendered['source'][$key] ?? '');
                                    $value  = is_array($rendered['target'][$key] ?? null) ? json_encode($rendered['target'][$key]) : ($rendered['target'][$key] ?? '');
                                    $empty  = trim((string)$value) === '';
                                @endphp
                                <div class="p-4 grid grid-cols-12 gap-4 items-start hover:bg-slate-50/50">
                                    <div class="col-span-12 md:col-span-5">
                                        <div class="flex items-center gap-2">
                                            <code class="text-[11px] text-slate-500 font-mono break-all">{{ $selected }}.{{ $key }}</code>
                                            @if($empty)
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">empty</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-slate-700 mt-1.5 leading-snug" dir="auto">{{ $source }}</div>
                                    </div>
                                    <div class="col-span-12 md:col-span-7">
                                        <textarea name="t[{{ $selected }}][{{ $key }}]" rows="2" dir="auto"
                                                  data-ai-group="{{ $selected }}" data-ai-key="{{ $key }}"
                                                  data-ai-source="{{ e($source) }}"
                                                  class="ai-target w-full px-3 py-2 bg-slate-50 border {{ $empty ? 'border-amber-300' : 'border-slate-200' }} rounded-xl text-sm focus:bg-white focus:border-violet-400"
                                                  placeholder="Translation in {{ $language->native_name }}…">{{ $value }}</textarea>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center text-slate-400 text-sm">No keys match this search.</div>
                            @endforelse
                        </div>

                        {{-- Pagination + save --}}
                        <div class="p-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50">
                            <div class="flex items-center gap-1">
                                @php
                                    $window = 2;
                                    $start = max(1, $page - $window);
                                    $end = min($rendered['lastPage'], $page + $window);
                                @endphp
                                @if($rendered['lastPage'] > 1)
                                    <a href="{{ route('admin.settings.languages.translations', ['language' => $language->id] + $baseQs + ['page' => max(1,$page-1)]) }}"
                                       class="px-3 h-9 inline-flex items-center bg-white border border-slate-200 rounded-lg text-xs font-bold {{ $page<=1 ? 'opacity-40 pointer-events-none' : '' }}">‹</a>
                                    @if($start > 1)
                                        <a href="{{ route('admin.settings.languages.translations', ['language' => $language->id] + $baseQs + ['page' => 1]) }}"
                                           class="px-3 h-9 inline-flex items-center bg-white border border-slate-200 rounded-lg text-xs font-bold">1</a>
                                        @if($start > 2)<span class="px-1 text-slate-400">…</span>@endif
                                    @endif
                                    @for($p = $start; $p <= $end; $p++)
                                        <a href="{{ route('admin.settings.languages.translations', ['language' => $language->id] + $baseQs + ['page' => $p]) }}"
                                           class="px-3 h-9 inline-flex items-center rounded-lg text-xs font-bold {{ $p===$page ? 'bg-violet-600 text-white' : 'bg-white border border-slate-200' }}">{{ $p }}</a>
                                    @endfor
                                    @if($end < $rendered['lastPage'])
                                        @if($end < $rendered['lastPage']-1)<span class="px-1 text-slate-400">…</span>@endif
                                        <a href="{{ route('admin.settings.languages.translations', ['language' => $language->id] + $baseQs + ['page' => $rendered['lastPage']]) }}"
                                           class="px-3 h-9 inline-flex items-center bg-white border border-slate-200 rounded-lg text-xs font-bold">{{ $rendered['lastPage'] }}</a>
                                    @endif
                                    <a href="{{ route('admin.settings.languages.translations', ['language' => $language->id] + $baseQs + ['page' => min($rendered['lastPage'],$page+1)]) }}"
                                       class="px-3 h-9 inline-flex items-center bg-white border border-slate-200 rounded-lg text-xs font-bold {{ $page>=$rendered['lastPage'] ? 'opacity-40 pointer-events-none' : '' }}">›</a>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.settings.languages.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700">{{ __('app.admin_settings_translations_btn_cancel') }}</a>
                                <button class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i> {{ __('app.admin_settings_translations_btn_save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </section>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Sidebar group filter (client-side, only filters the link list)
document.getElementById('group-filter')?.addEventListener('input', function (e) {
    const v = e.target.value.toLowerCase();
    document.querySelectorAll('#group-list li').forEach(li => {
        li.style.display = li.dataset.name.toLowerCase().includes(v) ? '' : 'none';
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.data('newKeysPanel', (firstGroup) => ({
        newRows: [{ group: firstGroup, key: '', en: '', value: '' }],
        addRow() { this.newRows.push({ group: firstGroup, key: '', en: '', value: '' }); },
        removeRow(i) { this.newRows.splice(i, 1); if (!this.newRows.length) this.addRow(); },
    }));

    Alpine.data('aiTranslator', (opts) => ({
        running: false,
        cancelRequested: false,
        progress: { done: 0, total: 0, current: '' },
        lastError: '',
        lastMessage: '',
        cancel() { this.cancelRequested = true; },
        async aiTranslateVisible(overwrite) {
            if (this.running) return;
            this.lastError = ''; this.lastMessage = '';
            const all = Array.from(document.querySelectorAll('textarea.ai-target'));
            const targets = all.filter(t => overwrite ? true : !t.value.trim());
            if (!targets.length) { this.lastMessage = 'Nothing to translate.'; return; }
            this.running = true;
            this.cancelRequested = false;
            this.progress = { done: 0, total: targets.length, current: '' };
            for (const ta of targets) {
                if (this.cancelRequested) { this.lastMessage = 'Stopped. Translated so far: ' + this.progress.done; break; }
                const group = ta.dataset.aiGroup, key = ta.dataset.aiKey, source = ta.dataset.aiSource || '';
                this.progress.current = `${group}.${key}`;
                if (!source.trim()) { this.progress.done++; continue; }
                try {
                    const res = await fetch(opts.endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': opts.csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ group, key, source, overwrite: overwrite ? 1 : 0 }),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok || !json.ok) {
                        this.lastError = `Stopped on ${group}.${key}: ${json.message || ('HTTP '+res.status)}.`;
                        break;
                    }
                    ta.value = json.translation;
                    ta.classList.add('!bg-emerald-50');
                    setTimeout(() => ta.classList.remove('!bg-emerald-50'), 600);
                    this.progress.done++;
                } catch (e) {
                    this.lastError = `Network error on ${group}.${key}: ${e.message}.`; break;
                }
            }
            this.running = false;
            if (!this.lastError && !this.cancelRequested) {
                this.lastMessage = `Done. Translated ${this.progress.done} key(s).`;
            }
        },
    }));
});
</script>
@endpush
@endsection
