@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-5 border-b border-slate-100">
        <div>
            <h3 class="text-base font-bold text-slate-800">Static Pages</h3>
            <p class="text-xs text-slate-500 mt-1">Manage the content of pages such as About, FAQs, Privacy, Returns, etc.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md shadow-emerald-500/20 transition-colors">
            <i class="fa-solid fa-plus"></i> New Page
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider">
                <tr>
                    <th class="text-left p-4 font-bold">Title</th>
                    <th class="text-left p-4 font-bold">Slug</th>
                    <th class="text-left p-4 font-bold">Status</th>
                    <th class="text-left p-4 font-bold">Order</th>
                    <th class="text-right p-4 font-bold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr class="border-t border-slate-100 hover:bg-slate-50/60 transition-colors">
                    <td class="p-4 font-semibold text-slate-800">
                        {{ $page->title }}
                        @if(in_array($page->slug, $systemSlugs ?? [], true))
                            <span class="ml-2 inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                                <i class="fa-solid fa-lock text-[9px]"></i> System
                            </span>
                        @endif
                    </td>
                    <td class="p-4 text-slate-500 font-mono text-xs">{{ $page->slug }}</td>
                    <td class="p-4">
                        @if($page->status)
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Disabled
                            </span>
                        @endif
                    </td>
                    <td class="p-4 text-slate-600">{{ $page->sort_order }}</td>
                    <td class="p-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ url(app()->getLocale() . '/' . $page->slug) }}" target="_blank"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-700 transition-colors"
                               title="View page">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.pages.edit', $page) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-600 hover:text-violet-800 transition-colors"
                               title="Edit page">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            @if(in_array($page->slug, $systemSlugs ?? [], true))
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-50 text-slate-300 cursor-not-allowed"
                                      title="System page — cannot be deleted. Toggle status to hide it.">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" data-ajax-confirm="Delete this page?" data-ajax-remove>
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-500 hover:text-rose-700 transition-colors"
                                            title="Delete page">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-12 text-center">
                        <div class="flex flex-col items-center gap-3 text-slate-500">
                            <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center">
                                <i class="fa-solid fa-file-lines text-xl text-slate-400"></i>
                            </div>
                            <p class="font-semibold text-slate-700">No pages yet</p>
                            <p class="text-xs">Click "New Page" to create your first static page.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $pages->links() }}</div>
</div>
@endsection
