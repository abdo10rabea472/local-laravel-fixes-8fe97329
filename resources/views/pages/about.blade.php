@extends('layouts.front')

@section('content')
<section class="bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-800 text-white py-20">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $page?->title ?: __('app.about_hero_title') }}</h1>
        <p class="text-lg text-violet-100 max-w-2xl mx-auto">{{ $page?->seo_description ?: __('app.about_hero_subtitle') }}</p>
    </div>
</section>

@if($page && trim((string) $page->content) !== '')
    <section class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 prose prose-slate prose-lg max-w-none">
            {!! $page->content !!}
        </div>
    </section>
@else
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-4">{{ __('app.about_story_title') }}</h2>
                <p class="text-slate-600 leading-relaxed mb-4">{{ __('app.about_story_p1', ['years' => $stats['years']]) }}</p>
                <p class="text-slate-600 leading-relaxed">{{ __('app.about_story_p2') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-violet-50 p-6 rounded-2xl text-center">
                    <div class="text-3xl font-bold text-violet-700">{{ $stats['products'] }}+</div>
                    <div class="text-sm text-slate-600 mt-1">{{ __('app.about_stat_products') }}</div>
                </div>
                <div class="bg-indigo-50 p-6 rounded-2xl text-center">
                    <div class="text-3xl font-bold text-indigo-700">{{ $stats['categories'] }}</div>
                    <div class="text-sm text-slate-600 mt-1">{{ __('app.about_stat_categories') }}</div>
                </div>
                <div class="bg-emerald-50 p-6 rounded-2xl text-center">
                    <div class="text-3xl font-bold text-emerald-700">{{ $stats['customers'] }}+</div>
                    <div class="text-sm text-slate-600 mt-1">{{ __('app.about_stat_customers') }}</div>
                </div>
                <div class="bg-amber-50 p-6 rounded-2xl text-center">
                    <div class="text-3xl font-bold text-amber-700">{{ $stats['years'] }}+</div>
                    <div class="text-sm text-slate-600 mt-1">{{ __('app.about_stat_years') }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-8">
            @php
                $cards = [
                    ['fa-bullseye',  __('app.about_mission_title'), __('app.about_mission_desc'), 'violet'],
                    ['fa-eye',       __('app.about_vision_title'),  __('app.about_vision_desc'),  'indigo'],
                    ['fa-handshake', __('app.about_values_title'),  __('app.about_values_desc'),  'emerald'],
                ];
            @endphp
            @foreach($cards as $card)
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-lg transition">
                <div class="w-14 h-14 bg-{{$card[3]}}-100 text-{{$card[3]}}-600 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas {{$card[0]}} text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $card[1] }}</h3>
                <p class="text-slate-600">{{ $card[2] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-slate-800 text-center mb-12">{{ __('app.about_team_title') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($team as $member)
                <div class="text-center">
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-violet-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-4xl font-bold mb-3 shadow-lg">
                        {{ mb_substr($member['name'], 0, 1) }}
                    </div>
                    <h3 class="font-bold text-slate-800">{{ $member['name'] }}</h3>
                    <p class="text-sm text-slate-500">{{ $member['role'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
