@extends('layouts.master')

@section('title', 'Edit Notification Rule')

@section('button')
    <a href="{{ route('admin.telegram-rules.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramRule.common.breadcrumb', ['title' => __('index.edit')])
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.telegram-rules.update', $rule->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.telegramRule.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection
