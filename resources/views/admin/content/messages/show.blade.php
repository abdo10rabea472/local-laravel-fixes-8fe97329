@extends('admin.layouts.app')
@section('title', __('app.admin_messages_show_page_title'))

@php
    $statusMap = ['new'=>__('app.admin_messages_status_new'),'read'=>__('app.admin_messages_status_read'),'replied'=>__('app.admin_messages_status_replied'),'archived'=>__('app.admin_messages_status_archived')];
@endphp

@section('content')
<x-admin.page :title="__('app.admin_messages_show_title')" :subtitle="__('app.admin_messages_show_subtitle')" :back="route('admin.messages.index')">
    <x-admin.card :title="__('app.admin_messages_card_content')" icon="fa-envelope-open-text">
        <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $message->subject }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('app.admin_messages_from') }} <strong class="text-gray-700 dark:text-gray-200">{{ \$message->name }}</strong>
                    &lt;<span class="font-mono">{{ $message->email }}</span>&gt;
                </p>
                @if($message->phone)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.admin_messages_phone') }} <span class="font-mono">{{ \$message->phone }}</span></p>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-dark-800 text-xs font-bold">{{ $statusMap[$message->status] ?? $message->status }}</span>
        </div>
        <div class="whitespace-pre-wrap p-5 bg-gray-50 dark:bg-dark-800 rounded-xl text-gray-700 dark:text-gray-200 leading-relaxed">{{ $message->message }}</div>
    </x-admin.card>

    <x-slot:side>
        <x-admin.card :title="__('app.admin_messages_card_actions')" icon="fa-bolt">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}"
               class="w-full h-12 inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/20 mb-3">
                <i class="fa-solid fa-reply"></i> {{ __('app.admin_messages_btn_reply') }}
            </a>
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('{{ __(\'app.admin_messages_confirm_delete\') }}')">
                @csrf @method('DELETE')
                <button class="w-full h-11 bg-rose-50 dark:bg-rose-950/30 text-rose-600 hover:bg-rose-100 rounded-xl text-sm font-bold">
                    <i class="fa-solid fa-trash"></i> {{ __('app.admin_messages_btn_delete') }}
                </button>
            </form>
        </x-admin.card>

        <x-admin.card :title="__('app.admin_messages_card_change_status')" icon="fa-tags">
            <div class="grid grid-cols-2 gap-2">
                @foreach($statusMap as $k=>$v)
                    <form method="POST" action="{{ route('admin.messages.status', $message) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $k }}">
                        <button class="w-full h-10 bg-gray-50 dark:bg-dark-800 hover:bg-gray-100 dark:hover:bg-dark-700 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold {{ $message->status===$k ? 'ring-2 ring-primary-500' : '' }}">{{ $v }}</button>
                    </form>
                @endforeach
            </div>
        </x-admin.card>
    </x-slot:side>
</x-admin.page>
@endsection
