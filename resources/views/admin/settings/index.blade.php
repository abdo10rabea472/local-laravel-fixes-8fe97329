@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
        <p class="text-xs text-slate-500 mt-1">{{ $subtitle }}</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}?tab={{ $tab }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        @include("admin.settings.partials.{$tab}")

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <p class="text-xs text-slate-500"><i class="fa-solid fa-circle-info mr-1"></i> {{ __('app.admin_settings_index_hint') }}</p>
            <button type="submit" class="h-11 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-floppy-disk mr-2"></i> {{ __('app.admin_settings_index_save') }}
            </button>
        </div>
    </form>
</div>
@endsection