@extends('layouts.master')

@if(request('branch_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', __('index.edit_branch'))

@section('button')
    <a href="{{ route('admin.branch.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @unless(request('branch_modal') === '1')
            @include('admin.branch.common.breadcrumb',['title'=>__('index.edit')])
        @endunless
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.branch.update', $branch->id) }}" enctype="multipart/form-data" method="post">
                    @method('PUT')
                    @csrf
                    @if(request('branch_modal') === '1')
                        <input type="hidden" name="branch_modal" value="1">
                    @endif
                    @include('admin.branch.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
