@extends('layouts.master')

@section('title', __('index.project'))

@section('action', __('index.lists'))

@section('button')
    @can('create_project')
        <a href="{{ route('admin.projects.create') }}">
            <button class="btn btn-primary mt-0 mb-4 text-md-start text-center">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.create_project') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.project.common.breadcrumb')

        <div class="search-box p-4 pb-0 bg-white rounded mb-3 box-shadow">
            <form class="forms-sample" action="{{ route('admin.projects.index') }}" method="get">
                <h5 class="mb-3">{{ __('index.project_filter') }}</h5>
                <div class="row align-items-center">

                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select" id="project_name" name="project_name">
                            <option value="" {{ !isset($filterParameters['project_name']) ? 'selected' : '' }}>{{ __('index.search_by_project') }}</option>
                            @foreach($allProjects as $key => $value)
                                <option value="{{ $value->id }}" {{ $value->id == $filterParameters['project_name'] ? 'selected' : '' }}>{{ ucfirst($value->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ __('index.search_by_status') }}</option>
                            @foreach(\App\Models\Project::STATUS as $value)
                                <option value="{{ $value }}" {{ $filterParameters['status'] == $value ? 'selected' : '' }}>
                                    {{ \App\Helpers\PMHelper::STATUS[$value] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select" id="priority" name="priority">
                            <option value="">{{ __('index.search_by_priority') }}</option>
                            @foreach(\App\Models\Project::PRIORITY as $value)
                                <option value="{{ $value }}" {{ $filterParameters['priority'] == $value ? 'selected' : '' }}> {{ ucfirst($value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg col-md-6 mb-4">
                        <select class="col-md-12 from-select" id="filter" name="members[]" multiple="multiple">
                            @foreach($employees as $key => $value)
                                <option value="{{ $value->id }}" {{ isset($filterParameters['members']) && in_array($value->id, $filterParameters['members']) ? 'selected' : '' }}>{{ ucfirst($value->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg mb-4">
                        <div class="d-flex float-lg-end">
                            <button type="submit" class="btn btn-block btn-secondary me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-danger" href="{{ route('admin.projects.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

            <?php
            $ProjectStatus = [
                'in_progress' => 'warning',
                'not_started' => 'dark',
                'on_hold' => 'info',
                'cancelled' => 'danger',
                'completed' => 'success',
            ];

            $isActiveStatus = [
                0 => __('index.inactive'),
                1 => __('index.active')
            ];
            ?>

        <div class="project-card">
            <div class="row">
                @forelse($projects as $key => $value)
                    <div class="col-xxl-3 col-xl-4 d-flex mb-4">
                        <div class="card p-4 w-100">
                            <div class="title-section d-flex align-items-center justify-content-between mb-2">
                                <div class="title-section-inner d-flex align-items-center justify-content-between">

                                    <div class="title-section-image me-2">
                                        <img class="rounded-circle" style="object-fit: cover" src="{{ asset(\App\Models\Project::UPLOAD_PATH . $value->cover_pic) }}"
                                             alt="profile">
                                    </div>
                                    <div class="title-section-heading">
                                        <h5 class="mb-1">
                                            <a href="{{ route('admin.projects.show', $value?->id) }}">{{ ucfirst($value?->name) }}</a>
                                        </h5>
                                        <p class="small">
                                            <b>{{ __('index.client') }} :</b> {{ ucfirst($value->client?->name) }}
                                        </p>
                                    </div>

                                </div>

                                @canany(['show_project_detail', 'edit_project', 'delete_project'])
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle p-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="link-icon" data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" style="">

                                            @can('edit_project')
                                                <a href="{{ route('admin.projects.edit', $value->id) }}" class="d-block py-1">
                                                    <i class="link-icon me-2" data-feather="edit"></i> {{ __('index.edit') }}
                                                </a>
                                            @endcan

                                            @can('show_project_detail')
                                                <a href="{{ route('admin.projects.show', $value->id) }}" class="d-block py-1">
                                                    <i class="link-icon me-2" data-feather="eye"></i> {{ __('index.view') }}
                                                </a>
                                            @endcan

                                            @can('delete_project')
                                                <a data-href="{{ route('admin.projects.delete', $value->id) }}" class="delete d-block py-1">
                                                    <i class="link-icon me-2" data-feather="delete"></i> {{ __('index.delete') }}
                                                </a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcanany
                            </div>
                            <ul class="d-flex justify-content-between list-unstyled mb-2">
                                <li>
                                    <small class="text-muted">{{ __('index.all_tasks') }} :{{ ($value->tasks->count()) }}</small>
                                </li>
                                <li>
                                    <small class="text-muted">{{ __('index.completed_tasks') }} :{{ ($value->completedTask->count()) }}</small>
                                </li>
                            </ul>
                            <div class="badge-section mb-2">
                                <span class="badge badge-soft-success text-end d-inline-block float-end">
                                    {{ $value->projectRemainingDaysToComplete() > 0 ? $value->projectRemainingDaysToComplete() : 0 }} {{ __('index.days_left') }}
                                </span>
                            </div>

                            <div class="progress mb-2">
                                <div class="progress-bar color2 rounded"
                                     role="progressbar"
                                     style="{{ \App\Helpers\AppHelper::getProgressBarStyle($value->getProjectProgressInPercentage()) }}"
                                     aria-valuenow="25"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <span>{{ ($value->getProjectProgressInPercentage()) }} %</span>
                                </div>
                            </div>

                            <div class="status-section">
                                <ul class="d-flex list-unstyled mb-3">
                                    <li>
                                    <span class="btn btn-{{ $ProjectStatus[$value->status] }} btn-xs cursor-default">
                                        {{ \App\Helpers\PMHelper::STATUS[$value->status] }}
                                    </span>
                                    </li>

                                    <li class="ms-2"><span class="btn btn-secondary btn-xs cursor-default">
                                        {{ ucfirst($value->priority) }}</span>
                                    </li>

                                    <li class="ms-2">
                                    <span class="btn btn-{{ $value->is_active ? 'success' : 'danger' }} btn-xs toggleStatus"
                                          data-href="{{ route('admin.projects.toggle-status', $value->id) }}">
                                        {{ ($isActiveStatus[$value->is_active]) }}
                                    </span>
                                    </li>
                                </ul>
                            </div>

                            <div class="member-section d-flex justify-content-between">
                                <div class="member-listed w-50">
                                    <h6 class="mb-1 ms-n3">{{ __('index.team_member') }}</h6>
                                    @forelse($value->assignedMembers as $key => $memberDetail)

                                        <button type="button" class="p-0 border-0 bg-transparent ms-n3" disabled data-toggle="tooltip" data-placement="top" title="{{ ucfirst($memberDetail->user->name) }}">
                                            <img class="rounded-circle" style="object-fit: cover"
                                                 src="{{ isset($memberDetail->user->avatar) ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $memberDetail->user->avatar) :
                                                                asset('assets/images/img.png') }}"
                                                 alt="profile">
                                        </button>
                                    @empty

                                    @endforelse
                                </div>

                                <div class="member-listed w-50 float-end text-end">
                                    <h6 class="mb-1">{{ __('index.leader') }}</h6>
                                    @forelse($value->projectLeaders as $key => $leader)
                                        <button type="button" class="p-0 border-0 bg-transparent ms-n3" disabled data-toggle="tooltip" data-placement="top" title="{{ ucfirst($leader->user->name) }}">
                                            <img class="rounded-circle" style="object-fit: cover"
                                                 src="{{ $leader->user->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $leader->user->avatar) :
                                                                asset('assets/images/img.png') }}"
                                                 alt="profile">
                                        </button>
                                    @empty

                                    @endforelse
                                </div>
                            </div>

                            <div class="date-section d-flex justify-content-between border-top mt-2 pt-2">
                                <div class="date-item">
                                    <p class="text-success">{{ \App\Helpers\AppHelper::formatDateForView($value->start_date) }}</p>
                                    <small class="text-muted">{{ __('index.start_date') }}</small>
                                </div>
                                <div class="date-item text-end">
                                    <p class="text-danger">{{ \App\Helpers\AppHelper::formatDateForView($value->deadline) }}</p>
                                    <small class="text-muted">{{ __('index.due_date') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            {{ __('index.no_records_found') }}
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $projects->appends($_GET)->links() }}
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.project.common.scripts')
@endsection
