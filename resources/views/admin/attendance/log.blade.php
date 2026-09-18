@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.attendance_logs'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.attendance.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.log_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.attendance.log') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-3">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option value="">{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-3">
                        <select class="form-select" name="department_id" id="department_id">
                            <option value="">{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option value="">{{ __('index.select_employee') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <select class="form-select" name="action" id="action">
                            <option value="">{{ __('index.all_actions') }}</option>
                            <option value="check_in" {{ ($filterData['action'] ?? '') === 'check_in' ? 'selected' : '' }}>{{ __('index.check_in') }}</option>
                            <option value="check_out" {{ ($filterData['action'] ?? '') === 'check_out' ? 'selected' : '' }}>{{ __('index.check_out') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <select class="form-select" name="attendance_type" id="attendance_type">
                            <option value="">{{ __('index.all_types') }}</option>
                            <option value="wifi" {{ ($filterData['attendance_type'] ?? '') === 'wifi' ? 'selected' : '' }}>WiFi</option>
                            <option value="qr" {{ ($filterData['attendance_type'] ?? '') === 'qr' ? 'selected' : '' }}>QR Code</option>
                            <option value="nfc" {{ ($filterData['attendance_type'] ?? '') === 'nfc' ? 'selected' : '' }}>NFC</option>
                            <option value="face" {{ ($filterData['attendance_type'] ?? '') === 'face' ? 'selected' : '' }}>Face</option>
                            <option value="web" {{ ($filterData['attendance_type'] ?? '') === 'web' ? 'selected' : '' }}>Web</option>
                            <option value="manual" {{ ($filterData['attendance_type'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        @if($isBsEnabled ?? false)
                            <input type="text" id="nepali_startDate" class="form-control nepaliDate" name="date"
                                   placeholder="{{ __('index.date') }}"
                                   value="{{ $filterData['raw_date'] ?? '' }}">
                        @else
                            <input type="date" class="form-control" name="date"
                                   placeholder="{{ __('index.date') }}"
                                   value="{{ $filterData['raw_date'] ?? $filterData['date'] ?? '' }}">
                        @endif
                    </div>

                    <div class="col-lg-3 col-md-6 d-md-flex mb-3">
                        <button type="submit" class="btn btn-success me-md-2 me-0 mb-md-0 mb-2">{{ __('index.filter') }}</button>
                        <a class="btn btn-secondary me-md-2 me-0"
                           href="{{ route('admin.attendance.log') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">{{ __('index.attendance_logs') }}</h6>
                <span class="badge bg-primary rounded-pill">{{ $logData->total() }} {{ __('index.records') }}</span>
            </div>
            <div class="card-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-logs" type="button" role="tab" aria-controls="manual-logs" aria-selected="true">
                            <i class="link-icon" data-feather="activity" style="width: 14px; height: 14px;"></i> {{ __('index.manual_logs') }} ({{ $logData->total() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="biometric-tab" data-bs-toggle="tab" data-bs-target="#biometric-logs" type="button" role="tab" aria-controls="biometric-logs" aria-selected="false">
                            <i class="link-icon" data-feather="cpu" style="width: 14px; height: 14px;"></i> {{ __('index.biometric_logs') }} ({{ $biometricLogData->total() }})
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="attendanceTabsContent">
                    <!-- Manual / App Attendance Logs -->
                    <div class="tab-pane fade show active" id="manual-logs" role="tabpanel" aria-labelledby="manual-tab">
                        <div class="table-responsive">
                            <table id="manualDataTable" class="table table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">SN</th>
                                    <th>{{ __('index.employee_name') }}</th>
                                    <th class="text-center">{{ __('index.activity') }}</th>
                                    <th class="text-center">{{ __('index.attendance_type') }}</th>
                                    <th class="text-center">{{ __('index.identifier') }}</th>
                                    <th class="text-center">{{ __('index.location') }}</th>
                                    <th class="text-center">{{ __('index.date') }}</th>
                                    @can('delete_attendance_log')
                                        <th class="text-center" style="width: 80px;">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($logData as $log)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration + ($logData->currentPage() - 1) * $logData->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="fw-bold">{{ $log->user?->name ?? 'N/A' }}</span>
                                                    @if($log->user?->employee_code)
                                                        <span class="text-muted small d-block">#{{ $log->user->employee_code }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if(($log->action ?? '') === 'check_in')
                                                <span class="badge bg-success">
                                                    <i data-feather="log-in" style="width: 11px; height: 11px;" class="me-1"></i>{{ __('index.check_in') }}
                                                </span>
                                            @elseif(($log->action ?? '') === 'check_out')
                                                <span class="badge bg-danger">
                                                    <i data-feather="log-out" style="width: 11px; height: 11px;" class="me-1"></i>{{ __('index.check_out') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ $log->action ? ucfirst($log->action) : 'Check-In' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $type = strtolower($log->attendance_type ?? '');
                                                $badgeClass = match($type) {
                                                    'wifi' => 'bg-info',
                                                    'qr' => 'bg-primary',
                                                    'nfc' => 'bg-warning text-dark',
                                                    'face' => 'bg-purple text-white',
                                                    'web' => 'bg-dark',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} text-uppercase">{{ $log->attendance_type ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted small" title="{{ $log->identifier ?? 'N/A' }}">
                                                {{ $log->identifier ? \Illuminate\Support\Str::limit($log->identifier, 22) : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if(!empty($log->latitude) && !empty($log->longitude))
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ __('index.show_location') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ 'https://maps.google.com/maps?q=' . $log->latitude . ',' . $log->longitude . '&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="#locationModal">
                                                    <i data-feather="map-pin" style="width: 11px; height: 11px;" class="me-1"></i>{{ __('index.view_location') }}
                                                </span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ \App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->created_at ?? $log->updated_at) }}
                                        </td>
                                        @can('delete_attendance_log')
                                            <td class="text-center">
                                                <form action="{{ route('admin.attendance.log.delete', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('index.confirm_delete_log') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2" style="width: 13px; height: 13px;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center py-3"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="dataTables_paginate mt-3">
                            {{ $logData->appends(request()->query())->links() }}
                        </div>
                    </div>

                    <!-- Biometric Attendance Logs -->
                    <div class="tab-pane fade" id="biometric-logs" role="tabpanel" aria-labelledby="biometric-tab">
                        <div class="table-responsive">
                            <table id="biometricDataTable" class="table table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">SN</th>
                                    <th>{{ __('index.employee_name') }}</th>
                                    <th>{{ __('index.device_serial_number') }}</th>
                                    <th class="text-center">{{ __('index.attendance_status') }}</th>
                                    <th class="text-center">{{ __('index.date') }}</th>
                                    @can('delete_attendance_log')
                                        <th class="text-center" style="width: 80px;">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($biometricLogData as $log)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration + ($biometricLogData->currentPage() - 1) * $biometricLogData->perPage() }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $log->user?->name ?? 'N/A' }}</span>
                                            @if($log->user?->employee_code)
                                                <span class="text-muted small d-block">#{{ $log->user->employee_code }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $log->sn ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($log->attendance_status == 0)
                                                <span class="badge bg-success">
                                                    <i data-feather="log-in" style="width: 11px; height: 11px;" class="me-1"></i>{{ __('index.check_in') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i data-feather="log-out" style="width: 11px; height: 11px;" class="me-1"></i>{{ __('index.check_out') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ \App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->timestamp) }}</td>
                                        @can('delete_attendance_log')
                                            <td class="text-center">
                                                <form action="{{ route('admin.attendance.biometric-log.delete', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('index.confirm_delete_log') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2" style="width: 13px; height: 13px;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center py-3"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="dataTables_paginate mt-3">
                            {{ $biometricLogData->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Google Maps -->
        <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="locationModalLabel">{{ __('index.location') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="mapFrame" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $("#department_id").select2();
            $("#branch_id").select2();
            $("#employee_id").select2();
            $("#action").select2();
            $("#attendance_type").select2();

            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const branchId = "{{ $filterData['branch_id'] ?? null }}";
            const departmentId = "{{ $filterData['department_id'] ?? '' }}";
            const employeeId = "{{ $filterData['employee_id'] ?? '' }}";

            // Location modal handler
            $(document).on('click', '.checkLocation', function () {
                const url = $(this).attr('data-href');
                $('#mapFrame').attr('src', url);
            });

            // Re-render feather icons on tab change
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                if (feather) {
                    feather.replace();
                }
            });

            const loadDepartments = async (selectedBranchId) => {
                if (!selectedBranchId) return;
                try {
                    $('#department_id').empty().append('<option value="">{{ __("index.select_department") }}</option>');
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });
                    if (!response || !response.data || response.data.length === 0) {
                        $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                        return;
                    }
                    response.data.forEach(data => {
                        $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                    });
                } catch (error) {
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                if (!selectedDepartmentId) return;
                try {
                    $('#employee_id').empty().append('<option value="">{{ __("index.select_employee") }}</option>');
                    const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });
                    const data = await response.json();
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(user => {
                            $('#employee_id').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''} >${user.name}</option>`);
                        });
                    } else {
                        $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    }
                } catch (error) {
                    $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                }
            };

            const initializeDropdowns = async () => {
                let selectedBranchId;
                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;
                    $('#branch_id').on('change', async () => {
                        const newBranchId = $('#branch_id').val();
                        await loadDepartments(newBranchId);
                        $('#employee_id').empty().append('<option value="">{{ __("index.select_employee") }}</option>');
                        await loadEmployees();
                    });
                    if (selectedBranchId) {
                        $('#branch_id').trigger('change');
                    }
                } else {
                    selectedBranchId = defaultBranchId;
                    if (selectedBranchId) {
                        await loadDepartments(selectedBranchId);
                        await loadEmployees();
                    }
                }
                $('#department_id').on('change', loadEmployees);
                if (departmentId) {
                    $('#department_id').trigger('change');
                }
            };

            initializeDropdowns();
        });
    </script>
@endsection
