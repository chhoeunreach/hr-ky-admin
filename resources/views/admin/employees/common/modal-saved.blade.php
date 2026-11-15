@extends('layouts.master')
@section('title', __('index.edit_user_detail'))
@section('main-content')
    <div data-employee-modal-saved>{{ $message ?? __('message.update_user') }}</div>
@endsection
