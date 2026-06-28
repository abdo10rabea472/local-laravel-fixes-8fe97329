@extends('admin.settings.layout', ['activeTab' => 'languages'])

@section('title', 'Languages')

@section('settings-content')
<div class="space-y-6" x-data="{ open:false, edit:null, form:{} }">
    @if(session('success'))<div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm font-bold">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first() }}</div>@endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-black text-slate-800">Languages</h2>
                <p class="text-sm text-slate-500">Manage available languages and the default locale.</p>
            </div>
            <button @click="open=true; edit=null; form={direction:'ltr', is_active:true, sort_order:({{ $languages->count() + 1 }})}"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">
                <i class="fa-solid fa-plus mr-1"></i> Add Language
            </button>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="p-3 text-left">Flag</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Native</th>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Locale</th>
                    <th class="p-3 text-left">Dir</th>
                    <th class="p-3 text-left">Default</th>
                    <th class="p-3 text-left">Active</th>
                    <th class="p-3 text-left">Order</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($languages as $lang)
                <tr class="border-t border-slate-100">
                    <td class="p-3">
                        @if($lang->flag)
                            <img src="{{ asset('storage/'.$lang->flag) }}" alt="" class="w-8 h-6 object-cover rounded">
                        @else
                            <span class="text-slate-300"><i class="fa-solid fa-flag"></i></span>
                        @endif
                    </td>
                    <td class="p-3 font-bold text-slate-800">{{ $lang->name }}</td>
                    <td class="p-3" dir="auto">{{ $lang->native_name }}</td>
                    <td class="p-3 font-mono">{{ $lang->code }}</td>
                    <td class="p-3 font-mono">{{ $lang->locale }}</td>
                    <td class="p-3 uppercase text-xs">{{ $lang->direction }}</td>
                    <td class="p-3">
                        @if($lang->is_default)
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">Default</span>
                        @else
                            <form method="POST" action="{{ route('admin.settings.languages.default', $lang) }}">@csrf
                                <button class="text-xs text-violet-600 hover:underline">Make default</button>
                            </form>
                        @endif
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs font-bold rounded-full {{ $lang->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $lang->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-3">{{ $lang->sort_order }}</td>
                    <td class="p-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.settings.languages.translations', $lang) }}"
                           class="text-emerald-600 hover:underline text-xs font-bold mr-2">
                            <i class="fa-solid fa-language"></i> Translate
                        </a>
                        <button @click='open=true; edit={{ $lang->id }}; form=@json($lang)'
                                class="text-violet-600 hover:underline text-xs font-bold mr-2">Edit</button>
                        @unless($lang->is_default)
                        <form method="POST" action="{{ route('admin.settings.languages.destroy', $lang) }}" class="inline"
                              onsubmit="return confirm('Delete this language?')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline text-xs font-bold">Delete</button>
                        </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="p-8 text-center text-slate-400">No languages yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open=false">
        <form method="POST" enctype="multipart/form-data"
              :action="edit ? '{{ url('admin/settings/languages') }}/' + edit : '{{ route('admin.settings.languages.store') }}'"
              class="bg-white rounded-3xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            @csrf
            <template x-if="edit">@method('PUT')</template>
            <h3 class="text-lg font-black" x-text="edit ? 'Edit Language' : 'Add Language'"></h3>

            <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Name</span>
                    <input type="text" name="name" x-model="form.name" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Native Name</span>
                    <input type="text" name="native_name" x-model="form.native_name" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">ISO Code</span>
                    <input type="text" name="code" x-model="form.code" required maxlength="10" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Locale</span>
                    <input type="text" name="locale" x-model="form.locale" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Direction</span>
                    <select name="direction" x-model="form.direction" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="ltr">LTR</option>
                        <option value="rtl">RTL</option>
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Sort Order</span>
                    <input type="number" name="sort_order" x-model="form.sort_order" min="0" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm col-span-2">
                    <span class="text-xs font-bold text-slate-500">Flag (PNG/SVG, ≤1MB)</span>
                    <input type="file" name="flag_file" accept="image/*" class="w-full mt-1 text-sm">
                </label>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" :checked="form.is_active"> Active
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" :checked="form.is_default"> Set as default
                </label>
            </div>

            <div class="flex gap-2 justify-end pt-2">
                <button type="button" @click="open=false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">Cancel</button>
                <button class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
