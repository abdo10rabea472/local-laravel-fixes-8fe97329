@props(['title' => '', 'subtitle' => null, 'back' => null, 'backLabel' => 'العودة للقائمة'])

<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @isset($actions){{ $actions }}@endisset
            @if($back)
                <a href="{{ $back }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary-600 hover:text-primary-700 dark:text-primary-500">
                    <i class="fa-solid fa-arrow-right"></i> {{ $backLabel }}
                </a>
            @endif
        </div>
    </div>

    {{-- Two-column grid: main (right/wide) + side (left/narrow) --}}
    @isset($side)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6 min-w-0 order-2 lg:order-1">
                {{ $slot }}
            </div>
            <aside class="lg:col-span-1 space-y-6 order-1 lg:order-2 lg:sticky lg:top-4">
                {{ $side }}
            </aside>
        </div>
    @else
        <div class="space-y-6">
            {{ $slot }}
        </div>
    @endisset
</div>
