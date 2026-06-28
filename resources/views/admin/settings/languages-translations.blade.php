@extends('admin.settings.layout', ['activeTab' => 'languages'])

@section('title', 'Translations — '.$language->name)

@section('settings-content')
<div class="space-y-6" x-data="{ tab: '{{ array_key_first($groups) ?? '' }}', q: '' }">
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
                <div class="ml-auto">
                    <input type="text" x-model="q" placeholder="Search keys…"
                           class="h-9 px-3 bg-white border border-slate-200 rounded-xl text-sm">
                </div>
            </div>

            @foreach($groups as $group => $data)
                <div x-show="tab==='{{ $group }}'" x-cloak class="divide-y divide-slate-100">
                    @foreach($data['keys'] as $key)
                        @php
                            $source = is_array($data['source'][$key] ?? null) ? json_encode($data['source'][$key]) : ($data['source'][$key] ?? '');
                            $value  = is_array($data['target'][$key] ?? null) ? json_encode($data['target'][$key]) : ($data['target'][$key] ?? '');
                        @endphp
                        <div class="p-4 grid grid-cols-12 gap-3 items-start"
                             x-show="q==='' || '{{ addslashes($key) }}'.toLowerCase().includes(q.toLowerCase())">
                            <div class="col-span-4">
                                <div class="text-xs font-mono text-slate-500">{{ $group }}.{{ $key }}</div>
                                <div class="text-sm text-slate-700 mt-1" dir="auto">{{ $source }}</div>
                            </div>
                            <div class="col-span-8">
                                <textarea name="t[{{ $group }}][{{ $key }}]" rows="1" dir="auto"
                                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-violet-400"
                                          placeholder="Translation in {{ $language->native_name }}…">{{ $value }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

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
@endsection
