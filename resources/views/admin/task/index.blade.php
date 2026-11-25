@php use App\Helpers\PMHelper; @endphp
@php use App\Models\Task; @endphp
@php use Illuminate\Support\Str; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')
@section('title',__('index.tasks'))
@section('action',__('index.lists'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.task.common.breadcrumb')

        @php
            $hasTaskFilters = collect($filterParameters ?? [])
                ->except(['search', 'per_page', 'page'])
                ->filter(function ($value) {
                    if (is_array($value)) {
                        return count(array_filter($value)) > 0;
                    }
                    return $value !== null && $value !== '';
                })
                ->isNotEmpty();
        @endphp
        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'taskFilterCollapse', 'directoryExpanded' => $hasTaskFilters])
            <div id="taskFilterCollapse" class="collapse{{ $hasTaskFilters ? ' show' : '' }}">
                <form class="forms-sample directory-filter-form" action="{{ route('admin.tasks.index') }}" id="taskFilterForm" method="get">
                    <div class="row g-3 align-items-end">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-xl-3 col-md-6">
                                <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value="" {{ ($filterParameters['branch_id'] ?? '') == '' ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}</option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ (string)($filterParameters['branch_id'] ?? '') === (string)$branch->id ? 'selected' : '' }}>
                                                {{ ucfirst($branch->name) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif

                        <div class="col-xl-3 col-md-6">
                            <label for="project" class="form-label">{{ __('index.project') }}</label>
                            <select class="form-select" id="project" name="project_id">
                                <option value="" {{ !isset($filterParameters['project_id']) ? 'selected':'' }}></option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="taskName" class="form-label">{{ __('index.task_name') }}</label>
                            <select class="form-select" id="taskName" name="task_id">
                                <option value="" {{ !isset($filterParameters['task_id']) ? 'selected':'' }}>@lang('index.search_by_task_name')</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="status" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">@lang('index.search_by_status')</option>
                                @foreach(Task::STATUS as $value)
                                    <option value="{{ $value }}" {{ ($filterParameters['status'] ?? null) == $value ? 'selected':'' }}>
                                        {{ (PMHelper::STATUS[$value]) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="priority" class="form-label">{{ __('index.priority') }}</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">@lang('index.search_by_priority')</option>
                                @foreach(Task::PRIORITY as $value)
                                    <option value="{{ $value }}" {{ ($filterParameters['priority'] ?? null) == $value ? 'selected':'' }}>
                                        {{ ucfirst($value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="taskMember" class="form-label">{{ __('index.task_members') }}</label>
                            <select class="form-select" id="taskMember" name="assigned_member[]" multiple>
                            </select>
                        </div>
                    </div>

                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.tasks.index')])
                </form>
            </div>
        </div>

        <div id="taskListSection">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
                @include('admin.section.directory-toolbar', ['directoryTable' => 'taskTable', 'directoryForm' => 'taskFilterForm', 'directorySearch' => 'taskListSearch', 'directoryRecords' => $tasks, 'directoryTitle' => __('index.tasks'), 'directoryIcon' => 'clipboard', 'directoryPermission' => 'create_task', 'directoryAddUrl' => route('admin.tasks.create'), 'directoryDefaultSize' => (string) ($filterParameters['per_page'] ?? '15'), 'directoryCurrentSize' => (string) ($filterParameters['per_page'] ?? '15'), 'directoryPageSizes' => [15, 25, 50, 100, 200, 'all'], 'directorySearchKey' => 'search', 'directorySearchInForm' => true, 'directoryLive' => false])
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="taskTable" class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>{{ __('index.task_name') }}</th>
                                    <th>{{ __('index.project') }}</th>
                                    <th>{{ __('index.status') }}</th>
                                    <th style="width: 150px;">{{ __('index.progress') }}</th>
                                    <th>{{ __('index.period') }}</th>
                                    <th class="text-center" style="width: 110px;">{{ __('index.is_active') }}</th>
                                    @canany(['edit_task','show_task_detail','delete_task'])
                                        <th class="text-center" style="width: 100px;" data-directory-actions>{{ __('index.action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $statusBadges = [
                                        'in_progress' => 'bg-primary bg-opacity-10 text-primary',
                                        'not_started' => 'bg-secondary bg-opacity-10 text-secondary',
                                        'cancelled' => 'bg-danger bg-opacity-10 text-danger',
                                        'completed' => 'bg-success bg-opacity-10 text-success',
                                    ];
                                @endphp
                                @forelse($tasks as $key => $value)
                                    @php
                                        $progressBar = ($value->taskChecklists->count() > 0) ? $value->getTaskProgressInPercentage() : (($value->status === 'completed') ? 100 : 0);
                                        $progressText = ((($value->taskChecklists->count() > 0) && ($value->status === 'completed')) ? $value->getTaskProgressInPercentage() : (($value->status === 'completed') ? 100 : 0));
                                        $remainingDays = $value->taskRemainingDaysToComplete();
                                    @endphp
                                    <tr>
                                        <td class="text-muted small fw-medium">{{ ($tasks->firstItem() ?? 0) + $key }}</td>
                                        <td>
                                            <a href="{{ route('admin.tasks.show', $value->id) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                                {{ $value->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.projects.show', $value->project_id) }}" class="text-muted small">
                                                {{ ucfirst($value?->project?->name) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadges[$value->status] ?? 'bg-secondary bg-opacity-10 text-secondary' }} px-2 py-1" style="font-size: 11px; font-weight: 600;">
                                                {{ PMHelper::STATUS[$value->status] ?? ucfirst($value->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress mb-1" style="height: 8px;">
                                                <div class="progress-bar color2 rounded"
                                                     role="progressbar"
                                                     style="{{ AppHelper::getProgressBarStyle($progressBar) }}"
                                                     aria-valuenow="{{ $progressBar }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small text-muted">{{ $progressText }} %</span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <span class="text-success"><i class="link-icon" data-feather="calendar" style="width: 13px;"></i>
                                                    {{ AppHelper::formatDateForView($value->start_date) }} -
                                                    <span class="text-danger">{{ AppHelper::formatDateForView($value->end_date) }}</span>
                                                </span>
                                            </div>
                                            <span class="badge badge-soft-success" style="font-size: 10px;">
                                                {{ $remainingDays > 0 ? $remainingDays : 0 }} @lang('index.days')
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <label class="switch mb-0">
                                                <input class="toggleStatus"
                                                       href="{{ route('admin.tasks.toggle-status', $value->id) }}"
                                                       type="checkbox" {{ ($value->is_active) == 1 ? 'checked' : '' }} {{ $remainingDays <= 0 ? 'disabled' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        @canany(['edit_task','show_task_detail','delete_task'])
                                            <td class="text-center">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle p-0" data-bs-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                        <i class="link-icon" data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @can('edit_task')
                                                            <a href="{{ route('admin.tasks.edit', $value->id) }}" class="d-block py-1">
                                                                <i class="link-icon me-2" data-feather="edit"></i> @lang('index.edit')
                                                            </a>
                                                        @endcan

                                                        @can('show_task_detail')
                                                            <a href="{{ route('admin.tasks.show', $value->id) }}" class="d-block py-1">
                                                                <i class="link-icon me-2" data-feather="eye"></i> @lang('index.view')
                                                            </a>
                                                        @endcan

                                                        @can('delete_task')
                                                            <a data-href="{{ route('admin.tasks.delete', $value->id) }}"
                                                               class="delete d-block py-1">
                                                                <i class="link-icon me-2" data-feather="delete"></i> @lang('index.delete')
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <div class="text-center py-5">
                                                <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                    <i class="link-icon" data-feather="clipboard" style="width: 32px; height: 32px;"></i>
                                                </div>
                                                <h6 class="fw-semibold text-secondary mb-1">{{ __('index.no_records_found') }}</h6>
                                                @can('create_task')
                                                    <a href="{{ route('admin.tasks.create') }}" class="btn btn-sm btn-primary rounded-3 px-3 mt-2">
                                                        <i class="link-icon" data-feather="plus" style="width: 14px; height: 14px;"></i>
                                                        <span>@lang('index.create_tasks')</span>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="dataTables_paginate">
                    {{ $tasks->appends($_GET)->links() }}
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.task.common.scripts')

@endsection
