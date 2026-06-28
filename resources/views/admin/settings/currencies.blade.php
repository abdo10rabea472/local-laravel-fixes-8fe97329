@extends('admin.settings.layout', ['activeTab' => 'currencies'])

@section('title', 'Currencies')

@section('settings-content')
<div class="space-y-6" x-data="{ open:false, edit:null, form:{} }">
    @if(session('success'))<div class="p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm font-bold">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="p-4 rounded-2xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first() }}</div>@endif

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-black text-slate-800">Currencies</h2>
                <p class="text-sm text-slate-500">All product prices are stored in the default currency. Other currencies convert via exchange rate.</p>
            </div>
            <button @click="open=true; edit=null; form={symbol_position:'before', decimals:2, decimal_separator:'.', thousands_separator:',', exchange_rate:1, is_active:true, sort_order:({{ $currencies->count() + 1 }})}"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">
                <i class="fa-solid fa-plus mr-1"></i> Add Currency
            </button>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Symbol</th>
                    <th class="p-3 text-left">Rate</th>
                    <th class="p-3 text-left">Preview</th>
                    <th class="p-3 text-left">Default</th>
                    <th class="p-3 text-left">Active</th>
                    <th class="p-3 text-left">Order</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($currencies as $cur)
                <tr class="border-t border-slate-100">
                    <td class="p-3 font-bold text-slate-800">{{ $cur->name }}</td>
                    <td class="p-3 font-mono">{{ $cur->code }}</td>
                    <td class="p-3">{{ $cur->symbol }} <span class="text-xs text-slate-400">({{ $cur->symbol_position }})</span></td>
                    <td class="p-3 font-mono">{{ rtrim(rtrim(number_format($cur->exchange_rate, 8, '.', ''), '0'), '.') }}</td>
                    <td class="p-3 text-slate-600">{{ $cur->format(1234.5) }}</td>
                    <td class="p-3">
                        @if($cur->is_default)
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">Default</span>
                        @else
                            <form method="POST" action="{{ route('admin.settings.currencies.default', $cur) }}">@csrf
                                <button class="text-xs text-violet-600 hover:underline">Make default</button>
                            </form>
                        @endif
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs font-bold rounded-full {{ $cur->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $cur->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-3">{{ $cur->sort_order }}</td>
                    <td class="p-3 text-right whitespace-nowrap">
                        <button @click='open=true; edit={{ $cur->id }}; form=@json($cur)'
                                class="text-violet-600 hover:underline text-xs font-bold mr-2">Edit</button>
                        @unless($cur->is_default)
                        <form method="POST" action="{{ route('admin.settings.currencies.destroy', $cur) }}" class="inline"
                              onsubmit="return confirm('Delete this currency?')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline text-xs font-bold">Delete</button>
                        </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="p-8 text-center text-slate-400">No currencies yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open=false">
        <form method="POST"
              :action="edit ? '{{ url('admin/settings/currencies') }}/' + edit : '{{ route('admin.settings.currencies.store') }}'"
              class="bg-white rounded-3xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            @csrf
            <template x-if="edit">@method('PUT')</template>
            <h3 class="text-lg font-black" x-text="edit ? 'Edit Currency' : 'Add Currency'"></h3>

            <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm col-span-2">
                    <span class="text-xs font-bold text-slate-500">Name</span>
                    <input type="text" name="name" x-model="form.name" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Code (ISO)</span>
                    <input type="text" name="code" x-model="form.code" required maxlength="10" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Symbol</span>
                    <input type="text" name="symbol" x-model="form.symbol" required maxlength="10" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Symbol Position</span>
                    <select name="symbol_position" x-model="form.symbol_position" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="before">Before ($100)</option>
                        <option value="after">After (100 ج.م)</option>
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Decimals</span>
                    <input type="number" name="decimals" x-model="form.decimals" min="0" max="6" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Decimal Separator</span>
                    <input type="text" name="decimal_separator" x-model="form.decimal_separator" maxlength="4" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Thousands Separator</span>
                    <input type="text" name="thousands_separator" x-model="form.thousands_separator" maxlength="4" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Exchange Rate <span class="text-slate-400">(per 1 default)</span></span>
                    <input type="number" step="0.00000001" name="exchange_rate" x-model="form.exchange_rate" required class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                </label>
                <label class="block text-sm">
                    <span class="text-xs font-bold text-slate-500">Sort Order</span>
                    <input type="number" name="sort_order" x-model="form.sort_order" min="0" class="w-full h-10 px-3 mt-1 bg-slate-50 border border-slate-200 rounded-xl text-sm">
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
