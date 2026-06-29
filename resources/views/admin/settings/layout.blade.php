@extends('admin.layouts.app')

@section('title', 'General Settings')

@php
$tabs = [
    'general' => [
        'label' => 'Site Info',
        'icon' => 'fa-globe',
        'route' => route('admin.settings.index', ['tab' => 'general']),
    ],
    'images' => [
        'label' => 'Images',
        'icon' => 'fa-images',
        'route' => route('admin.settings.index', ['tab' => 'images']),
    ],
    'contact' => [
        'label' => 'Contact Info',
        'icon' => 'fa-address-card',
        'route' => route('admin.settings.index', ['tab' => 'contact']),
    ],
    'ai' => [
        'label' => 'Artificial Intelligence',
        'icon' => 'fa-robot',
        'route' => route('admin.settings.index', ['tab' => 'ai']),
    ],
    'mail' => [
        'label' => 'Mail Settings',
        'icon' => 'fa-envelope',
        'route' => route('admin.settings.index', ['tab' => 'mail']),
    ],
    'languages' => [
        'label' => 'Languages',
        'icon' => 'fa-language',
        'route' => route('admin.settings.languages.index'),
    ],
    'currencies' => [
        'label' => 'Currencies',
        'icon' => 'fa-money-bill-wave',
        'route' => route('admin.settings.currencies.index'),
    ],
    'header-menu' => [
        'label' => 'Header',
        'icon' => 'fa-bars',
        'route' => route('admin.settings.header-menu'),
    ],
    'shipping' => [
        'label' => 'Shipping',
        'icon' => 'fa-truck-fast',
        'route' => route('admin.settings.shipping'),
    ],
    'payment-gateways' => [
        'label' => 'Payment Gateways',
        'icon' => 'fa-credit-card',
        'route' => route('admin.settings.payment-gateways.index'),
    ],
];

$seoTabs = [
    'homepage' => [
        'label' => 'Homepage',
        'icon' => 'fa-house',
        'route' => route('admin.homepage.edit'),
    ],
    'product-catalog' => [
        'label' => 'Products Page',
        'icon' => 'fa-boxes-packing',
        'route' => route('admin.product-catalog.edit'),
    ],
    'pages' => [
        'label' => 'Static Pages',
        'icon' => 'fa-file-lines',
        'route' => route('admin.pages.index'),
    ],
];

$activeTab = $activeTab ?? 'general';
$isSeoTab = in_array($activeTab, array_keys($seoTabs), true);
@endphp

@section('content')
<style>
    /* Dark-mode overrides scoped to the settings area */
    .dark .settings-area .bg-white { background-color: #1f2937 !important; }
    .dark .settings-area .bg-slate-50,
    .dark .settings-area .bg-gray-50 { background-color: #111827 !important; }
    .dark .settings-area .bg-slate-100 { background-color: #1f2937 !important; }
    .dark .settings-area .border-slate-100 { border-color: #374151 !important; }
    .dark .settings-area .border-slate-200,
    .dark .settings-area .border-gray-200 { border-color: #374151 !important; }
    .dark .settings-area .text-slate-800,
    .dark .settings-area .text-slate-700,
    .dark .settings-area .text-gray-800,
    .dark .settings-area .text-gray-900 { color: #f3f4f6 !important; }
    .dark .settings-area .text-slate-600 { color: #d1d5db !important; }
    .dark .settings-area .text-slate-500,
    .dark .settings-area .text-slate-400,
    .dark .settings-area .text-gray-500 { color: #9ca3af !important; }
    .dark .settings-area input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
    .dark .settings-area textarea,
    .dark .settings-area select {
        background-color: #111827 !important;
        border-color: #374151 !important;
        color: #f3f4f6 !important;
    }
    .dark .settings-area input::placeholder,
    .dark .settings-area textarea::placeholder { color: #6b7280 !important; }
    .dark .settings-area .hover\:bg-slate-50:hover,
    .dark .settings-area .hover\:bg-gray-50:hover { background-color: #374151 !important; }
    .dark .settings-area .bg-amber-50 { background-color: rgba(245,158,11,0.10) !important; }
    .dark .settings-area .bg-red-50,
    .dark .settings-area .bg-rose-50 { background-color: rgba(239,68,68,0.10) !important; }
    .dark .settings-area .bg-emerald-50,
    .dark .settings-area .bg-green-50 { background-color: rgba(16,185,129,0.10) !important; }
    .dark .settings-area .bg-blue-50,
    .dark .settings-area .bg-sky-50 { background-color: rgba(59,130,246,0.10) !important; }
    .dark .settings-area .bg-violet-50,
    .dark .settings-area .bg-indigo-50 { background-color: rgba(139,92,246,0.10) !important; }
    .dark .settings-area table tbody tr { background-color: transparent !important; }
    .dark .settings-area table thead { background-color: #111827 !important; }
    .dark .settings-area .divide-slate-100 > * + *,
    .dark .settings-area .divide-gray-100 > * + * { border-color: #374151 !important; }
</style>
<div class="settings-area space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r bg-violet-600 text-white group bg-violet-600 text-white shadow-lg">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative flex items-center gap-6 p-6 sm:p-8">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                <i class="fa-solid fa-sliders text-3xl"></i>
            </div>
            <div class="flex-1">
                <div class="text-xs font-bold text-emerald-100 mb-1">Settings Panel</div>
                <h1 class="text-2xl sm:text-3xl font-black">General Settings</h1>
                <p class="text-sm text-emerald-50 mt-1">Manage site settings, images, SEO, and static pages.</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6 items-start">
        {{-- Main Content --}}
        <div class="flex-1 min-w-0 order-2 md:order-1 w-full">
            @yield('settings-content')
        </div>

        {{-- Sidebar Tabs --}}
        <div class="w-full md:w-64 lg:w-72 shrink-0 order-1 md:order-2">

            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">General Settings</h3>
                </div>
                <nav class="p-2 space-y-1">
                    @foreach($tabs as $key => $tab)
                    <a href="{{ $tab['route'] }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ $activeTab === $key ? 'group bg-violet-600 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid {{ $tab['icon'] }} w-5 text-center"></i>
                        <span>{{ $tab['label'] }}</span>
                        @if($activeTab === $key)
                            <i class="fa-solid fa-chevron-right ml-auto text-xs"></i>
                        @endif
                    </a>
                    @endforeach
                </nav>

                <div class="p-4 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">SEO &amp; Content</h3>
                </div>
                <nav class="p-2 space-y-1">
                    @foreach($seoTabs as $key => $tab)
                    <a href="{{ $tab['route'] }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all {{ $activeTab === $key ? 'group bg-violet-600 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid {{ $tab['icon'] }} w-5 text-center"></i>
                        <span>{{ $tab['label'] }}</span>
                        @if($activeTab === $key)
                            <i class="fa-solid fa-chevron-right ml-auto text-xs"></i>
                        @endif
                    </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
