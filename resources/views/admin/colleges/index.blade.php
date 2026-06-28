@extends('admin.layouts.app')
@section('title', 'Colleges')

@section('content')
<x-admin.page title="College Categories" subtitle="Manage college pages: name, icon, colors, description, and SEO.">
    <x-admin.card title="All Colleges" icon="fa-building-columns" padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800 text-gray-500 dark:text-gray-400 text-xs">
                    <tr>
                        <th class="text-left p-4">College</th>
                        <th class="text-left p-4">Colors</th>
                        <th class="text-left p-4">Subcategories</th>
                        <th class="text-left p-4">Products</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-dark-800/50">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                @if($college->image)
                                    <img src="{{ asset('storage/' . $college->image) }}" class="h-10 w-10 object-contain rounded-xl bg-gray-50 dark:bg-dark-800 p-1 shrink-0" alt="">
                                @else
                                    <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0" style="background: {{ $college->primary_color ?? '#6366f1' }}">
                                        {{ mb_substr($college->name, 0, 2) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $college->name }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">{{ $college->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-1">
                                <span class="h-6 w-6 rounded-lg border border-gray-200 dark:border-gray-700" style="background:{{ $college->primary_color ?? '#6366f1' }}"></span>
                                <span class="h-6 w-6 rounded-lg border border-gray-200 dark:border-gray-700" style="background:{{ $college->secondary_color ?? '#8b5cf6' }}"></span>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-gray-700 dark:text-gray-300">{{ $college->children_count }}</td>
                        <td class="p-4 font-mono text-gray-700 dark:text-gray-300">{{ $college->products_count }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $college->status ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-dark-800 text-gray-500' }}">
                                {{ $college->status ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.colleges.edit', $college) }}" class="text-primary-600 hover:text-primary-700" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="{{ route('admin.subcategories.index', ['college_id' => $college->id]) }}" class="text-indigo-500 hover:text-indigo-600" title="Subcategories"><i class="fa-solid fa-sitemap"></i></a>
                                <a href="{{ route('category.show', $college->slug) }}" target="_blank" class="text-gray-500 hover:text-gray-700" title="Preview"><i class="fa-solid fa-eye"></i></a>
                                <form method="POST" action="{{ route('admin.colleges.destroy', $college) }}" data-ajax-confirm="Delete college?" data-ajax-remove>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-600"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-12 text-center text-gray-400">
                        <i class="fa-solid fa-building-columns text-3xl mb-3 block"></i>
                        No colleges yet.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($colleges->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">{{ $colleges->links() }}</div>
        @endif
    </x-admin.card>

    <x-slot:side>
        <x-admin.card title="Quick actions" icon="fa-bolt">
            <a href="{{ route('admin.colleges.create') }}" class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 transition-colors">
                <i class="fa-solid fa-plus"></i> Add college
            </a>
            <a href="{{ route('admin.subcategories.index') }}" class="mt-2 w-full h-11 inline-flex items-center justify-center gap-2 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                <i class="fa-solid fa-sitemap"></i> Subcategories
            </a>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
