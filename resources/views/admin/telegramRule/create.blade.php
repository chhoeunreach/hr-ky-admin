@extends('layouts.master')

@if(request('telegram_rule_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', __('index.add_telegram_rule'))

@section('button')
    <a href="{{ route('admin.telegram-rules.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @unless(request('telegram_rule_modal') === '1')
            @include('admin.telegramRule.common.breadcrumb', ['title' => __('index.create')])
        @endunless
        <div class="card @if(request('telegram_rule_modal') === '1') border-0 shadow-none bg-transparent @endif">
            <div class="card-body @if(request('telegram_rule_modal') === '1') p-0 @else pb-0 @endif">
                <form class="forms-sample" action="{{ route('admin.telegram-rules.store') }}" method="POST">
                    @csrf
                    @if(request('telegram_rule_modal') === '1')
                        <input type="hidden" name="telegram_rule_modal" value="1">
                    @endif
                    @include('admin.telegramRule.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection
