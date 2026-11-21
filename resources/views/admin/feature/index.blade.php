@extends('layouts.master')
@section('title', __('index.feature_control'))
@section('main-content')
<section class="content">
    @include('admin.section.flash_message')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.feature_control') }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="card-title mb-1">{{ __('index.feature_control') }}</h6>
                <p class="text-muted mb-0 small">{{ __('app_settings.feature_control_description') }}</p>
            </div>
            @can('role_permission')
                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.roles.index') }}">{{ __('index.roles_permissions') }}</a>
            @endcan
        </div>
        <div class="card-body">
            @unless($controlsReady)
                <div class="alert alert-warning">{{ __('app_settings.feature_control_setup_required') }}</div>
            @endunless
            <div class="table-responsive">
                <table id="dataTableExample" class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('index.name') }}</th>
                            <th>{{ __('index.group') }}</th>
                            <th>{{ __('index.status') }}</th>
                            <th>{{ __('index.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catalog as $key => $control)
                            @php($enabled = $statuses->get($key, true))
                            <tr>
                                <td>
                                    <strong>{{ $control['name'] }}</strong>
                                    @if($control['name'] !== $key)<small class="d-block text-muted">{{ $key }}</small>@endif
                                    @if($control['actions'])
                                        <details class="small mt-1">
                                            <summary>{{ __('app_settings.affected_functions') }}</summary>
                                            @foreach($control['actions'] as $action)
                                                <code class="d-block">{{ $action }}</code>
                                            @endforeach
                                        </details>
                                    @endif
                                </td>
                                <td>{{ implode(', ', $control['menus']) }}</td>
                                <td><span class="badge {{ $enabled ? 'bg-success' : 'bg-secondary' }}">{{ $enabled ? __('index.enabled') : __('index.disabled') }}</span></td>
                                <td>
                                    <form action="{{ route('admin.feature.status.update') }}" method="post">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="key" value="{{ $key }}">
                                        <input type="hidden" name="enabled" value="{{ $enabled ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm {{ $enabled ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                aria-label="{{ ($enabled ? __('app_settings.disable_feature') : __('app_settings.enable_feature')).' '.$control['name'] }}"
                                                @disabled(!$controlsReady)>
                                            {{ $enabled ? __('app_settings.disable_feature') : __('app_settings.enable_feature') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
