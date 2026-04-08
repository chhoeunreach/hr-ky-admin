@extends('layouts.master')

@section('title', __('index.employees_title'))

@section('action', __('index.employees_action'))

@section('button')
    @can('create_employee')
        <div class="float-md-end d-flex align-items-center gap-2 justify-content-center">

            <a href="{{ route('admin.users.create')}}">
                <button class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_employee') }}
                </button>
            </a>
            <a href="{{ route('admin.users.export')}}">
                <button class="btn btn-warning d-flex align-items-center gap-2">
                    <i class="link-icon" data-feather="download"></i>{{ __('index.export_csv') }}
                </button>
            </a>
        </div>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.users.common.breadcrumb')

        <div class="search-box p-4 bg-white rounded mb-3 box-shadow pb-2">
            <form class="forms-sample" action="{{ route('admin.users.index') }}" method="get">
                <h5>{{ __('index.employee_lists') }}</h5>
                <div class="row align-items-center mt-4">

                    <div class="col-xxl col-xl-4 col-md-6 mb-3">
                        <select class="form-control" id="branch" name="branch_id">
                            <option selected disabled>{{ __('index.select_branch') }}</option>
                            @foreach($branches as $branch)
                                <option {{ (($filterParameters['branch_id'] == $branch->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $branch->id)) ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-3">
                        <select class="form-control" id="department" name="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-3">
                        <input type="text" placeholder="{{ __('index.employee_name') }}" id="employeeName" name="employee_name" value="{{ $filterParameters['employee_name'] }}" class="form-control">
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-3">
                        <input type="text" placeholder="{{ __('index.employee_email') }}" id="email" name="email" value="{{ $filterParameters['email'] }}" class="form-control">
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-3">
                        <input type="number" placeholder="{{ __('index.employee_phone') }}" id="phone" name="phone" value="{{ $filterParameters['phone'] }}" class="form-control">
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-3">
                        <button type="submit" class="btn btn-block btn-primary form-control">{{ __('index.filter') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            @can('show_detail_employee')
                                <th>#</th>
                            @endcan
                            <th>{{ __('index.full_name') }}</th>
                            <th>{{ __('index.address') }}</th>
                            <th class="text-center">{{ __('index.email') }}</th>
                            <th class="text-center">{{ __('index.designation') }}</th>
                            <th class="text-center">{{ __('index.department') }}</th>
                            <th class="text-center">{{ __('index.role') }}</th>
                            <th class="text-center">{{ __('index.shift') }}</th>
                            <th class="text-center">{{ __('index.workplace') }}</th>
                            <th class="text-center">{{ __('index.is_active') }}</th>
                            @canany(['edit_employee','delete_employee','change_password','force_logout'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php
                            $changeColor = [
                                0 => 'success',
                                1 => 'primary',
                            ]
                            ?>
                        @forelse($users as $key => $value)
                            <tr>
                                @can('show_detail_employee')
                                    <td>
                                        <a href="{{ route('admin.users.show', $value->id) }}" id="showOfficeTimeDetail">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan
                                <td>
                                    <p>{{ ucfirst($value->name) }}</p>
                                    <small class="text-muted">({{ ucfirst($value->role ? $value->role->name : 'N/A') }})</small>
                                </td>
                                <td>{{ ucfirst($value->address) }}</td>
                                <td class="text-center">{{ $value->email }}</td>
                                <td class="text-center">{{ $value->post ? ucfirst($value->post->post_name) : 'N/A' }}</td>
                                <td class="text-center">{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                <td class="text-center">{{ $value->role ? ucfirst($value->role->name) : 'N/A' }}</td>
                                <td class="text-center">{{ $value->officeTime ? ucfirst($value->officeTime->shift) : 'N/A' }}</td>
                                <td class="text-center">
                                    <a class="changeWorkPlace btn btn-{{ $changeColor[$value->workspace_type] }} btn-xs"
                                       data-href="{{ route('admin.users.change-workspace', $value->id) }}"
                                       title="Change workspace">
                                        {{ $value->workspace_type == \App\Models\User::FIELD ? 'Field' : 'Office' }}
                                    </a>
                                </td>
                                    <td class="text-center">
                                        <label class="switch">
                                            <input class="toggleStatus"
                                                   href="{{ route('admin.users.toggle-status', $value->id) }}"
                                                   type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>

                                    @canany(['edit_employee','delete_employee','change_password','force_logout'])
                                        <td class="text-center">
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
                                                    @can('edit_employee')
                                                        <li class="dropdown-item py-2">
                                                            <a href="{{ route('admin.users.edit', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.edit_detail') }}</button>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('delete_employee')
                                                        @if($value->id != auth()->user()->id || $value->id != 1)
                                                            <li class="dropdown-item py-2">
                                                                <a class="deleteEmployee"
                                                                   data-href="{{ route('admin.users.delete', $value->id) }}">
                                                                    <button class="btn btn-primary btn-xs">{{ __('index.delete_user') }}</button>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endcan

                                                    @can('change_password')
                                                        <li class="dropdown-item py-2">
                                                            <a class="changePassword"
                                                               data-href="{{ route('admin.users.change-password', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.change_password') }}</button>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('force_logout')
                                                        <li class="dropdown-item py-2">
                                                            <a class="forceLogOut"
                                                               data-href="{{ route('admin.users.force-logout', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.force_logout') }}</button>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
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

    </section>
    @include('admin.users.common.password')
@endsection

@section('scripts')
    @include('admin.users.common.scripts')
@endsection
