<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seo = $seo ?? [];
        $seoTitle = $seo['seo_title'] ?? 'UNI-LAB MARKET';
        $seoDescription = $seo['seo_description'] ?? '';
        $seoKeywords = $seo['seo_keywords'] ?? '';
        $canonicalUrl = $seo['canonical_url'] ?? url()->current();
        $ogTitle = $seo['og_title'] ?? $seoTitle;
        $ogDescription = $seo['og_description'] ?? $seoDescription;
        $ogImage = $seo['og_image'] ?? site_setting_url('default_og_image', asset('imges/photo_٢٠٢٦-٠٢-٢٥_٠٨-٤٧-٣٧-removebg-preview.png'));
        $schemaMarkup = $seo['schema_markup'] ?? null;
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if($seoKeywords)
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @if($schemaMarkup)
        <script type="application/ld+json">{!! $schemaMarkup !!}</script>
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ss.css') }}">
    @stack('styles')

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @include('components.front-header')
    @include('components.welcome-popup')
    @include('components.free-shipping-popup')


    <main>
        @yield('content')
    </main>

    @include('components.front-footer')

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script defer src="{{ asset('js/swiper.js') }}"></script>
    @stack('scripts')
</body>
</html>
