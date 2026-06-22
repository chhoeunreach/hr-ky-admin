@extends('layouts.master')

@section('title', 'Edit Telegram Group')

@section('button')
    <a href="{{ route('admin.telegram-groups.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramGroup.common.breadcrumb', ['title' => __('index.edit')])
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.telegram-groups.update', $telegramGroup->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    @include('admin.telegramGroup.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection
