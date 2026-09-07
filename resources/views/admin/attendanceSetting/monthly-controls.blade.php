@extends('layouts.master')

@section('title', __('index.monthly_attendance_controls'))
@section('action', __('index.monthly_attendance_controls'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">@lang('index.dashboard')</a></li>
                <li class="breadcrumb-item">
                    @can('general_setting')
                        <a href="{{ route('admin.general-settings.index') }}">@lang('index.general_settings')</a>
                    @else
                        @lang('index.general_settings')
                    @endcan
                </li>
                <li class="breadcrumb-item active" aria-current="page">@lang('index.monthly_attendance_controls')</li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('index.monthly_attendance_controls') }}</h5>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.attendance-monthly.index') }}">
                    <i class="link-icon" data-feather="calendar"></i> {{ __('index.attendance_monthly') }}
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.attendance-monthly.controls.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="bonus_amount">{{ __('index.monthly_attendance_bonus_amount') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input
                                    type="number"
                                    class="form-control @error('bonus_amount') is-invalid @enderror"
                                    id="bonus_amount"
                                    name="bonus_amount"
                                    min="0"
                                    step="1"
                                    value="{{ old('bonus_amount', $settings['monthly_attendance_bonus_amount']->value ?? 20) }}"
                                >
                                @error('bonus_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>{{ __('index.target_rule') }}</th>
                                <th class="text-center" style="width: 140px;">{{ __('index.status') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $rules = [
                                    'require_check_in' => ['slug' => 'monthly_attendance_require_check_in', 'label' => __('index.require_check_in')],
                                    'require_check_out' => ['slug' => 'monthly_attendance_require_check_out', 'label' => __('index.require_check_out')],
                                    'require_no_late_check_in' => ['slug' => 'monthly_attendance_require_no_late_check_in', 'label' => __('index.control_late_check_in')],
                                    'require_no_early_check_out' => ['slug' => 'monthly_attendance_require_no_early_check_out', 'label' => __('index.control_check_out_before_time_out')],
                                    'require_no_early_check_in' => ['slug' => 'monthly_attendance_require_no_early_check_in', 'label' => __('index.control_check_in_before_time_start')],
                                ];
                            @endphp

                            @foreach($rules as $input => $rule)
                                <tr>
                                    <td>{{ $rule['label'] }}</td>
                                    <td class="text-center">
                                        <input type="hidden" name="{{ $input }}" value="0">
                                        <label class="switch mb-0">
                                            <input
                                                type="checkbox"
                                                name="{{ $input }}"
                                                value="1"
                                                {{ old($input, $settings[$rule['slug']]->status ?? 1) ? 'checked' : '' }}
                                            >
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="link-icon" data-feather="save"></i> @lang('index.update')
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
