@php
    use App\Models\User;
    use App\Support\TelegramBotSettings;
@endphp
@extends('layouts.master')

@section('title', __('index.employees_title'))

@section('action', __('index.employees_action'))

@section('button')
    @can('create_employee')
        <div class="float-md-end d-flex align-items-center gap-2 justify-content-center">

            <a href="{{ route('admin.employees.create')}}">
                <button class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_employee') }}
                </button>
            </a>
        </div>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.employees.common.breadcrumb')

        @php
            $telegramBotUsername = TelegramBotSettings::get(TelegramBotSettings::BOT_USERNAME, '');
            $hasEmployeeFilters = filled($filterParameters['branch_id'] ?? null)
                || filled($filterParameters['department_id'] ?? null)
                || filled($filterParameters['post_id'] ?? null)
                || filled($filterParameters['employee_name'] ?? null)
                || filled($filterParameters['search'] ?? null)
                || filled($filterParameters['email'] ?? null)
                || filled($filterParameters['phone'] ?? null)
                || (($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null)
                || (($filterParameters['per_page'] ?? '25') !== '25');
        @endphp

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#employeeFilterCollapse"
                            aria-expanded="{{ $hasEmployeeFilters ? 'true' : 'false' }}"
                            aria-controls="employeeFilterCollapse">
                        <i class="link-icon" data-feather="filter"></i>
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.employee_lists') }}</h6>
                </div>
            </div>
            <div id="employeeFilterCollapse" class="collapse{{ $hasEmployeeFilters ? ' show' : '' }}">
            <form class="forms-sample card-body pb-0" action="{{ route('admin.employees.index') }}" id="employeeFilterForm" method="get">
                <input type="hidden" id="search" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                            <select class="form-control" id="branch" name="branch_id">
                                <option value="" {{ empty($filterParameters['branch_id']) ? 'selected' : '' }}>{{ __('index.select_branch') }}</option>
                                @foreach($branches as $branch)
                                    <option
                                        {{ ($filterParameters['branch_id'] == $branch->id) ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="department" name="department_id">
                            <option value="" selected>{{ __('index.select_department') }}</option>
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="post" name="post_id">
                            <option value="" selected>{{ __('index.select_post') }}</option>
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.employee_name') }}" id="employeeName"
                               name="employee_name" value="{{ $filterParameters['employee_name'] }}"
                               class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.employee_email') }}" id="email" name="email"
                               value="{{ $filterParameters['email'] }}" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="number" placeholder="{{ __('index.employee_phone') }}" id="phone" name="phone"
                               value="{{ $filterParameters['phone'] }}" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="">All Status</option>
                            <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-xxl-4 col-xl-4 col-md-6">
                        <div class="d-md-flex align-items-center gap-2">
                            <button type="submit" value="filter" class="btn btn-block btn-success mb-4">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary mb-4" href="{{ route('admin.employees.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>

                </div>


            </form>
            </div>
        </div>

        <div id="employeeListSection">
        <div class="card">
            <div class="card-header">
                <div class="employee-toolbar">
                    <div class="employee-toolbar-left">
                        <h6 class="card-title mb-0">{{ __('index.employee_lists') }}</h6>
                        <div class="employee-entry-control">
                            <span>Show</span>
                            <select class="form-control employee-entry-select" id="per_page" name="per_page" form="employeeFilterForm">
                                <option value="25" {{ (string)($filterParameters['per_page'] ?? '') === '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (string)($filterParameters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (string)($filterParameters['per_page'] ?? '') === '100' ? 'selected' : '' }}>100</option>
                                <option value="200" {{ (string)($filterParameters['per_page'] ?? '') === '200' ? 'selected' : '' }}>200</option>
                                <option value="500" {{ (string)($filterParameters['per_page'] ?? '') === '500' ? 'selected' : '' }}>500</option>
                                <option value="1000" {{ (string)($filterParameters['per_page'] ?? '') === '1000' ? 'selected' : '' }}>1,000</option>
                                <option value="all" {{ (string)($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>All</option>
                            </select>
                            <span>entries</span>
                        </div>
                    </div>
                    <div class="employee-toolbar-actions">
                        @can('create_employee')
                            <button type="button"
                                    id="export_employee"
                                    data-href="{{ route('admin.employees.index') }}"
                                    value="export"
                                    class="btn btn-outline-secondary btn-sm">
                                Export
                            </button>
                        @endcan
                    </div>
                    <div class="employee-toolbar-search">
                        <input type="text"
                               id="employeeListSearch"
                               class="employee-list-search"
                               value="{{ $filterParameters['search'] ?? '' }}"
                               placeholder="Search ...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <style>
                    .employee-toolbar {
                        display: grid;
                        grid-template-columns: auto 1fr auto;
                        align-items: center;
                        gap: 16px;
                    }

                    .employee-toolbar-left {
                        display: flex;
                        align-items: center;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .employee-toolbar-actions {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 10px;
                    }

                    .employee-toolbar-search {
                        display: flex;
                        justify-content: flex-end;
                    }

                    .employee-entry-control {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        color: #111827;
                        font-weight: 500;
                    }

                    .employee-entry-select {
                        min-width: 120px;
                    }

                    .employee-list-search {
                        width: min(100%, 250px);
                        border: 1px solid #d7dfeb;
                        border-radius: 14px;
                        min-height: 44px;
                        padding: 0 14px;
                        color: #111827;
                        background: #f8fbff;
                        box-shadow: none;
                    }

                    .employee-list-search:focus {
                        outline: none;
                        border-color: #93c5fd;
                        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
                    }

                    .employee-table-wrap {
                        overflow-x: auto;
                        overflow-y: visible;
                    }

                    .employee-table {
                        --employee-table-font-size: 0.6875rem;
                        --employee-table-header-size: 0.59375rem;
                        width: 100%;
                        min-width: 1120px;
                        table-layout: fixed;
                        font-size: var(--employee-table-font-size);
                    }

                    .employee-table th,
                    .employee-table td {
                        padding: 7px 8px;
                        vertical-align: middle;
                    }

                    .employee-table th {
                        padding: 6px 6px;
                        font-size: var(--employee-table-header-size);
                        font-weight: 600;
                        line-height: 1.15;
                        white-space: normal;
                        word-break: normal;
                        overflow-wrap: break-word;
                        text-align: center;
                    }

                    .employee-table .col-icon,
                    .employee-table .col-code,
                    .employee-table .col-name,
                    .employee-table .col-english-name,
                    .employee-table .col-address,
                    .employee-table .col-email,
                    .employee-table .col-branch,
                    .employee-table .col-designation,
                    .employee-table .col-department,
                    .employee-table .col-role,
                    .employee-table .col-shift,
                    .employee-table .col-boolean,
                    .employee-table .col-workplace,
                    .employee-table .col-action {
                        white-space: nowrap;
                    }

                    .employee-table .col-icon {
                        width: 36px;
                    }

                    .employee-table .col-code {
                        width: 70px;
                    }

                    .employee-table .col-name {
                        width: 160px;
                    }

                    .employee-table .col-english-name {
                        width: 140px;
                    }

                    .employee-table .col-address {
                        width: 90px;
                    }

                    .employee-table .col-email {
                        width: 145px;
                    }

                    .employee-table .col-branch {
                        width: 85px;
                    }

                    .employee-table .col-designation,
                    .employee-table .col-department {
                        width: 90px;
                    }

                    .employee-table .col-role {
                        width: 80px;
                    }

                    .employee-table .col-shift {
                        width: 64px;
                    }

                    .employee-table .col-boolean {
                        width: 62px;
                    }

                    .employee-table .col-workplace {
                        width: 64px;
                    }

                    .employee-table .col-action {
                        width: 46px;
                    }

                    .employee-name-cell {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }

                    .employee-name-cell img {
                        width: 32px !important;
                        height: 32px !important;
                    }

                    .employee-name-main {
                        min-width: 0;
                        flex: 1;
                    }

                    .employee-name-main p,
                    .employee-name-main small {
                        display: block;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    .employee-table td.col-code,
                    .employee-table td.col-address,
                    .employee-table td.col-email,
                    .employee-table td.col-branch,
                    .employee-table td.col-designation,
                    .employee-table td.col-department,
                    .employee-table td.col-role,
                    .employee-table td.col-shift {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    .employee-table .btn-xs {
                        padding: 2px 6px;
                        font-size: 11px;
                    }

                    .employee-table .switch {
                        margin-bottom: 0;
                    }

                    .telegram-connect-card {
                        text-align: center;
                    }

                    .telegram-connect-qr {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 220px;
                        height: 220px;
                        max-width: 100%;
                        margin: 0 auto 18px;
                        padding: 14px;
                        border: 1px solid #d8e0e7;
                        border-radius: 12px;
                        background: #ffffff;
                    }

                    .telegram-connect-qr svg {
                        width: 100%;
                        height: 100%;
                    }

                    .telegram-connect-link {
                        font-size: 12px;
                    }

                    .telegram-connect-status {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 14px;
                        padding: 8px 14px;
                        border-radius: 999px;
                        color: #0f7a3b;
                        background: #dcfce7;
                        font-weight: 700;
                    }

                    @media (max-width: 767.98px) {
                        .employee-toolbar {
                            grid-template-columns: 1fr;
                            align-items: stretch;
                        }

                        .employee-toolbar-actions,
                        .employee-toolbar-search {
                            justify-content: flex-start;
                        }

                        .employee-entry-control {
                            width: 100%;
                            justify-content: space-between;
                        }

                        .employee-entry-select {
                            min-width: 0;
                            flex: 1;
                        }

                        .employee-list-search {
                            width: 100%;
                        }

                        .employee-table .col-name {
                            width: 180px;
                        }

                        .employee-table .col-address,
                        .employee-table .col-email {
                            width: 150px;
                        }

                        .employee-table {
                            --employee-table-font-size: 0.625rem;
                            --employee-table-header-size: 0.5625rem;
                        }
                    }
                </style>
                <div class="table-responsive employee-table-wrap">
                    <table id="employeeTable" class="table employee-table">
                        <thead>
                        <tr>
                            @can('show_detail_employee')
                                <th class="col-icon">#</th>
                            @endcan
                            <th class="text-center col-code">{{ __('index.employee_code') }}</th>
                            <th class="col-name">{{ __('index.khmer_name') }}</th>
                            <th class="col-english-name">{{ __('index.english_name') }}</th>
                            <th class="col-address">{{ __('index.address') }}</th>
                            <th class="text-center col-email">{{ __('index.email') }}</th>
                            <th class="text-center col-branch">{{ __('index.branch') }}</th>
                            <th class="text-center col-designation">{{ __('index.designation') }}</th>
                            <th class="text-center col-department">{{ __('index.department') }}</th>
                            <th class="text-center col-role">{{ __('index.role') }}</th>
                            <th class="text-center col-shift">{{ __('index.shift') }}</th>
                            <th class="text-center col-boolean">{{ __('index.holiday_check_in') }}</th>
                            <th class="text-center col-workplace">{{ __('index.workplace') }}</th>
                            <th class="text-center col-boolean">{{ __('index.is_active') }}</th>
                            @canany(['employee.profile.view','edit_employee','delete_employee','change_password','force_logout'])
                                <th class="text-center col-action">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $changeColor = [
                                0 => 'success',
                                1 => 'primary',
                            ]
                            ?>
                        @forelse($users as $key => $value)
                            <tr>
                                @can('show_detail_employee')
                                    <td class="col-icon">
                                        <a href="{{ route('admin.employees.show', $value->id) }}"
                                           id="showOfficeTimeDetail">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan
                                <td class="text-center col-code">{{ $value->username ?: 'N/A' }}</td>
                                <td class="col-name">
                                    @php
                                        $profileImage = $value->avatar
                                            ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $value->avatar)
                                            : asset('assets/images/img.png');
                                    @endphp
                                    <div class="employee-name-cell">
                                        @can('show_detail_employee')
                                            <a href="{{ route('admin.employees.show', $value->id) }}"
                                               class="d-flex align-items-center gap-2 text-decoration-none text-reset employee-name-main">
                                                <img
                                                    src="{{ $profileImage }}"
                                                    alt="{{ ucfirst($value->name) }}"
                                                    class="rounded-circle"
                                                    style="width: 42px; height: 42px; object-fit: cover;"
                                                >
                                                <div class="employee-name-main">
                                                    <p class="mb-0">{{ ucfirst($value->name) }}</p>
                                                    <small class="text-muted">({{ ucfirst($value->role ? $value->role->name : 'N/A') }})</small>
                                                </div>
                                            </a>
                                        @else
                                            <div class="d-flex align-items-center gap-2 employee-name-main">
                                                <img
                                                    src="{{ $profileImage }}"
                                                    alt="{{ ucfirst($value->name) }}"
                                                    class="rounded-circle"
                                                    style="width: 42px; height: 42px; object-fit: cover;"
                                                >
                                                <div class="employee-name-main">
                                                    <p class="mb-0">{{ ucfirst($value->name) }}</p>
                                                    <small class="text-muted">({{ ucfirst($value->role ? $value->role->name : 'N/A') }})</small>
                                                </div>
                                            </div>
                                        @endcan

                                        @can('edit_employee')
                                            <a href="{{ route('admin.employees.edit', $value->id) }}"
                                               class="btn btn-outline-primary btn-xs"
                                                title="{{ __('index.edit_detail') }}"
                                                target="_blank"
                                                rel="noopener noreferrer">
                                                <i class="link-icon" data-feather="edit"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                                <td class="col-english-name">{{ $value->english_name ? ucfirst($value->english_name) : 'N/A' }}</td>
                                @php
                                    $fullAddress = $value->address ? ucfirst($value->address) : 'N/A';
                                    $addressPreview = \Illuminate\Support\Str::limit($fullAddress, 20);
                                @endphp
                                <td class="col-address" title="{{ $fullAddress }}">{{ $addressPreview }}</td>
                                <td class="text-center col-email">{{ $value->email }}</td>
                                <td class="text-center col-branch">{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                <td class="text-center col-designation">{{ $value->post ? ucfirst($value->post->post_name) : 'N/A' }}</td>
                                <td class="text-center col-department">{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                <td class="text-center col-role">{{ $value->role ? ucfirst($value->role->name) : 'N/A' }}</td>
                                <td class="text-center col-shift">{{ $value->officeTime ? ucfirst($value->officeTime->shift) : 'N/A' }}</td>
                                <td class="text-center col-boolean">
                                    <label class="switch">
                                        <input class="toggleHolidayCheckIn"
                                               href="{{ route('admin.employees.toggle-holiday-checkin', $value->id) }}"
                                               type="checkbox" {{ $value->allow_holiday_check_in == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center col-workplace">
                                    <a class="changeWorkPlace btn btn-{{ $changeColor[$value->workspace_type] }} btn-xs"
                                       data-href="{{ route('admin.employees.change-workspace', $value->id) }}"
                                       title="Change workspace">
                                        {{ $value->workspace_type == User::FIELD ? 'Field' : 'Office' }}
                                    </a>
                                </td>
                                <td class="text-center col-boolean">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{ route('admin.employees.toggle-status', $value->id) }}"
                                               type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['employee.profile.view','edit_employee','delete_employee','change_password','force_logout'])
                                    <td class="text-center col-action">
                                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-haspopup="true"
                                           aria-expanded="false"
                                           title="{{ __('index.action') }}"
                                        >
                                        </a>

                                        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                                            <ul class="list-unstyled p-1 mb-0">
                                                @can('employee.profile.view')
                                                    <li class="dropdown-item py-2">
                                                        <a href="{{ route('admin.employees.profile.show', $value->id) }}">
                                                            <button class="btn btn-primary btn-xs">Employee 360</button>
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('edit_employee')
                                                    <li class="dropdown-item py-2">
                                                        <a href="{{ route('admin.employees.edit', $value->id) }}"
                                                           target="_blank"
                                                           rel="noopener noreferrer">
                                                            <button
                                                                class="btn btn-primary btn-xs">{{ __('index.edit_detail') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('edit_employee')
                                                    <li class="dropdown-item py-2">
                                                        @php $telegramConnectUrl = $telegramBotUsername ? TelegramBotSettings::connectUrl($value) : null; @endphp
                                                        @if($telegramConnectUrl)
                                                            <button type="button"
                                                                    class="btn {{ $value->telegram_chat_id ? 'btn-success' : 'btn-primary' }} btn-xs"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#telegramConnectModal{{ $value->id }}">
                                                                <i class="link-icon" data-feather="{{ $value->telegram_chat_id ? 'check-circle' : 'send' }}"></i>
                                                                {{ $value->telegram_chat_id ? 'Has been connected' : 'Connect to Telegram' }}
                                                            </button>
                                                        @else
                                                            <button class="btn btn-secondary btn-xs" disabled title="Save Bot Username in Telegram Bot settings first">
                                                                <i class="link-icon" data-feather="send"></i> Connect to Telegram
                                                            </button>
                                                        @endif
                                                    </li>
                                                @endcan

                                                @can('delete_employee')
                                                    @if( (isset(auth()->user()->id) && $value->id != auth()->user()->id) || $value->id != 1)
                                                        <li class="dropdown-item py-2">
                                                            <a class="deleteEmployee"
                                                               data-href="{{ route('admin.employees.delete', $value->id) }}">
                                                                <button
                                                                    class="btn btn-primary btn-xs">{{ __('index.delete_user') }}</button>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endcan

                                                @can('change_password')
                                                    <li class="dropdown-item py-2">
                                                        <a class="changePassword"
                                                           data-href="{{ route('admin.employees.change-password', $value->id) }}">
                                                            <button
                                                                class="btn btn-primary btn-xs">{{ __('index.change_password') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('force_logout')
                                                    <li class="dropdown-item py-2">
                                                        <a class="forceLogOut"
                                                           data-href="{{ route('admin.employees.force-logout', $value->id) }}">
                                                            <button
                                                                class="btn btn-primary btn-xs">{{ __('index.force_logout') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>

                                        @can('edit_employee')
                                            @if($telegramConnectUrl)
                                                <div class="modal fade" id="telegramConnectModal{{ $value->id }}" tabindex="-1"
                                                     aria-labelledby="telegramConnectModalLabel{{ $value->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="telegramConnectModalLabel{{ $value->id }}">
                                                                    {{ $value->telegram_chat_id ? ucfirst($value->name) . ' has been connected' : 'Connect ' . ucfirst($value->name) . ' to Telegram' }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="telegram-connect-card">
                                                                    @if($value->telegram_chat_id)
                                                                        <div class="telegram-connect-status">
                                                                            <i class="link-icon" data-feather="check-circle"></i>
                                                                            Has been connected
                                                                        </div>

                                                                        <p class="mb-2">
                                                                            This employee is already connected to Telegram.
                                                                        </p>

                                                                        <div class="text-muted mb-3">
                                                                            Chat ID: {{ $value->telegram_chat_id }}
                                                                            @if($value->telegram_username)
                                                                                <br>Username: {{ '@' . $value->telegram_username }}
                                                                            @endif
                                                                            @if($value->telegram_linked_at)
                                                                                <br>Linked at: {{ optional($value->telegram_linked_at)->format('Y-m-d H:i') }}
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <div class="telegram-connect-qr">
                                                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(190)->margin(1)->generate($telegramConnectUrl) !!}
                                                                        </div>

                                                                        <p class="mb-2">
                                                                            Scan this QR code or open the link to connect this employee with the Telegram bot.
                                                                        </p>
                                                                    @endif

                                                                    <div class="input-group input-group-sm">
                                                                        <input type="text"
                                                                               class="form-control telegram-connect-link"
                                                                               id="telegramConnectLink{{ $value->id }}"
                                                                               value="{{ $telegramConnectUrl }}"
                                                                               readonly>
                                                                        <button type="button"
                                                                                class="btn btn-outline-secondary copyTelegramConnectLink"
                                                                                data-target="telegramConnectLink{{ $value->id }}">
                                                                            Copy
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form method="POST" action="{{ route('admin.telegram-employees.sync-starts') }}" class="m-0">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-outline-success">
                                                                        <i class="link-icon" data-feather="download-cloud"></i>
                                                                        Sync Telegram Starts
                                                                    </button>
                                                                </form>
                                                                <a href="{{ $telegramConnectUrl }}"
                                                                   target="_blank"
                                                                   rel="noopener noreferrer"
                                                                   class="btn {{ $value->telegram_chat_id ? 'btn-outline-primary' : 'btn-primary' }}">
                                                                    <i class="link-icon" data-feather="send"></i>
                                                                    {{ $value->telegram_chat_id ? 'Open Connect Link Again' : 'Open Telegram' }}
                                                                </a>
                                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $users->appends($_GET)->links() }}
        </div>
        </div>

    </section>
    @include('admin.employees.common.password')
@endsection

@section('scripts')
    @include('admin.employees.common.scripts')
    <script>
        $(document).on('click', '.copyTelegramConnectLink', function () {
            var button = $(this);
            var input = document.getElementById(button.data('target'));

            if (!input) {
                return;
            }

            input.select();
            input.setSelectionRange(0, input.value.length);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }

            var originalText = button.text();
            button.text('Copied');

            setTimeout(function () {
                button.text(originalText);
            }, 1500);
        });

        var telegramConnectSyncTimer = null;
        var telegramConnectSyncAttempts = 0;

        function syncTelegramStartsFromEmployeeModal() {
            fetch('{{ route('admin.telegram-employees.sync-starts') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.linked && Number(data.linked) > 0) {
                    window.location.reload();
                }
            }).catch(function () {});
        }

        $(document).on('shown.bs.modal', '[id^="telegramConnectModal"]', function () {
            telegramConnectSyncAttempts = 0;
            clearInterval(telegramConnectSyncTimer);
            telegramConnectSyncTimer = setInterval(function () {
                telegramConnectSyncAttempts++;
                syncTelegramStartsFromEmployeeModal();

                if (telegramConnectSyncAttempts >= 12) {
                    clearInterval(telegramConnectSyncTimer);
                }
            }, 5000);
        });

        $(document).on('hidden.bs.modal', '[id^="telegramConnectModal"]', function () {
            clearInterval(telegramConnectSyncTimer);
        });
    </script>
@endsection
