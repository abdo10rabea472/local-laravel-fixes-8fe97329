@extends('admin.settings.layout')

@section('settings-content')
<div class="space-y-6" x-data="{
    showModal: false,
    isEdit: false,
    form: { id: null, parent_id: '', location: '{{ $currentLocation }}', title: '', url: '', type: 'link', coupon_code: '', coupon_percent: '', icon: '', target: '_self', position: 0, status: true },
    openCreate() {
        this.isEdit = false;
        this.form = { id: null, parent_id: '', location: '{{ $currentLocation }}', title: '', url: '', type: 'link', coupon_code: '', coupon_percent: '', icon: '', target: '_self', position: 0, status: true };
        this.showModal = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.form = { ...item, parent_id: item.parent_id ?? '', location: item.location ?? '{{ $currentLocation }}', type: item.type || 'link', coupon_code: item.coupon_code || '', coupon_percent: item.coupon_percent || '', status: !!item.status };
        this.showModal = true;
    }
}">
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800">{{ __('app.admin_settings_header_menu_title') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('app.admin_settings_header_menu_subtitle') }}</p>
            </div>
            <button @click="openCreate()" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md shadow-emerald-500/20 transition-colors">
                <i class="fa-solid fa-plus ml-2"></i> {{ __('app.admin_settings_header_menu_add_btn') }}
            </button>
        </div>

        {{-- Locations tabs --}}
        <div class="px-6 pt-4 border-b border-slate-100">
            <nav class="flex gap-2 overflow-x-auto pb-2">
                @foreach($locations as $key => $label)
                <a href="{{ route('admin.settings.header-menu', ['location' => $key]) }}"
                   class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $currentLocation === $key ? 'bg-violet-600 text-white shadow-md shadow-violet-500/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                </a>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            @if($items->isNotEmpty())
            <div id="sortable-root" class="space-y-3" data-location="{{ $currentLocation }}">
                @foreach($items as $item)
                <div class="sortable-item border border-slate-200 rounded-2xl overflow-hidden bg-white" data-id="{{ $item->id }}">
                    <div class="flex items-center gap-4 p-4 bg-slate-50/50">
                        <div class="sortable-handle cursor-grab active:cursor-grabbing h-8 w-8 rounded-lg bg-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-300 transition-colors">
                            <i class="fa-solid fa-grip-vertical"></i>
                        </div>
                        <div class="h-8 w-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-black">
                            {{ $item->position }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-800 flex items-center gap-2">
                                @if($item->icon)
                                    <i class="fa-solid {{ $item->icon }} text-slate-400"></i>
                                @endif
                                {{ $item->title }}
                                @if($item->children->isNotEmpty())
                                    <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">{{ $item->children->count() }} {{ __('app.admin_settings_header_menu_child_badge') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 truncate">{{ $item->url }}</div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if($item->status)
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">{{ __('app.admin_settings_header_menu_badge_active') }}</span>
                            @else
                                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-full">{{ __('app.admin_settings_header_menu_badge_inactive') }}</span>
                            @endif
                            <button @click="openEdit(@js($item))" class="text-violet-600 hover:text-violet-800">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.settings.header-menu.destroy', $item) }}" data-ajax-confirm="{{ __('app.admin_settings_header_menu_confirm_delete') }}" data-ajax-remove class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($item->children->isNotEmpty())
                    <div class="sortable-children border-t border-slate-100 bg-white p-3 space-y-2" data-parent-id="{{ $item->id }}">
                        @foreach($item->children as $child)
                        <div class="sortable-child flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100" data-id="{{ $child->id }}">
                            <div class="sortable-handle-child cursor-grab active:cursor-grabbing text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-grip-lines"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-slate-700">
                                    @if($child->icon)
                                        <i class="fa-solid {{ $child->icon }} text-slate-400 ml-1"></i>
                                    @endif
                                    {{ $child->title }}
                                </div>
                                <div class="text-xs text-slate-400 truncate">{{ $child->url }}</div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($child->status)
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">{{ __('app.admin_settings_header_menu_badge_active') }}</span>
                                @else
                                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">{{ __('app.admin_settings_header_menu_badge_inactive') }}</span>
                                @endif
                                <button @click="openEdit(@js($child))" class="text-violet-600 hover:text-violet-800 text-sm">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.settings.header-menu.destroy', $child) }}" data-ajax-confirm="{{ __('app.admin_settings_header_menu_confirm_delete_child') }}" data-ajax-remove class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-16 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <i class="fa-solid fa-bars text-4xl text-slate-300 mb-4"></i>
                <p class="text-slate-500">{{ __('app.admin_settings_header_menu_empty') }} {{ $locations[$currentLocation] }}.</p>
                <button @click="openCreate()" class="mt-4 text-sm font-bold text-violet-600 hover:text-violet-800">{{ __('app.admin_settings_header_menu_add_first') }}</button>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6" @click.outside="showModal = false">
            <h4 class="text-lg font-bold mb-4" :class="{'': true}" x-text="isEdit ? '{{ __('app.admin_settings_header_menu_modal_edit_title') }}' : '{{ __('app.admin_settings_header_menu_modal_add_title') }}'"></h4>
            <form :action="isEdit ? '{{ url('admin/settings/header-menu') }}/' + form.id : '{{ route('admin.settings.header-menu.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_title') }}</label>
                        <input type="text" name="title" x-model="form.title" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_icon') }}</label>
                        <input type="text" name="icon" x-model="form.icon" placeholder="fa-house" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_type') }}</label>
                    <select name="type" x-model="form.type" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="link">{{ __('app.admin_settings_header_menu_type_link') }}</option>
                        <option value="coupon">{{ __('app.admin_settings_header_menu_type_coupon') }}</option>
                    </select>
                </div>

                <template x-if="form.type === 'coupon'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_coupon_code') }}</label>
                            <input type="text" name="coupon_code" x-model="form.coupon_code" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_coupon_percent') }}</label>
                            <input type="number" min="0" max="100" name="coupon_percent" x-model="form.coupon_percent" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        </div>
                    </div>
                </template>

                <div class="space-y-2" x-show="form.type === 'link'">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_url') }}</label>
                    <input type="text" name="url" x-model="form.url" placeholder="/products أو https://..." class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    <p class="text-xs text-slate-400">{{ __('app.admin_settings_header_menu_field_url_hint') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_parent') }}</label>
                        <select name="parent_id" x-model="form.parent_id" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                            <option value="">{{ __('app.admin_settings_header_menu_option_none') }}</option>
                            @foreach($items as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_location') }}</label>
                        <select name="location" x-model="form.location" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                            @foreach($locations as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_target') }}</label>
                        <select name="target" x-model="form.target" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                            <option value="_self">{{ __('app.admin_settings_header_menu_target_self') }}</option>
                            <option value="_blank">{{ __('app.admin_settings_header_menu_target_blank') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_settings_header_menu_field_position') }}</label>
                        <input type="number" name="position" x-model="form.position" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                    <div class="flex items-center gap-2 h-11 mt-6">
                        <input type="checkbox" name="status" value="1" x-model="form.status" class="rounded">
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.admin_settings_header_menu_field_active') }}</label>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 h-11 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-2xl transition-colors">{{ __('app.admin_settings_header_menu_btn_save') }}</button>
                    <button type="button" @click="showModal = false" class="h-11 px-6 bg-slate-100 rounded-2xl font-bold">{{ __('app.admin_settings_header_menu_btn_cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function showToast(msg) {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    toast.style.cssText = 'position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#1f2937; color:white; padding:14px 24px; border-radius:9999px; z-index:99999; box-shadow:0 10px 15px -3px rgb(0 0 0 / 0.3);';
    toast.textContent = msg;
    toast.style.opacity = 1;
    setTimeout(() => { toast.style.opacity = 0; }, 2800);
}

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const reorderUrl = @json(route('admin.settings.header-menu.reorder'));

    function serializeList(container, parentId = null) {
        return Array.from(container.children).map((el, index) => ({
            id: el.dataset.id,
            position: index,
            parent_id: parentId,
        }));
    }

    function sendReorder(items) {
        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ items }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showToast('{{ __('app.admin_settings_header_menu_btn_save') }}');
            }
        }).catch(() => {
            showToast('Error saving order');
        });
    }

    const rootEl = document.getElementById('sortable-root');
    if (rootEl) {
        Sortable.create(rootEl, {
            handle: '.sortable-handle',
            animation: 150,
            onEnd: function () {
                let items = serializeList(rootEl, null);
                rootEl.querySelectorAll('.sortable-children').forEach(childContainer => {
                    const parentId = childContainer.dataset.parentId;
                    items = items.concat(serializeList(childContainer, parentId));
                });
                sendReorder(items);
            }
        });
    }

    document.querySelectorAll('.sortable-children').forEach(childContainer => {
        Sortable.create(childContainer, {
            handle: '.sortable-handle-child',
            animation: 150,
            onEnd: function () {
                let items = serializeList(rootEl, null);
                rootEl.querySelectorAll('.sortable-children').forEach(container => {
                    const parentId = container.dataset.parentId;
                    items = items.concat(serializeList(container, parentId));
                });
                sendReorder(items);
            }
        });
    });
});
</script>
@endpush
@endsection
