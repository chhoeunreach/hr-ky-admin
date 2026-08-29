@extends('layouts.master')

@section('title', 'Employee Profiles')

@section('action', 'Employee Profile')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between gap-3">
                <h6 class="card-title mb-0">Employee Profile</h6>
                <form method="get" class="d-flex gap-2">
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search employee">
                    <button class="btn btn-primary">Search</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th class="text-end">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>{{ $employee->english_name ?: $employee->name }}</td>
                                <td>{{ $employee->employee_code ?: $employee->username }}</td>
                                <td>{{ $employee->branch?->name ?: 'N/A' }}</td>
                                <td>{{ $employee->department?->dept_name ?: 'N/A' }}</td>
                                <td>{{ $employee->post?->post_name ?: 'N/A' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-primary btn-xs" href="{{ route('admin.employees.profile.show', $employee->id) }}">Employee 360</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No records found</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $employees->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
@endsection
