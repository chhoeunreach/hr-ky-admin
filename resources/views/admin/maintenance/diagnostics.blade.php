@extends('layouts.master')

@section('title', __('diagnostics.title'))

@section('main-content')
    <h1 class="h3 mb-3">{{ __('diagnostics.title') }}</h1>

    @php
        $badgeColors = ['passed' => 'success', 'warning' => 'warning text-dark', 'unavailable' => 'secondary'];
    @endphp

    <section class="card mb-4" aria-labelledby="systemChecksHeading">
        <div class="card-header">
            <h2 class="h5 mb-0" id="systemChecksHeading">{{ __('diagnostics.checks_title') }}</h2>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($checks as $id => $check)
                <li class="list-group-item p-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h3 class="h6 mb-0">{{ __('diagnostics.checks.'.$id.'.title') }}</h3>
                        <span class="badge bg-{{ $badgeColors[$check['status']] ?? 'danger' }}">{{ __('diagnostics.status.'.$check['status']) }}</span>
                    </div>
                    @if($check['status'] === 'passed')
                        <p class="text-muted mb-0">{{ __('diagnostics.checks.'.$id.'.passed') }}</p>
                    @else
                        @if(isset($check['details']['count']))
                            <p class="mb-1">{{ trans_choice('diagnostics.checks.'.$id.'.finding', $check['details']['count']) }}</p>
                        @endif
                        <p class="mb-0">{{ __('diagnostics.checks.'.$id.'.fix') }}</p>
                        @if($id === 'salary_directory' && isset($check['details']['count']))
                            <a class="btn btn-sm btn-outline-primary mt-2" href="{{ route('admin.employee-salaries.index') }}">{{ __('diagnostics.checks.salary_directory.open') }}</a>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <section class="card" aria-labelledby="recentLogsHeading">
        <div class="card-header">
            <h2 class="h5 mb-2" id="recentLogsHeading">{{ __('diagnostics.recent_logs') }}</h2>
            <p class="text-muted mb-3">{{ __('diagnostics.log_scope', ['limit' => \App\Services\Maintenance\SystemDiagnostics::LOG_LIMIT]) }}</p>
            <form method="get" class="d-flex flex-wrap align-items-center gap-2">
                <label for="diagnosticsLevel">{{ __('diagnostics.level') }}</label>
                <select class="form-select form-select-sm w-auto" name="level" id="diagnosticsLevel">
                    <option value="">{{ __('diagnostics.all_levels') }}</option>
                    <option value="error" @selected($level === 'error')>{{ __('diagnostics.status.error') }}</option>
                    <option value="warning" @selected($level === 'warning')>{{ __('diagnostics.status.warning') }}</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('diagnostics.apply') }}</button>
            </form>
        </div>
        @if($logs['unreadable'])
            <p class="alert alert-warning m-3" role="alert">{{ __('diagnostics.unreadable') }}</p>
        @endif
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>{{ __('diagnostics.time') }}</th>
                    <th>{{ __('diagnostics.level') }}</th>
                    <th>{{ __('diagnostics.issue') }}</th>
                    <th>{{ __('diagnostics.next_action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs['entries'] as $entry)
                    <tr>
                        <td class="text-nowrap">{{ $entry['timestamp'] }}</td>
                        <td><span class="badge bg-{{ $badgeColors[$entry['level']] ?? 'danger' }}">{{ __('diagnostics.status.'.$entry['level']) }}</span></td>
                        <td>{{ __('diagnostics.categories.'.$entry['category'].'.title') }}</td>
                        <td>{{ __('diagnostics.categories.'.$entry['category'].'.fix') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">{{ __('diagnostics.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
