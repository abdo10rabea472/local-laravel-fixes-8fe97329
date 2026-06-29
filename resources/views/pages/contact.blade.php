@extends('layouts.front')

@section('content')
<section class="bg-gradient-to-br from-violet-600 to-indigo-800 text-white py-16">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-3">{{ __('app.contact_hero_title') }}</h1>
        <p class="text-violet-100">{{ __('app.contact_hero_subtitle') }}</p>
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 space-y-4">
            @php
                $infoCards = [
                    ['fa-phone',         __('app.contact_info_phone'),   site_setting('contact_phone',   '+966 50 000 0000'),                       'violet'],
                    ['fa-envelope',      __('app.contact_info_email'),   site_setting('contact_email',   'support@unilab.com'),                     'indigo'],
                    ['fa-location-dot',  __('app.contact_info_address'), site_setting('contact_address', __('app.contact_default_address')),       'emerald'],
                    ['fa-clock',         __('app.contact_info_hours'),   site_setting('contact_hours',   __('app.contact_default_hours')),         'amber'],
                ];
            @endphp
            @foreach($infoCards as $info)
            <div class="bg-white p-5 rounded-xl shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 bg-{{$info[3]}}-100 text-{{$info[3]}}-600 rounded-lg flex items-center justify-center shrink-0">
                    <i class="fas {{$info[0]}}"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500">{{ $info[1] }}</div>
                    <div class="font-semibold text-slate-800">{{ $info[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-sm">
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.contact_form_name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.contact_form_email') }} *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.contact_form_phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.contact_form_subject') }} *</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.contact_form_message') }} *</label>
                    <textarea name="message" rows="6" required
                              class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">{{ old('message') }}</textarea>
                    @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full bg-violet-600 hover:bg-violet-700 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-paper-plane"></i> {{ __('app.contact_form_submit') }}
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
