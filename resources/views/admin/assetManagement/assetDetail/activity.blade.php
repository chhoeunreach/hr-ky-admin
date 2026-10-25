@extends('layouts.master')
@section('title', __('index.asset_activity_history'))
@section('main-content')
<section class="content">
    <a class="btn btn-outline-secondary mb-3" href="{{ route('admin.assets.index') }}">{{ __('index.assets') }}</a>
    @include('admin.assetManagement.assetDetail.common.activity')
</section>
@endsection
