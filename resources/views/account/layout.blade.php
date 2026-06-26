@extends('layouts.front')
@section('content')
<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-6xl mx-auto px-4">
        @include('account.partials.sidebar')
        <div class="lg:mr-72">
            @yield('account_content')
        </div>
    </div>
</div>
@endsection
