@extends('layouts.master')

@section('title',__('index.resignation'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.resignation.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="resignationForm" lang="{{ app()->getLocale() }}" class="forms-sample" action="{{route('admin.resignation.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @if(request()->boolean('return_to_profile'))
                        <input type="hidden" name="return_to_profile" value="1">
                    @endif
                    @include('admin.resignation.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.resignation.common.scripts')
@endsection
