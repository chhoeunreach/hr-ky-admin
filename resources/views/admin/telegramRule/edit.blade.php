@extends('layouts.master')

@if(request('telegram_rule_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', 'Edit Notification Rule')

@section('button')
    <a href="{{ route('admin.telegram-rules.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @unless(request('telegram_rule_modal') === '1')
            @include('admin.telegramRule.common.breadcrumb', ['title' => __('index.edit')])
        @endunless
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.telegram-rules.update', $rule->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if(request('telegram_rule_modal') === '1')
                        <input type="hidden" name="telegram_rule_modal" value="1">
                    @endif
                    @include('admin.telegramRule.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection
