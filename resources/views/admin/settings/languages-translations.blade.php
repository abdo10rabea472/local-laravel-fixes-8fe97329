@extends('admin.settings.layout', ['activeTab' => 'languages'])

@section('title', 'Translations — '.$language->name)

@section('settings-content')
<div class="space-y-6"
     x-data="aiTranslator({
        languageCode: '{{ $language->code }}',
        endpoint: '{{ route('admin.settings.languages.translations.ai_one', $language) }}',
        csrf: '{{ csrf_token() }}',
        firstGroup: '{{ array_key_first($groups) ?? '__new__' }}',
        firstGroupName: '{{ array_key_first($groups) ?? '' }}',
     })">


    @if(session('success'))<div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm font-bold">{{ session('error') }}</div>@endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-black text-slate-800">
                    <i class="fa-solid fa-language mr-2 text-violet-600"></i>
                    {{ $language->name }} <span class="text-slate-400">({{ $language->code }})</span>
                </h2>
                <p class="text-sm text-slate-500">
                    Source: <span class="font-mono">{{ $defaultCode }}</span> · {{ count($groups) }} group(s)
                </p>
            </div>
            <a href="{{ route('admin.settings.languages.index') }}" class="text-sm text-slate-500 hover:text-slate-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        @if(empty($groups))
            <div class="p-8 text-center text-slate-400">No translation files found in <span class="font-mono">resources/lang/{{ $defaultCode }}/</span></div>
        @else
        <form method="POST" action="{{ route('admin.settings.languages.translations.save', $language) }}">
            @csrf

            <div class="flex flex-wrap gap-2 p-4 border-b border-slate-100 bg-slate-50">
                @foreach($groups as $group => $data)
                    <button type="button" @click="tab='{{ $group }}'"
                            :class="tab==='{{ $group }}' ? 'bg-violet-600 text-white' : 'bg-white text-slate-700 border border-slate-200'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold">
                        {{ $group }} <span class="opacity-60">({{ count($data['keys']) }})</span>
                    </button>
                @endforeach
                <button type="button" @click="tab='__new__'"
                        :class="tab==='__new__' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 border border-emerald-300'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold">
                    <i class="fa-solid fa-plus mr-1"></i> Add new keys
                </button>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" @click="aiTranslateVisible(false)" x-show="!running"
                            :disabled="running"
                            class="h-9 px-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold disabled:opacity-50">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Translate empty with AI
                    </button>
                    <button type="button" @click="aiTranslateVisible(true)" x-show="!running"
                            class="h-9 px-3 rounded-xl bg-white border border-violet-300 text-violet-700 text-xs font-bold">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i> Re-translate all
                    </button>
                    <button type="button" @click="cancel()" x-show="running" x-cloak
                            class="h-9 px-3 rounded-xl bg-rose-600 text-white text-xs font-bold">
                        <i class="fa-solid fa-stop mr-1"></i> Stop
                    </button>
                    <input type="text" x-model="q" placeholder="Search keys…"
                           class="h-9 px-3 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
            </div>

            <div x-show="running || lastError || lastMessage" x-cloak class="px-4 py-2 border-b border-slate-100 bg-violet-50 text-violet-800 text-xs font-bold flex items-center gap-3">
                <template x-if="running">
                    <span><i class="fa-solid fa-spinner fa-spin mr-1"></i>
                        Translating <span x-text="progress.done"></span> / <span x-text="progress.total"></span>
                        — current: <span class="font-mono" x-text="progress.current"></span>
                    </span>
                </template>
                <template x-if="!running && lastMessage">
                    <span class="text-emerald-700"><i class="fa-solid fa-check mr-1"></i><span x-text="lastMessage"></span></span>
                </template>
                <template x-if="lastError">
                    <span class="text-rose-700 ml-auto"><i class="fa-solid fa-triangle-exclamation mr-1"></i><span x-text="lastError"></span></span>
                </template>
            </div>

            @foreach($groups as $group => $data)
                <div x-show="q==='' ? tab==='{{ $group }}' : tab!=='__new__'" x-cloak class="divide-y divide-slate-100">
                    @if($loop->first)
                        <div x-show="q!==''" class="px-4 py-2 bg-amber-50 text-amber-800 text-xs font-bold">
                            <i class="fa-solid fa-magnifying-glass mr-1"></i> Searching across all groups…
                        </div>
                    @endif
                    @php $groupHasMatch = false; @endphp
                    @foreach($data['keys'] as $key)
                        @php
                            $source = is_array($data['source'][$key] ?? null) ? json_encode($data['source'][$key]) : ($data['source'][$key] ?? '');
                            $value  = is_array($data['target'][$key] ?? null) ? json_encode($data['target'][$key]) : ($data['target'][$key] ?? '');
                            $haystack = strtolower($group.'.'.$key.' '.$source.' '.$value);
                        @endphp
                        <div class="p-4 grid grid-cols-12 gap-3 items-start"
                             x-show="q==='' || `{{ addslashes($haystack) }}`.includes(q.toLowerCase())">
                            <div class="col-span-4">
                                <div class="text-xs font-mono text-slate-500">{{ $group }}.{{ $key }}</div>
                                <div class="text-sm text-slate-700 mt-1" dir="auto">{{ $source }}</div>
                            </div>
                            <div class="col-span-8">
                                <textarea name="t[{{ $group }}][{{ $key }}]" rows="1" dir="auto"
                                          data-ai-group="{{ $group }}" data-ai-key="{{ $key }}"
                                          data-ai-source="{{ e($source) }}"
                                          class="ai-target w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-violet-400"
                                          placeholder="Translation in {{ $language->native_name }}…">{{ $value }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            {{-- Add new keys panel --}}
            <div x-show="tab==='__new__'" x-cloak class="p-5 space-y-3 bg-emerald-50/40">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Add new translation keys</h3>
                        <p class="text-xs text-slate-500">Saved to <span class="font-mono">resources/lang/en/{group}.php</span> (source) and <span class="font-mono">{{ $language->code }}/{group}.php</span>.</p>
                    </div>
                    <button type="button" @click="addRow()" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">
                        <i class="fa-solid fa-plus mr-1"></i> Add row
                    </button>
                </div>
                <div class="grid grid-cols-12 gap-2 text-[11px] font-bold text-slate-500 uppercase px-1">
                    <div class="col-span-2">Group</div>
                    <div class="col-span-3">Key</div>
                    <div class="col-span-3">English (source)</div>
                    <div class="col-span-3">{{ $language->native_name }}</div>
                    <div class="col-span-1"></div>
                </div>
                <template x-for="(row, i) in newRows" :key="i">
                    <div class="grid grid-cols-12 gap-2 items-start">
                        <input type="text" :name="`new[${i}][group]`" x-model="row.group"
                               placeholder="home" list="group-suggestions"
                               class="col-span-2 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono">
                        <input type="text" :name="`new[${i}][key]`" x-model="row.key"
                               placeholder="my_new_key"
                               class="col-span-3 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono">
                        <textarea :name="`new[${i}][en]`" x-model="row.en" rows="1" dir="ltr"
                                  placeholder="English text…"
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
                    @foreach($groups as $g => $_) <option value="{{ $g }}"></option> @endforeach
                </datalist>
            </div>

            <div class="p-5 border-t border-slate-100 flex justify-end gap-2 bg-slate-50">
                <a href="{{ route('admin.settings.languages.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700">Cancel</a>
                <button class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">
                    <i class="fa-solid fa-save mr-1"></i> Save Translations
                </button>
            </div>
        </form>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiTranslator', (opts) => ({
        tab: opts.firstGroup,
        q: '',
        newRows: [{ group: opts.firstGroupName, key: '', en: '', value: '' }],
        running: false,
        cancelRequested: false,
        progress: { done: 0, total: 0, current: '' },
        lastError: '',
        lastMessage: '',

        addRow() { this.newRows.push({ group: this.tab !== '__new__' ? this.tab : opts.firstGroupName, key: '', en: '', value: '' }); },
        removeRow(i) { this.newRows.splice(i, 1); if (!this.newRows.length) this.addRow(); },
        cancel() { this.cancelRequested = true; },

        // Translate every visible textarea (current tab or matching the search query).
        // overwrite=false skips ones that already have a value.
        async aiTranslateVisible(overwrite) {
            if (this.running) return;
            this.lastError = ''; this.lastMessage = '';
            const all = Array.from(document.querySelectorAll('textarea.ai-target'));
            // Only the ones currently visible (Alpine x-show toggles parent display).
            const visible = all.filter(t => t.offsetParent !== null);
            const targets = visible.filter(t => overwrite ? true : !t.value.trim());
            if (!targets.length) { this.lastMessage = 'Nothing to translate.'; return; }

            this.running = true;
            this.cancelRequested = false;
            this.progress = { done: 0, total: targets.length, current: '' };

            for (const ta of targets) {
                if (this.cancelRequested) { this.lastMessage = 'Stopped. Translated so far: ' + this.progress.done; break; }
                const group = ta.dataset.aiGroup;
                const key   = ta.dataset.aiKey;
                const source = ta.dataset.aiSource || '';
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
                        this.lastError = `Stopped on ${group}.${key}: ${json.message || ('HTTP '+res.status)}. Saved so far: ${this.progress.done}.`;
                        break; // stop on first error — partial work is already saved on the server.
                    }
                    ta.value = json.translation;
                    // light visual feedback
                    ta.classList.add('!bg-emerald-50');
                    setTimeout(() => ta.classList.remove('!bg-emerald-50'), 600);
                    this.progress.done++;
                } catch (e) {
                    this.lastError = `Network error on ${group}.${key}: ${e.message}. Saved so far: ${this.progress.done}.`;
                    break;
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
