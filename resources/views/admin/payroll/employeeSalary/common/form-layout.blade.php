@extends('layouts.master')

@section('styles')
    @if(request('employee_modal') === '1')
        <style>
            body { background: #fff; }
            .content > .card { border: 0; box-shadow: none; margin-bottom: 0; }
            .content > .card > .card-header { display: none; }
            .content > .card > .card-body,
            .content > .card > form > .card-body { padding: 0; }
            .content > .card > form > .card-footer { padding: 1rem 0 0; background: transparent; border: 0; }
        </style>
    @endif
@endsection
