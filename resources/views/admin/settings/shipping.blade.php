@extends('admin.settings.layout')

@section('settings-content')
<div class="space-y-6"
     x-data="{
        showCountryModal: false,
        showRegionModal: false,
        isEditCountry: false,
        isEditRegion: false,
        country: { id: null, name: '', cost: '', position: 0, status: true },
        region: { id: null, country_id: null, name: '', cost: '', position: 0, status: true },
        openCreateCountry() {
            this.isEditCountry = false;
            this.country = { id: null, name: '', cost: '', position: 0, status: true };
            this.showCountryModal = true;
        },
        openEditCountry(c) {
            this.isEditCountry = true;
            this.country = { id: c.id, name: c.name, cost: c.cost ?? '', position: c.position, status: !!c.status };
            this.showCountryModal = true;
        },
        openCreateRegion(countryId) {
            this.isEditRegion = false;
            this.region = { id: null, country_id: countryId, name: '', cost: '', position: 0, status: true };
            this.showRegionModal = true;
        },
        openEditRegion(r) {
            this.isEditRegion = true;
            this.region = { id: r.id, country_id: r.country_id, name: r.name, cost: r.cost ?? '', position: r.position, status: !!r.status };
            this.showRegionModal = true;
        }
     }">

    {{-- ─────────────── Free Shipping Settings ─────────────── --}}
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-gift"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">{{ __('app.admin_settings_shipping_free_title') }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('app.admin_settings_shipping_free_subtitle') }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.settings.shipping.threshold') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf @method('PUT')

            <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                <div>
                    <p class="font-bold text-slate-800 text-sm">{{ __('app.admin_settings_shipping_free_enable_label') }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_settings_shipping_free_enable_hint') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="free_shipping_enabled" value="1" class="sr-only peer" {{ $freeShippingEnabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-5"></div>
                </label>
            </label>

            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="space-y-2 flex-1 w-full">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_free_threshold_label') }}</label>
                    <input type="number" min="0" step="0.01" name="free_shipping_threshold" value="{{ $freeThreshold }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5 space-y-5">
                <h4 class="text-sm font-bold text-slate-800">{{ __('app.admin_settings_shipping_free_display_title') }}</h4>

                <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">{{ __('app.admin_settings_shipping_free_header_bar_label') }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_settings_shipping_free_header_bar_hint') }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="free_shipping_show_in_header" value="1" class="sr-only peer" {{ $freeShippingShowInHeader ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-5"></div>
                    </label>
                </label>

                <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">{{ __('app.admin_settings_shipping_free_popup_label') }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_settings_shipping_free_popup_hint') }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="free_shipping_popup_enabled" value="1" class="sr-only peer" {{ $freeShippingPopupEnabled ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-5"></div>
                    </label>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_free_popup_title_label') }}</label>
                        <input type="text" name="free_shipping_popup_title" value="{{ $freeShippingPopupTitle }}" maxlength="150" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_free_popup_image_label') }}</label>
                        <input type="file" name="free_shipping_popup_image" accept="image/*" class="w-full text-sm">
                        @if(site_setting_url('free_shipping_popup_image'))
                            <div class="flex items-center gap-3 mt-2">
                                <img src="{{ site_setting_url('free_shipping_popup_image') }}" alt="" class="h-16 w-auto object-cover rounded-lg border border-slate-100">
                                <label class="flex items-center gap-2 text-xs text-rose-600">
                                    <input type="checkbox" name="remove_free_shipping_popup_image" value="1"> {{ __('app.admin_settings_shipping_free_popup_remove_image') }}
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_free_popup_message_label') }}</label>
                    <textarea name="free_shipping_popup_message" rows="3" maxlength="500" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ $freeShippingPopupMessage }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="h-11 px-6 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-md shadow-emerald-500/20">
                    {{ __('app.admin_settings_shipping_free_btn_save') }}
                </button>
            </div>
        </form>
    </div>


    {{-- ─────────────── Countries + Regions ─────────────── --}}
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-2xl bg-violet-50 flex items-center justify-center text-violet-600">
                    <i class="fa-solid fa-globe"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">{{ __('app.admin_settings_shipping_countries_title') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('app.admin_settings_shipping_countries_subtitle') }}</p>
                </div>
            </div>
            <button @click="openCreateCountry()" class="bg-violet-600 hover:bg-violet-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md shadow-violet-500/20 transition-colors">
                <i class="fa-solid fa-plus ml-2"></i> {{ __('app.admin_settings_shipping_add_country') }}
            </button>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($countries as $country)
            <div class="p-5" x-data="{ open: true }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <button type="button" @click="open = !open" class="h-9 w-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                            <i class="fa-solid" :class="open ? 'fa-chevron-down' : 'fa-chevron-left'"></i>
                        </button>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold text-slate-900">{{ $country->name }}</h4>
                                @if($country->status)
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">{{ __('app.admin_settings_shipping_badge_active') }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">{{ __('app.admin_settings_shipping_badge_inactive') }}</span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                    {{ $country->regions->count() }} {{ __('app.admin_settings_shipping_regions_count') }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                @if($country->cost !== null)
                                    {{ __('app.admin_settings_shipping_country_price') }} <span class="font-bold text-violet-600">{{ money($country->cost) }}</span>
                                @else
                                    <span class="italic">{{ __('app.admin_settings_shipping_country_no_price') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openCreateRegion({{ $country->id }})" class="text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-3 py-2 rounded-xl">
                            <i class="fa-solid fa-plus ml-1"></i> {{ __('app.admin_settings_shipping_add_region') }}
                        </button>
                        <button @click="openEditCountry(@js($country))" class="h-9 w-9 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-600 flex items-center justify-center">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.settings.shipping.countries.destroy', $country) }}" data-ajax-confirm="{{ __('app.admin_settings_shipping_confirm_delete_country') }}" data-ajax-remove class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="h-9 w-9 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div x-show="open" x-collapse class="mt-4 pl-12">
                    @if($country->regions->isEmpty())
                        <div class="text-center text-sm text-slate-400 py-6 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                            <i class="fa-solid fa-location-dot text-2xl text-slate-300 mb-2"></i>
                            <p>{{ __('app.admin_settings_shipping_no_regions') }}</p>
                        </div>
                    @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase">
                                <tr>
                                    <th class="px-4 py-2.5 text-right">{{ __('app.admin_settings_shipping_col_region') }}</th>
                                    <th class="px-4 py-2.5 text-right">{{ __('app.admin_settings_shipping_col_region_price') }}</th>
                                    <th class="px-4 py-2.5 text-right">{{ __('app.admin_settings_shipping_col_total_price') }}</th>
                                    <th class="px-4 py-2.5 text-right">{{ __('app.admin_settings_shipping_col_status') }}</th>
                                    <th class="px-4 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($country->regions as $region)
                                    @php
                                        $regionCost = $region->cost !== null ? (float) $region->cost : 0;
                                        $countryCost = $country->cost !== null ? (float) $country->cost : 0;
                                        $total = $countryCost + $regionCost;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $region->name }}</td>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $region->cost !== null ? money($region->cost) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-violet-600">{{ money($total) }}</td>
                                        <td class="px-4 py-3">
                                            @if($region->status)
                                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">{{ __('app.admin_settings_shipping_badge_active') }}</span>
                                            @else
                                                <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">{{ __('app.admin_settings_shipping_badge_inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2 justify-end">
                                                <button @click="openEditRegion(@js($region))" class="text-violet-600 hover:text-violet-800">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.settings.shipping.regions.destroy', $region) }}" data-ajax-confirm="{{ __('app.admin_settings_shipping_confirm_delete_region') }}" data-ajax-remove class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-500">
                <i class="fa-solid fa-earth-americas text-5xl text-slate-300 mb-3"></i>
                <p class="font-semibold">{{ __('app.admin_settings_shipping_empty') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ __('app.admin_settings_shipping_empty_hint') }}</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ─────────────── Country Modal ─────────────── --}}
    <div x-show="showCountryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6" @click.outside="showCountryModal = false">
            <h4 class="text-lg font-bold mb-4" x-text="isEditCountry ? '{{ __('app.admin_settings_shipping_country_modal_edit') }}' : '{{ __('app.admin_settings_shipping_country_modal_add') }}'"></h4>
            <form :action="isEditCountry ? '{{ url('admin/settings/shipping/countries') }}/' + country.id : '{{ route('admin.settings.shipping.countries.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditCountry"><input type="hidden" name="_method" value="PUT"></template>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_country_field_name') }}</label>
                    <input type="text" name="name" x-model="country.name" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_country_field_cost') }}</label>
                        <input type="number" step="0.01" min="0" name="cost" x-model="country.cost" placeholder="اتركه فارغًا لعدم وجود سعر" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <p class="text-[11px] text-slate-400">إذا تم تحديده يُجمع مع {{ __('app.admin_settings_shipping_col_region_price') }}.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_country_field_position') }}</label>
                        <input type="number" name="position" x-model="country.position" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="status" value="1" x-model="country.status" class="rounded">
                    <span class="text-sm font-semibold text-slate-700">{{ __('app.admin_settings_shipping_badge_active') }}</span>
                </label>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 h-11 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-2xl">{{ __('app.admin_settings_shipping_country_btn_save') }}</button>
                    <button type="button" @click="showCountryModal = false" class="h-11 px-6 bg-slate-100 rounded-2xl font-bold">{{ __('app.admin_settings_shipping_country_btn_cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─────────────── Region Modal ─────────────── --}}
    <div x-show="showRegionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6" @click.outside="showRegionModal = false">
            <h4 class="text-lg font-bold mb-4" x-text="isEditRegion ? '{{ __('app.admin_settings_shipping_region_modal_edit') }}' : '{{ __('app.admin_settings_shipping_region_modal_add') }}'"></h4>
            <form :action="isEditRegion ? '{{ url('admin/settings/shipping/regions') }}/' + region.id : '{{ route('admin.settings.shipping.regions.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditRegion"><input type="hidden" name="_method" value="PUT"></template>
                <template x-if="!isEditRegion"><input type="hidden" name="country_id" :value="region.country_id"></template>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">اسم {{ __('app.admin_settings_shipping_col_region') }} *</label>
                    <input type="text" name="name" x-model="region.name" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_col_region_price') }} (EGP) — اختياري</label>
                        <input type="number" step="0.01" min="0" name="cost" x-model="region.cost" placeholder="اتركه فارغًا" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <p class="text-[11px] text-slate-400">{{ __('app.admin_settings_shipping_region_field_cost_hint') }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_shipping_country_field_position') }}</label>
                        <input type="number" name="position" x-model="region.position" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="status" value="1" x-model="region.status" class="rounded">
                    <span class="text-sm font-semibold text-slate-700">{{ __('app.admin_settings_shipping_badge_active') }}</span>
                </label>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 h-11 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-2xl">{{ __('app.admin_settings_shipping_region_btn_save') }}</button>
                    <button type="button" @click="showRegionModal = false" class="h-11 px-6 bg-slate-100 rounded-2xl font-bold">{{ __('app.admin_settings_shipping_region_btn_cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
