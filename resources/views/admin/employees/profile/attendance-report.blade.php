@extends('layouts.master')

@section('title', __('index.attendance_confirmation_report'))
@section('action', __('index.employee_profile'))

@section('main-content')
    @php
        $reportDate = request('report_date') ?: now()->format('Y-m-d');
        $departmentNames = $departments->whereIn('id', $departmentIds)->pluck('dept_name')->implode(', ');
        $reportBranch = $branches->firstWhere('id', request('branch_id'));
        $reportBranchName = $reportBranch?->name ?: __('index.all_branches');
        $reportLogo = $reportBranch?->logo
            ? asset(\App\Models\Branch::UPLOAD_PATH . $reportBranch->logo)
            : null;
        $reportOrientation = request('orientation') === 'portrait' ? 'portrait' : 'landscape';
    @endphp
    <section class="content">
        @include('admin.section.flash_message')
        <form id="attendanceReportFilters" method="get" action="{{ route('admin.employees.profile.index') }}" class="attendance-report-filters no-print mb-4">
            <input type="hidden" name="attendance_report" value="1">
            @foreach(['search', 'post_id', 'review_status'] as $filter)
                @if(request()->filled($filter))
                    <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                @endif
            @endforeach
            <div class="row g-3 align-items-end">
                <div class="col-lg-2 col-md-6">
                    <label for="reportDate" class="form-label">{{ __('index.attendance_date') }}</label>
                    <input id="reportDate" class="form-control" type="date" name="report_date" value="{{ $reportDate }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="reportBranch" class="form-label">{{ __('index.branch') }}</label>
                    <select id="reportBranch" class="form-select" name="branch_id">
                        <option value="">{{ __('index.all_branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label for="reportDepartments" class="form-label">{{ __('index.department') }}</label>
                    <select id="reportDepartments" class="form-select" name="department_ids[]" multiple data-placeholder="{{ __('index.all_departments') }}">
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(in_array((string) $department->id, array_map('strval', $departmentIds), true))>{{ $department->dept_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="reportStatus" class="form-label">{{ __('index.status') }}</label>
                    <select id="reportStatus" class="form-select" name="employment_status">
                        <option value="">{{ __('index.all_status') }}</option>
                        @foreach(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected($employmentStatus === $status)>{{ __('index.' . $status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('index.apply') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.employees.profile.index', request()->except(['attendance_report', 'page'])) }}" title="{{ __('index.employee_profile') }}"><i data-feather="arrow-left"></i></a>
                </div>
            </div>
        </form>

        <div class="attendance-report-page-controls no-print">
            <span>A4</span>
            <div class="btn-group" role="group" aria-label="{{ __('index.page_orientation') }}">
                @foreach(['portrait', 'landscape'] as $orientation)
                    <input class="btn-check" type="radio" name="orientation" id="orientation-{{ $orientation }}" value="{{ $orientation }}" form="attendanceReportFilters" @checked($reportOrientation === $orientation)>
                    <label class="btn btn-outline-secondary btn-sm" for="orientation-{{ $orientation }}">{{ __('index.' . $orientation) }}</label>
                @endforeach
            </div>
            @if($canExportAttendanceReport)
                <button type="button" id="attendanceReportWordExport" class="btn btn-outline-primary btn-sm" data-file-name="Daily-Attendance-Checklist-{{ $reportDate }}.docx">
                    <i class="link-icon" data-feather="file-text"></i> {{ __('index.export_word') }}
                </button>
            @endif
            @if($canPrintAttendanceReport)
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" title="{{ __('index.print') }}" aria-label="{{ __('index.print') }}"><i class="link-icon" data-feather="printer"></i></button>
            @endif
        </div>
        <div id="attendanceReportExportError" class="alert alert-danger no-print" role="alert" hidden>{{ __('index.checklist_export_error') }}</div>
        <div class="attendance-report-scroll">
            <article class="attendance-confirmation-paper" data-orientation="{{ $reportOrientation }}">
                <header class="attendance-report-heading">
                    <div class="attendance-report-letterhead">
                        <div class="attendance-report-brand">
                            @if($reportLogo)
                                <img src="{{ $reportLogo }}" alt="{{ $reportBranchName }}">
                            @endif
                            <div><strong>សាខា: {{ $reportBranchName }}</strong><span>{{ __('index.hr_department') }}</span></div>
                        </div>
                    </div>
                    <h1>{{ __('index.attendance_confirmation_report') }}</h1>
                    <p>{{ __('index.attendance_confirmation_subtitle') }}</p>
                </header>
                <div class="attendance-report-meta">
                    <div><strong>{{ __('index.attendance_date') }}:</strong> <span>{{ $reportDate }}</span></div>
                    <div><strong>{{ __('index.branch') }}:</strong> <span>{{ $reportBranchName }}</span></div>
                    <div class="attendance-report-departments"><strong>{{ __('index.department') }}:</strong> <span>{{ $departmentNames ?: __('index.all_departments') }}</span></div>
                    <label><strong>{{ __('index.report_number') }}:</strong> <input type="text" aria-label="{{ __('index.report_number') }}"></label>
                    <label><strong>{{ __('index.department_head') }}:</strong> <input type="text" aria-label="{{ __('index.department_head') }}"></label>
                </div>
                <div class="attendance-report-table-heading">
                    <h2>{{ __('index.attendance_confirmation_employee_table') }}</h2>
                    <span>{{ __('index.checklist_total_employees') }}: <strong>{{ $employees->count() }}</strong></span>
                </div>
                <table class="attendance-confirmation-table">
                    <colgroup><col style="width:4%"><col style="width:9%"><col style="width:16%"><col style="width:13%"><col style="width:8%"><col style="width:8%"><col style="width:6%"><col style="width:6%"><col style="width:20%"><col style="width:10%"></colgroup>
                    <thead>
                    <tr>
                        <th rowspan="2" scope="col">{{ __('index.sn') }}</th>
                        <th rowspan="2" scope="col">{{ __('index.employee_id') }}</th>
                        <th rowspan="2" scope="col">{{ __('index.employee_name') }}</th>
                        <th rowspan="2" scope="col">{{ __('index.department') }}</th>
                        <th colspan="4" scope="colgroup">{{ __('index.attendance_daily_details') }}</th>
                        <th rowspan="2" scope="col">{{ __('index.reason_and_explanation') }}</th>
                        <th rowspan="2" scope="col">{{ __('index.approved_by') }}</th>
                    </tr>
                    <tr>
                        <th>{{ __('index.actual_check_in') }}</th>
                        <th>{{ __('index.actual_check_out') }}</th>
                        <th>{{ __('index.late_arrival') }}</th>
                        <th>{{ __('index.early_departure') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="attendance-report-employee-code">{{ $employee->employee_code ?: $employee->username }}</td>
                            <td class="attendance-report-employee-name">{{ $employee->name ?: $employee->english_name }}</td>
                            <td>{{ $employee->department?->dept_name ?: __('index.not_available') }}</td>
                            <td><input type="text" aria-label="{{ __('index.actual_check_in') }} - {{ $employee->name }}"></td>
                            <td><input type="text" aria-label="{{ __('index.actual_check_out') }} - {{ $employee->name }}"></td>
                            <td class="text-center"><input type="checkbox" aria-label="{{ __('index.late_arrival') }} - {{ $employee->name }}"></td>
                            <td class="text-center"><input type="checkbox" aria-label="{{ __('index.early_departure') }} - {{ $employee->name }}"></td>
                            <td><div contenteditable="true" role="textbox" aria-label="{{ __('index.reason_and_explanation') }} - {{ $employee->name }}"></div></td>
                            <td><div contenteditable="true" role="textbox" aria-label="{{ __('index.approved_by') }} - {{ $employee->name }}"></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center">{{ __('index.no_records_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="attendance-report-summary">
                    @foreach(['present', 'absent', 'late_arrival', 'early_departure'] as $summary)
                        <label><strong>{{ __('index.' . $summary) }}:</strong><input type="number" min="0" step="1" aria-label="{{ __('index.' . $summary) }}"></label>
                    @endforeach
                </div>
                <p class="attendance-report-note"><strong>{{ __('index.note') }}:</strong> {{ __('index.checklist_verification_note') }}</p>
                <footer class="attendance-report-signatures">
                    @foreach(['prepared_by', 'reviewed_by', 'approved_by'] as $signature)
                        <div>
                            <strong>{{ __('index.' . $signature) }}</strong>
                            <div class="attendance-report-signature-space"></div>
                            <label>{{ __('index.name') }}:<input type="text" aria-label="{{ __('index.' . $signature) }} - {{ __('index.name') }}"></label>
                            <label>{{ __('index.date') }}:<input type="text" aria-label="{{ __('index.' . $signature) }} - {{ __('index.date') }}"></label>
                        </div>
                    @endforeach
                </footer>
            </article>
        </div>
    </section>
    <style>
        .attendance-report-filters { padding-bottom: 20px; border-bottom: 1px solid #d6dadd; }
        .attendance-report-filters .select2-selection--multiple { min-height: 38px; }
        .attendance-report-scroll { overflow-x: auto; padding: 2px; }
        .attendance-report-page-controls { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 12px; margin-bottom: 12px; font-size: 12px; }
        .attendance-report-page-controls .link-icon { width: 16px; height: 16px; }
        .attendance-report-page-controls > span { margin-right: auto; color: #555; font-weight: 600; }
        .attendance-confirmation-paper { box-sizing: border-box; background: #fff; color: #171717; padding: 10mm; width: 297mm; min-width: 297mm; min-height: 210mm; margin: 0 auto; border: 1px solid #d6dadd; font-size: 12px; line-height: 1.7; letter-spacing: 0; }
        .attendance-confirmation-paper[data-orientation="portrait"] { width: 210mm; min-width: 210mm; min-height: 297mm; font-size: 11px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-heading h1 { font-size: 18px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-brand strong { font-size: 13px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-brand img { width: 44px; height: 44px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-meta { font-size: 11px; gap: 8px 20px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-table-heading h2 { font-size: 12px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-confirmation-table { font-size: 10px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-confirmation-table th,
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-confirmation-table td { padding: 6px 3px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-summary { gap: 12px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-summary strong { font-size: 10px; }
        .attendance-confirmation-paper[data-orientation="portrait"] .attendance-report-signatures { gap: 20px; }
        .attendance-report-heading { text-align: center; border-bottom: 2px solid #252525; padding-bottom: 16px; margin-bottom: 18px; }
        .attendance-report-letterhead { display: flex; justify-content: space-between; align-items: start; gap: 24px; text-align: left; margin-bottom: 20px; }
        .attendance-report-brand { display: flex; align-items: center; gap: 12px; max-width: 100%; }
        .attendance-report-brand img { width: 56px; height: 56px; object-fit: contain; flex-shrink: 0; }
        .attendance-report-brand strong { display: block; font-size: 16px; overflow-wrap: anywhere; }
        .attendance-report-brand span { display: block; color: #555; font-size: 11px; }
        .attendance-report-heading h1 { font-size: 22px; line-height: 1.7; margin: 0; font-weight: 700; }
        .attendance-report-heading p { font-size: 12px; color: #555; margin: 4px 0 0; }
        .attendance-report-meta { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 10px 32px; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid #c8c8c8; font-size: 12px; }
        .attendance-report-meta > div, .attendance-report-meta label { display: flex; align-items: baseline; gap: 10px; margin: 0; min-width: 0; }
        .attendance-report-meta strong { flex-shrink: 0; }
        .attendance-report-meta span { overflow-wrap: anywhere; }
        .attendance-report-departments { grid-column: 1 / -1; }
        .attendance-confirmation-paper input { border: 0; border-bottom: 1px dotted #888; border-radius: 0; background: transparent; color: #111; min-width: 0; max-width: 100%; font: inherit; padding: 2px; }
        .attendance-report-meta input { flex: 1; }
        .attendance-report-note { font-size: 11px; line-height: 1.8; margin: 12px 0 0; }
        .attendance-report-table-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 10px; }
        .attendance-report-table-heading h2 { font-size: 14px; margin: 0; font-weight: 700; }
        .attendance-report-table-heading > span { font-size: 11px; flex-shrink: 0; }
        .attendance-confirmation-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 11px; }
        .attendance-confirmation-table th, .attendance-confirmation-table td { border: 1px solid #747474; padding: 8px 6px; overflow-wrap: anywhere; vertical-align: middle; }
        .attendance-confirmation-table th { text-align: center; line-height: 1.7; background: #eef0f2; font-weight: 700; }
        .attendance-confirmation-table thead tr:last-child th { background: #f8f9fa; }
        .attendance-confirmation-table tbody td { height: 42px; }
        .attendance-report-employee-code { font-variant-numeric: tabular-nums; }
        .attendance-report-employee-name { font-weight: 600; }
        .attendance-confirmation-table input { width: 100%; text-align: center; }
        .attendance-confirmation-table input[type="checkbox"] { appearance: none; width: 14px; height: 14px; border: 1px solid #555; padding: 0; vertical-align: middle; cursor: pointer; }
        .attendance-confirmation-table input[type="checkbox"]:checked::after { content: 'X'; display: block; text-align: center; font: bold 11px/12px Arial, sans-serif; }
        .attendance-confirmation-table [contenteditable] { min-height: 24px; white-space: pre-wrap; }
        .attendance-confirmation-table [contenteditable]:focus { outline: 1px solid #2563eb; }
        .attendance-report-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; padding: 14px 0; margin-top: 6px; border-bottom: 1px solid #c8c8c8; break-inside: avoid; }
        .attendance-report-summary label { display: flex; align-items: baseline; gap: 8px; margin: 0; min-width: 0; }
        .attendance-report-summary strong { font-size: 11px; }
        .attendance-report-summary input { flex: 1; width: 40px; }
        .attendance-report-signatures { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 40px; text-align: center; margin-top: 24px; font-size: 11px; break-inside: avoid; }
        .attendance-report-signatures > div { min-width: 0; }
        .attendance-report-signatures strong { font-size: 12px; }
        .attendance-report-signature-space { height: 56px; border-bottom: 1px solid #777; margin-bottom: 8px; }
        .attendance-report-signatures label { display: flex; align-items: baseline; gap: 8px; text-align: left; margin: 6px 0 0; }
        .attendance-report-signatures input { flex: 1; width: 0; }
        @media print {
            .sidebar, .navbar, .footer, #preloader, .breadcrumb, .no-print { display: none !important; }
            .main-wrapper, .page-wrapper, .page-content, .content, .attendance-report-scroll { margin: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
            .attendance-confirmation-paper,
            .attendance-confirmation-paper[data-orientation="portrait"] { padding: 0; min-width: 0; min-height: 0; max-width: none; width: 100%; border: 0; }
            .attendance-report-heading { break-inside: avoid; }
            .attendance-report-table-heading { break-after: avoid; }
            .attendance-report-meta { break-inside: avoid; }
            .attendance-confirmation-table thead { display: table-header-group; }
            .attendance-confirmation-table tr { break-inside: avoid; }
            .attendance-confirmation-paper input:not([type="checkbox"]) { appearance: textfield; }
            .attendance-confirmation-paper input::-webkit-inner-spin-button { appearance: none; }
            .attendance-confirmation-paper input::-webkit-calendar-picker-indicator { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <style id="attendanceReportPageSize">@page { size: A4 {{ $reportOrientation }}; margin: 10mm; }</style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paper = document.querySelector('.attendance-confirmation-paper');
            const pageStyle = document.getElementById('attendanceReportPageSize');
            document.querySelectorAll('input[name="orientation"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    const orientation = input.value === 'portrait' ? 'portrait' : 'landscape';
                    paper.dataset.orientation = orientation;
                    pageStyle.textContent = '@page { size: A4 ' + orientation + '; margin: 10mm; }';
                });
            });
        });
    </script>
    @include('admin.employees.profile.partials.department-filter-script', ['departmentFilterForm' => 'attendanceReportFilters'])
    @if($canExportAttendanceReport)
        <script src="https://cdn.jsdelivr.net/npm/docx@8.5.0/build/index.umd.js"></script>
        <script src="{{ asset('assets/js/attendance-checklist-word.js') }}"></script>
    @endif
@endsection
