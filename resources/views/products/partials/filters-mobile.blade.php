<div class="space-y-1 mb-6">
    <a href="{{ route('products.index', request()->except(['college', 'department', 'page'])) }}"
       class="block px-4 py-2.5 rounded-xl text-xs font-bold {{ !$activeCollege ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600' }}">
        {{ __('app.shared_all_colleges') }}
    </a>
    @foreach($colleges as $college)
    <a href="{{ route('products.index', array_merge(request()->except(['department', 'page']), ['college' => $college->slug])) }}"
       class="block px-4 py-2.5 rounded-xl text-xs font-bold {{ ($activeCollege?->id ?? null) === $college->id ? 'bg-cyan-50 text-cyan-700' : 'text-slate-600' }}">
        {{ $college->name }}
    </a>
    @endforeach
</div>
@if($activeCollege && $activeCollege->children->isNotEmpty())
<div class="flex flex-wrap gap-2">
    @foreach($activeCollege->children as $dept)
    <a href="{{ route('products.index', array_merge(request()->except('page'), ['college' => $activeCollege->slug, 'department' => $dept->slug])) }}"
       class="px-3 py-1.5 text-[11px] font-semibold rounded-xl border {{ ($activeDepartment?->id ?? null) === $dept->id ? 'bg-violet-600 text-white border-violet-600' : 'border-slate-200' }}">
        {{ $dept->name }}
    </a>
    @endforeach
</div>
@endif
