@extends('layouts.master')

@if(request('telegram_group_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', 'Create Telegram Group')

@section('button')
    <a href="{{ route('admin.telegram-groups.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @unless(request('telegram_group_modal') === '1')
            @include('admin.telegramGroup.common.breadcrumb', ['title' => __('index.create')])
        @endunless
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.telegram-groups.store') }}" method="POST">
                    @csrf
                    @if(request('telegram_group_modal') === '1')
                        <input type="hidden" name="telegram_group_modal" value="1">
                    @endif
                    @include('admin.telegramGroup.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection
