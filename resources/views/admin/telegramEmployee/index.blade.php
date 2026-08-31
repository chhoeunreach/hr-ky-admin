@extends('layouts.master')

@section('title', 'Employee Telegram Alerts')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Employee Telegram Alerts</h4>
                <p class="text-muted mb-0">Link employee personal chats and send Telegram alerts.</p>
            </div>
            <a href="{{ route('admin.telegram-bot.index') }}" class="btn btn-outline-primary">
                <i class="link-icon" data-feather="settings"></i> Telegram Bot
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Broadcast Alert</h6>
            </div>
            <form class="card-body" method="POST" action="{{ route('admin.telegram-employees.broadcast') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label" for="broadcast_branch_id">Branch</label>
                        <select class="form-select" id="broadcast_branch_id" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label" for="broadcast_department_id">Department</label>
                        <select class="form-select" id="broadcast_department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-8 mb-3">
                        <label class="form-label" for="broadcast_message">Message</label>
                        <textarea class="form-control" id="broadcast_message" name="message" rows="2" required>{{ old('message') }}</textarea>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="link-icon" data-feather="send"></i> Send Alert
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Employee Filters</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.telegram-employees.index') }}" method="get">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="text" placeholder="Search employee or chat ID" name="search" value="{{ $filters['search'] }}" class="form-control">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $filters['branch_id'] === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string) $filters['department_id'] === (string) $department->id ? 'selected' : '' }}>{{ $department->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select" name="linked">
                            <option value="">All Link Status</option>
                            <option value="yes" {{ $filters['linked'] === 'yes' ? 'selected' : '' }}>Linked</option>
                            <option value="no" {{ $filters['linked'] === 'no' ? 'selected' : '' }}>Not Linked</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-6 d-flex mb-4">
                        <button type="submit" class="btn btn-success me-2">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Personal Telegram Links</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Employees can send <code>/link EMPLOYEE_CODE</code> to the bot. Admins can also paste the chat ID manually.</p>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Telegram</th>
                            <th>Message</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <strong>{{ $employee->name }}</strong>
                                    <div class="text-muted small">{{ $employee->employee_code ?: $employee->username }}</div>
                                </td>
                                <td>{{ $employee->branch?->name ?: 'N/A' }}</td>
                                <td>{{ $employee->department?->dept_name ?: 'N/A' }}</td>
                                <td style="min-width: 280px;">
                                    <form method="POST" action="{{ route('admin.telegram-employees.update', $employee->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" name="telegram_chat_id" class="form-control" value="{{ $employee->telegram_chat_id }}" placeholder="Chat ID">
                                            <input type="text" name="telegram_username" class="form-control" value="{{ $employee->telegram_username ? '@' . $employee->telegram_username : '' }}" placeholder="@username">
                                            <button type="submit" class="btn btn-outline-primary">Save</button>
                                        </div>
                                        @if($employee->telegram_chat_id)
                                            <span class="badge bg-success">Linked</span>
                                            <small class="text-muted">{{ optional($employee->telegram_linked_at)->format('Y-m-d H:i') }}</small>
                                        @else
                                            <span class="badge bg-secondary">Not linked</span>
                                        @endif
                                    </form>
                                </td>
                                <td style="min-width: 320px;">
                                    <form method="POST" action="{{ route('admin.telegram-employees.send', $employee->id) }}">
                                        @csrf
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="message" class="form-control" placeholder="Private message" {{ $employee->telegram_chat_id ? '' : 'disabled' }} required>
                                            <button type="submit" class="btn btn-outline-success" {{ $employee->telegram_chat_id ? '' : 'disabled' }}>
                                                <i class="link-icon" data-feather="send"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center"><strong>{{ __('index.no_records_found') }}</strong></td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate">
            {{ $employees->appends($_GET)->links() }}
        </div>
    </section>
@endsection
