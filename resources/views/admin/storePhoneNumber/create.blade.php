@extends('layouts.master')

@section('title', __('index.add_new_phone_number'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.storePhoneNumber.common.breadcrumb')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ __('index.register_store_phone_number') }}</h5>
                <a href="{{ route('admin.store-phone-numbers.index') }}" class="btn btn-secondary btn-sm">
                    <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.store-phone-numbers.store') }}" method="POST">
                    @csrf
                    @include('admin.storePhoneNumber.common.form')

                    <div class="mt-4 border-top pt-3 text-end">
                        <a href="{{ route('admin.store-phone-numbers.index') }}" class="btn btn-secondary me-2">{{ __('index.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="link-icon" data-feather="check"></i> {{ __('index.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
