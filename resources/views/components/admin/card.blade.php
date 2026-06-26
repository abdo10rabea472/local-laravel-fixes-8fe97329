@props(['title' => null, 'subtitle' => null, 'icon' => null, 'padding' => 'p-5 sm:p-6'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm ' . $padding]) }}>
    @if($title)
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="min-w-0">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    @if($icon)<i class="fas {{ $icon }} text-primary-600"></i>@endif
                    {{ $title }}
                </h3>
                @if($subtitle)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($headerActions){{ $headerActions }}@endisset
        </div>
    @endif
    {{ $slot }}
</div>
