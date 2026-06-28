@extends('admin.layouts.app')
@section('title', $title)

@section('content')
<form method="POST" action="{{ $category->exists ? route('admin.colleges.update', $category) : route('admin.colleges.store') }}" enctype="multipart/form-data">
    @csrf
    @if($category->exists) @method('PUT') @endif

    <x-admin.page :title="$title" subtitle="College name, icon, colors, description and SEO." :back="route('admin.colleges.index')" backLabel="Back to colleges">
        <x-admin.card title="College Info" icon="fa-building-columns">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">College name *</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required placeholder="e.g., Medicine"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="auto-generated"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Display order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">College page description</label>
                <textarea name="description" rows="4" placeholder="Short description about the college and its products..."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('description', $category->description) }}</textarea>
            </div>
        </x-admin.card>

        <x-admin.card title="Icon & Banner" icon="fa-image">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">College icon</label>
                    @if($category->image)
                    <div class="mb-3 p-3 bg-gray-50 dark:bg-dark-800 rounded-2xl inline-block">
                        <img src="{{ asset('storage/' . $category->image) }}" alt="" class="h-16 w-16 object-contain">
                    </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-bold">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">College page banner</label>
                    @if($category->banner)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $category->banner) }}" alt="" class="h-20 rounded-2xl object-cover">
                    </div>
                    @endif
                    <input type="file" name="banner" accept="image/*"
                           class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-bold">
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="College Page Colors" icon="fa-palette">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Primary color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $category->primary_color ?? '#6366f1') }}"
                               class="h-11 w-16 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer bg-transparent">
                        <input type="text" value="{{ old('primary_color', $category->primary_color ?? '#6366f1') }}" readonly
                               class="flex-1 h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">Secondary color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $category->secondary_color ?? '#8b5cf6') }}"
                               class="h-11 w-16 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer bg-transparent">
                        <input type="text" value="{{ old('secondary_color', $category->secondary_color ?? '#8b5cf6') }}" readonly
                               class="flex-1 h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono">
                    </div>
                </div>
            </div>
            <div class="mt-4 p-4 rounded-xl"
                 style="background: linear-gradient(135deg, {{ old('primary_color', $category->primary_color ?? '#6366f1') }}, {{ old('secondary_color', $category->secondary_color ?? '#8b5cf6') }})">
                <p class="text-white font-bold text-sm">Color preview</p>
            </div>
        </x-admin.card>

        <x-admin.card title="SEO Settings" icon="fa-magnifying-glass-chart">
            <div class="space-y-3">
                <input type="text" name="seo_title" value="{{ old('seo_title', $category->exists ? $category->getRawOriginal('seo_title') : '') }}" placeholder="SEO Title"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <textarea name="seo_description" rows="2" placeholder="Meta Description"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">{{ old('seo_description', $category->exists ? $category->getRawOriginal('seo_description') : '') }}</textarea>
                <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $category->seo_keywords) }}" placeholder="Keywords"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $category->canonical_url) }}" placeholder="Canonical URL"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="og_title" value="{{ old('og_title', $category->og_title) }}" placeholder="OG Title"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                    <input type="text" name="og_description" value="{{ old('og_description', $category->og_description) }}" placeholder="OG Description"
                           class="w-full h-11 px-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:border-primary-500 focus:outline-none">
                </div>
                <textarea name="schema_markup" rows="3" placeholder="Schema JSON-LD"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:border-primary-500 focus:outline-none">{{ old('schema_markup', $category->schema_markup) }}</textarea>
            </div>
        </x-admin.card>

        <x-slot:side>
            <x-admin.card title="College status" icon="fa-toggle-on">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                    <input type="checkbox" name="status" value="1" @checked(old('status', $category->exists ? $category->status : true))
                           class="rounded text-primary-600 focus:ring-primary-500">
                    College is active and visible
                </label>

                <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <button type="submit" class="w-full h-12 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                        <i class="fa-solid fa-floppy-disk mr-1"></i>
                        {{ $category->exists ? 'Save Changes' : 'Add College' }}
                    </button>
                    <a href="{{ route('admin.colleges.index') }}" class="w-full h-11 inline-flex items-center justify-center bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        Cancel
                    </a>
                </div>
            </x-admin.card>
        </x-slot:side>
    </x-admin.page>
</form>
@endsection
