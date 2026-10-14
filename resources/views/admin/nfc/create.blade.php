@extends('layouts.master')

@section('title',__('index.nfc'))

@section('button')
    <a href="{{route('admin.nfc.index')}}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">@lang('index.dashboard')</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.nfc.index')}}">@lang('index.nfc_section')</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.create') }} @lang('index.nfc')</li>
            </ol>
            @yield('button')
        </nav>

        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{route('admin.nfc.store')}}" method="POST">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="title" class="form-label">@lang('index.title')</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" autocomplete="off" placeholder="{{ __('index.title') }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="identifier" class="form-label">@lang('index.nfc') <span style="color: red">*</span></label>
                            <input type="text" class="form-control @error('identifier') is-invalid @enderror" id="identifier" name="identifier" value="{{ old('identifier') }}" required autocomplete="off" placeholder="@lang('index.nfc')">
                            @error('identifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4 mt-lg-4 text-start">
                            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ __('index.create') }} @lang('index.nfc')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
