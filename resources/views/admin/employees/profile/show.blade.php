@extends('layouts.master')

@section('title', 'Employee 360 Profile')

@section('action', 'Employee Profile')

@section('button')
    <div class="d-md-flex">
        @can('employee.performance.create')
            <a href="{{ route('admin.staff-evaluations.ai-create', ['employee_id' => $employee->id]) }}" class="btn btn-success me-2">
                AI Evaluation Form
            </a>
        @endcan
        <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-outline-secondary me-2">
            <i class="link-icon" data-feather="eye"></i>Basic Detail
        </a>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-primary">
            <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}
        </a>
    </div>
@endsection

@section('main-content')
    <section class="content employee-360">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        @php
            $avatar = $employee->avatar
                ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $employee->avatar)
                : asset('assets/images/img.png');
            $metric = function ($label, $value) {
                return ['label' => $label, 'value' => filled($value) ? $value : 'N/A'];
            };
            $overviewMetrics = [
                $metric('Employee ID', $summary['employee_id'] ?? null),
                $metric('Position', $summary['position'] ?? null),
                $metric('Department', $summary['department'] ?? null),
                $metric('Branch', $summary['branch'] ?? null),
                $metric('Direct Manager', $summary['manager'] ?? null),
                $metric('Join Date', $summary['join_date'] ?? null),
                $metric('Years of Service', $summary['years_of_service'] ?? null),
                $metric('Employment Status', ucfirst($summary['employment_status'] ?? '')),
                $metric('Probation Status', $summary['probation_status'] ?? null),
                $metric('Present', $attendanceSummary['present_days'] ?? null),
                $metric('Late', $attendanceSummary['late_count'] ?? null),
                $metric('Absent', $attendanceSummary['absent_days'] ?? null),
                $metric('Leave', $attendanceSummary['leave_days'] ?? null),
                $metric('Off Day', $attendanceSummary['off_day_days'] ?? null),
                $metric('Pending Day Off', $attendanceSummary['pending_day_off_days'] ?? null),
                $metric('Pending Leave', $attendanceSummary['pending_leave_days'] ?? null),
                $metric('Time Leave', $attendanceSummary['time_leave_days'] ?? null),
                $metric('Time Leave Request', $attendanceSummary['time_leave_requests'] ?? null),
                $metric('No Checkout', $attendanceSummary['no_checkout_days'] ?? null),
                $metric('Worked Hours', $attendanceSummary['worked_hours'] ?? null),
                $metric('Not Late Until', $attendanceSummary['not_late_until'] ?? null),
                $metric('Office Time', $attendanceSummary['office_time'] ?? null),
                $metric('Attendance Score', $attendanceSummary['attendance_score'] ?? null),
                $metric('Leave Balance', $leaveBalance),
            ];
            if ($canViewPerformance) {
                $overviewMetrics[] = $metric('Evaluation Score', $summary['last_evaluation_score'] ?? null);
                $overviewMetrics[] = $metric('Evaluation Grade', $summary['evaluation_grade'] ?? null);
                $overviewMetrics[] = $metric('Next Evaluation', $summary['next_evaluation_date'] ?? null);
            }
            if ($canViewDiscipline) {
                $overviewMetrics[] = $metric('Total Warnings', $summary['total_warnings'] ?? 0);
            }
            if ($canViewReward) {
                $overviewMetrics[] = $metric('Total Rewards', $summary['total_rewards'] ?? 0);
            }
            if ($canViewTraining) {
                $overviewMetrics[] = $metric('Training Completed', $summary['training_completed'] ?? 0);
            }
            if ($canViewSalary) {
                $overviewMetrics[] = $metric('Current Base Salary', $summary['current_base_salary'] ?? null);
                $overviewMetrics[] = $metric('Last Salary Increase', $summary['last_salary_increase'] ?? null);
            }
            $defaultItems = [
                ['Work Quality - Graphic Design', 'Clean, accurate design aligned with brand standards', 15],
                ['Task Completion', 'Complete assigned thumbnails, posters, and tasks on schedule', 10],
                ['Thumbnail & Poster', 'Create attractive visuals that match requirements', 10],
                ['IT Support', 'Support PC, Mac, and network issues effectively', 10],
                ['Stream Live Control', 'Prepare and manage stable live streams', 10],
                ['Content & Video Editing', 'Support technical content and quality video editing', 10],
                ['Responsibility', 'Stay focused, careful, and accountable for assigned work', 10],
                ['Teamwork', 'Cooperate and support teammates', 5],
                ['Accepting Guidance', 'Listen to and apply manager guidance', 5],
                ['Persistence & Patience', 'Work under pressure without giving up easily', 5],
                ['Learning / Training', 'Learn new skills and technical methods quickly', 5],
                ['Communication & Initiative', 'Communicate well and seek ways to improve work', 5],
            ];
            $tabs = [
                'overview' => 'Overview',
                'complete-form' => 'Complete Form',
                'personal' => 'Personal & Documents',
                'attendance' => 'Attendance',
            ];
            if ($canViewDocument) {
                $tabs['contract-form'] = 'Contract Form';
            }
            if ($canViewDiscipline) {
                $tabs['discipline'] = 'Staff Warning Records';
                $tabs['staff-warning-form'] = 'Staff Warning Form';
            }
            if ($canViewEmployment) {
                $tabs['employment'] = 'Employment';
            }
            if ($canViewSalary) {
                $tabs['salary'] = 'Salary';
            }
            if ($canViewInterview) {
                $tabs['interview'] = 'Interview';
            }
            if ($canViewKpi) {
                $tabs['kpi'] = 'KPI';
            }
            if ($canViewPerformance) {
                $tabs['evaluation'] = 'Evaluation';
            }
            if ($canViewTraining) {
                $tabs['training'] = 'Training';
            }
            if ($canViewReward) {
                $tabs['rewards'] = 'Rewards';
            }
            if ($canViewGoal) {
                $tabs['goals'] = 'Goals';
            }
            if ($canViewAudit) {
                $tabs['history'] = 'History';
            }
        @endphp

        <style>
            .employee-360-hero {
                display: grid;
                grid-template-columns: auto 1fr;
                align-items: center;
                gap: 18px;
                margin-bottom: 18px;
            }
            .employee-360-hero img {
                width: 86px;
                height: 86px;
                object-fit: cover;
                border: 3px solid #fff;
                box-shadow: 0 8px 22px rgba(15, 23, 42, .12);
            }
            .employee-360-title {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
            }
            .employee-360-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
                border-bottom: 1px solid #dbe3ef;
            }
            .employee-360-tabs .nav-link {
                white-space: nowrap;
                color: #475569;
                border: 0;
                border-bottom: 2px solid transparent;
                border-radius: 0;
                padding: 12px 14px;
                font-weight: 600;
            }
            .employee-360-tabs .nav-link.active {
                color: #0f766e;
                border-bottom-color: #0f766e;
                background: transparent;
            }
            .employee-360-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
                gap: 12px;
            }
            .employee-360-metric {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 12px;
                background: #fff;
                min-height: 78px;
            }
            .employee-360-metric small {
                display: block;
                color: #64748b;
                font-weight: 600;
                margin-bottom: 6px;
            }
            .employee-360-metric strong {
                color: #0f172a;
                overflow-wrap: anywhere;
            }
            .employee-360-section {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 16px;
                margin-bottom: 16px;
                background: #fff;
            }
            .employee-360-section h6 {
                margin-bottom: 14px;
            }
            .employee-360-table {
                font-size: .8125rem;
            }
            .employee-360-table th {
                white-space: nowrap;
                color: #475569;
            }
            .employee-complete-toolbar {
                display: flex;
                justify-content: flex-end;
                margin: 0 auto 14px;
                max-width: 178mm;
            }
            #complete-form {
                background: #eef2f7;
                border-radius: 6px;
                margin: -6px;
                padding: 18px 8px;
            }
            #staff-warning-form {
                background: #eef2f7;
                border-radius: 6px;
                margin: -6px;
                padding: 18px 8px;
            }
            #contract-form {
                background: #eef2f7;
                border-radius: 6px;
                margin: -6px;
                padding: 18px 8px;
            }
            #employeeOverviewPrintRoot,
            #employeeCompletePrintRoot,
            #employeeWarningPrintRoot,
            #employeeContractPrintRoot {
                display: none;
            }
            .employee-complete-paper {
                --employee-complete-accent: var(--primary-color, #0f766e);
                --employee-complete-accent-soft: color-mix(in srgb, var(--employee-complete-accent) 7%, #ffffff);
                --employee-complete-accent-rail: color-mix(in srgb, var(--employee-complete-accent) 16%, transparent);
                background: #fff;
                background-image:
                    linear-gradient(180deg, var(--employee-complete-accent-soft), transparent 170px),
                    linear-gradient(90deg, var(--employee-complete-accent-rail) 0, var(--employee-complete-accent-rail) 5px, transparent 5px);
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                box-sizing: border-box;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
                color: #111827;
                font-family: 'Kantumruy Pro', 'Noto Sans Khmer', 'Roboto', Arial, sans-serif;
                font-size: 10px;
                line-height: 1.35;
                margin: 0 auto;
                max-width: 178mm;
                min-height: 252mm;
                padding: 10mm 9mm;
                width: 100%;
            }
            .employee-complete-paper *,
            .employee-complete-paper *::before,
            .employee-complete-paper *::after {
                box-sizing: border-box;
            }
            .employee-complete-header {
                border-bottom: 2px solid var(--employee-complete-accent);
                margin-bottom: 11px;
                padding-bottom: 10px;
                position: relative;
                text-align: center;
            }
            .employee-complete-logo {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 50%;
                box-shadow: 0 6px 16px rgba(15, 23, 42, .08);
                height: 24mm;
                object-fit: contain;
                padding: 2mm;
                position: absolute;
                left: 0;
                top: 0;
                width: 24mm;
                z-index: 1;
            }
            .employee-complete-header::before {
                background: linear-gradient(90deg, #ffffff 0%, rgba(255, 255, 255, .92) 58%, rgba(255, 255, 255, 0) 100%);
                content: "";
                height: 28mm;
                left: -2mm;
                pointer-events: none;
                position: absolute;
                top: -2mm;
                width: 42mm;
            }
            .employee-complete-khmer-brand {
                color: var(--employee-complete-accent);
                font-family: 'Kantumruy Pro', 'Noto Sans Khmer', 'Roboto', Arial, sans-serif;
                font-size: 21px;
                font-weight: 800;
                margin-bottom: 2px;
            }
            .employee-complete-brand {
                color: var(--employee-complete-accent);
                font-size: 14px;
                font-weight: 800;
                letter-spacing: 1.8px;
                margin-bottom: 4px;
            }
            .employee-complete-header h4 {
                color: #0f172a;
                font-size: 14px;
                font-weight: 700;
                margin-bottom: 4px;
            }
            .employee-complete-subtitle {
                color: #334155;
                font-size: 10.5px;
                font-weight: 700;
                text-transform: uppercase;
            }
            .employee-complete-confidential {
                color: #64748b;
                display: block;
                margin-top: 4px;
            }
            .employee-complete-meta {
                display: grid;
                gap: 6px;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                margin: 9px 0 12px;
            }
            .employee-complete-meta div {
                background: rgba(248, 250, 252, .9);
                border: 1px solid #dbe3ef;
                border-radius: 5px;
                padding: 5px 7px;
            }
            .employee-complete-meta small {
                color: #64748b;
                display: block;
                font-weight: 700;
                margin-bottom: 2px;
            }
            .employee-complete-meta strong {
                color: #0f172a;
                font-size: 10.5px;
                font-weight: 700;
            }
            .employee-complete-section {
                break-inside: avoid;
                margin-bottom: 9px;
            }
            .employee-complete-section h6 {
                align-items: center;
                background: linear-gradient(90deg, var(--employee-complete-accent-soft), #f8fafc);
                border-left: 4px solid var(--employee-complete-accent);
                border-radius: 4px;
                color: #0f172a;
                display: flex;
                font-size: 10.5px;
                font-weight: 700;
                justify-content: space-between;
                margin-bottom: 5px;
                padding: 5px 8px;
            }
            .employee-complete-list {
                border-left: 1px solid #dbe3ef;
                border-top: 1px solid #dbe3ef;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .employee-complete-field {
                border-bottom: 1px solid #dbe3ef;
                border-right: 1px solid #dbe3ef;
                display: grid;
                grid-template-columns: minmax(108px, 36%) 1fr;
                min-height: 27px;
            }
            .employee-complete-field span {
                background: #f8fafc;
                border-right: 1px solid #dbe3ef;
                color: #475569;
                font-weight: 600;
                padding: 4px 6px;
            }
            .employee-complete-field strong {
                font-weight: 500;
                overflow-wrap: anywhere;
                padding: 4px 6px;
            }
            .employee-complete-table th,
            .employee-complete-table td {
                border: 1px solid #dbe3ef !important;
                font-size: 9.5px;
                padding: 4px 5px !important;
                vertical-align: top;
            }
            .employee-complete-table th {
                background: #f1f5f9;
                color: #334155;
                font-weight: 700;
            }
            .employee-complete-table tbody tr:nth-child(even) td {
                background: #fbfdff;
            }
            .employee-complete-signatures {
                display: grid;
                gap: 6px;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            .employee-complete-signature {
                background: #fbfdff;
                border: 1px solid #dbe3ef;
                border-radius: 5px;
                min-height: 104px;
                padding: 8px;
            }
            .employee-complete-signature strong {
                color: var(--employee-complete-accent);
            }
            .employee-warning-paper {
                max-width: 190mm;
                min-height: 277mm;
                padding: 12mm 12mm;
            }
            .employee-warning-title {
                color: #991b1b;
                font-size: 18px;
                font-weight: 800;
                letter-spacing: .7px;
                margin-bottom: 4px;
                text-transform: uppercase;
            }
            .employee-warning-paper .employee-complete-header {
                border-bottom-color: #991b1b;
            }
            .employee-warning-section h6 {
                background: linear-gradient(90deg, #fee2e2, #f8fafc);
                border-left-color: #991b1b;
            }
            .employee-warning-checks {
                display: grid;
                gap: 8px 14px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-top: 8px;
            }
            .employee-warning-check {
                align-items: center;
                display: flex;
                gap: 8px;
                min-height: 24px;
            }
            .employee-warning-box {
                border: 1.5px solid #334155;
                display: inline-block;
                height: 13px;
                width: 13px;
            }
            .employee-warning-lines {
                border: 1px solid #dbe3ef;
                min-height: 72px;
                padding: 8px;
            }
            .employee-warning-lines.tall {
                min-height: 96px;
            }
            .employee-warning-footer {
                border-top: 2px solid #991b1b;
                color: #64748b;
                font-size: 10px;
                font-weight: 700;
                margin-top: 12px;
                padding-top: 8px;
                text-align: center;
                text-transform: uppercase;
            }
            .employee-warning-signatures {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .employee-contract-paper {
                --employee-complete-accent: #1d4ed8;
                --employee-complete-accent-soft: #eff6ff;
                --employee-complete-accent-rail: rgba(29, 78, 216, .13);
                max-width: 190mm;
                min-height: 277mm;
                padding: 11mm 12mm;
            }
            .employee-contract-title {
                color: #1d4ed8;
                font-size: 20px;
                font-weight: 800;
                margin-bottom: 3px;
            }
            .employee-contract-paper .employee-complete-header {
                border-bottom-color: #1d4ed8;
            }
            .employee-contract-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .employee-contract-party-title {
                color: #1e3a8a;
                font-size: 11px;
                font-weight: 800;
                margin: 7px 0 5px;
            }
            .employee-contract-article {
                border: 1px solid #dbe3ef;
                border-radius: 5px;
                margin-bottom: 7px;
                padding: 7px 9px;
            }
            .employee-contract-article h6 {
                background: transparent;
                border-left: 0;
                color: #1e3a8a;
                display: block;
                font-size: 11px;
                margin: 0 0 4px;
                padding: 0;
            }
            .employee-contract-article p,
            .employee-contract-article li {
                margin-bottom: 3px;
            }
            .employee-contract-article ul {
                margin-bottom: 0;
                padding-left: 18px;
            }
            .employee-contract-rule-box {
                border: 1px solid #111827;
                margin: 6px 0;
                padding: 7px 10px;
            }
            .employee-contract-diamond-list {
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .employee-contract-diamond-list li {
                color: #111827;
                font-size: 10.5px;
                line-height: 1.55;
                margin-bottom: 4px;
                padding-left: 18px;
                position: relative;
            }
            .employee-contract-diamond-list li::before {
                color: #111827;
                content: "❖";
                font-size: 10px;
                font-weight: 700;
                left: 0;
                position: absolute;
                top: 0;
            }
            .employee-contract-red {
                color: #dc2626;
                font-weight: 800;
            }
            .employee-contract-signatures {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .employee-contract-attachments {
                border-top: 1px solid #dbe3ef;
                margin-top: 8px;
                padding-top: 7px;
            }
            .employee-overview-paper {
                --employee-complete-accent: #0f766e;
                --employee-complete-accent-soft: #ecfdf5;
                --employee-complete-accent-rail: rgba(15, 118, 110, .13);
                max-width: 190mm;
                min-height: 277mm;
                padding: 10mm 10mm 8mm;
            }
            .employee-overview-title {
                color: #0f766e;
                font-size: 18px;
                font-weight: 800;
                letter-spacing: .7px;
                text-transform: uppercase;
            }
            .employee-overview-subtitle {
                color: #334155;
                font-size: 12px;
                font-weight: 700;
                margin-top: 3px;
            }
            .employee-overview-summary {
                display: grid;
                gap: 6px;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                margin-bottom: 10px;
            }
            .employee-overview-summary div {
                border: 1px solid #dbe3ef;
                border-radius: 5px;
                min-height: 44px;
                padding: 6px;
                text-align: center;
            }
            .employee-overview-summary small {
                color: #64748b;
                display: block;
                font-size: 8.5px;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 3px;
            }
            .employee-overview-summary strong {
                color: #0f172a;
                font-size: 13px;
                font-weight: 800;
            }
            .employee-overview-table th,
            .employee-overview-table td {
                border: 1px solid #dbe3ef !important;
                font-size: 8.8px;
                padding: 3px 4px !important;
                vertical-align: top;
            }
            .employee-overview-table th {
                background: #f1f5f9;
                color: #334155;
                font-weight: 800;
            }
            .employee-overview-signatures {
                display: grid;
                gap: 8px;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .employee-overview-footer {
                border-top: 2px solid #0f766e;
                color: #64748b;
                font-size: 9px;
                font-weight: 800;
                margin-top: 10px;
                padding-top: 7px;
                text-align: center;
                text-transform: uppercase;
            }
            @media (max-width: 575.98px) {
                .employee-360-hero {
                    grid-template-columns: 1fr;
                    text-align: center;
                }
                .employee-360-title {
                    justify-content: center;
                }
                .employee-complete-list,
                .employee-complete-signatures,
                .employee-warning-signatures,
                .employee-contract-signatures,
                .employee-contract-meta {
                    grid-template-columns: 1fr;
                }
                .employee-complete-field {
                    grid-template-columns: 1fr;
                }
                .employee-complete-field span {
                    border-bottom: 1px solid #d7dee8;
                    border-right: 0;
                }
                .employee-complete-meta {
                    grid-template-columns: 1fr;
                }
            }
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 0;
                }
                html,
                body {
                    background: #fff !important;
                    height: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                body * {
                    visibility: hidden !important;
                }
                #complete-form,
                #complete-form *,
                #staff-warning-form,
                #staff-warning-form *,
                #contract-form,
                #contract-form *,
                #employeeOverviewPrintRoot,
                #employeeOverviewPrintRoot *,
                #employeeCompletePrintRoot,
                #employeeCompletePrintRoot *,
                #employeeWarningPrintRoot,
                #employeeWarningPrintRoot *,
                #employeeContractPrintRoot,
                #employeeContractPrintRoot * {
                    visibility: visible !important;
                }
                body.printing-overview-form .main-wrapper,
                body.printing-complete-form .main-wrapper,
                body.printing-warning-form .main-wrapper,
                body.printing-contract-form .main-wrapper {
                    display: none !important;
                }
                body.printing-overview-form #employeeOverviewPrintRoot,
                body.printing-complete-form #employeeCompletePrintRoot,
                body.printing-warning-form #employeeWarningPrintRoot,
                body.printing-contract-form #employeeContractPrintRoot {
                    background: #ffffff !important;
                    display: block !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    position: static !important;
                    visibility: visible !important;
                    width: 100% !important;
                }
                .employee-360-hero,
                #preloader,
                .sidebar,
                .navbar,
                .footer,
                .page-content > :not(.employee-360),
                .employee-360 > .breadcrumb,
                .employee-360 > .alert,
                .employee-360 .card > .card-header,
                .employee-360 .tab-content > .tab-pane:not(#complete-form) {
                    display: none !important;
                }
                .employee-360,
                .main-wrapper,
                .page-wrapper,
                .page-content,
                .employee-360 .card,
                .employee-360 .card-body,
                .employee-360 .tab-content {
                    background: transparent !important;
                    border: 0 !important;
                    box-shadow: none !important;
                    display: block !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
                #complete-form {
                    background: transparent;
                    box-sizing: border-box;
                    display: block !important;
                    opacity: 1 !important;
                    margin: 0 !important;
                    min-height: auto !important;
                    padding: 0 !important;
                    position: static !important;
                    transform: none !important;
                    visibility: visible !important;
                    width: 100% !important;
                }
                #staff-warning-form {
                    background: transparent;
                    box-sizing: border-box;
                    display: block !important;
                    opacity: 1 !important;
                    margin: 0 !important;
                    min-height: auto !important;
                    padding: 0 !important;
                    position: static !important;
                    transform: none !important;
                    visibility: visible !important;
                    width: 100% !important;
                }
                #contract-form {
                    background: transparent;
                    box-sizing: border-box;
                    display: block !important;
                    opacity: 1 !important;
                    margin: 0 !important;
                    min-height: auto !important;
                    padding: 0 !important;
                    position: static !important;
                    transform: none !important;
                    visibility: visible !important;
                    width: 100% !important;
                }
                .employee-complete-toolbar,
                .employee-360-tabs {
                    display: none !important;
                }
                .employee-complete-paper {
                    background: #fff;
                    background-image:
                        linear-gradient(180deg, var(--employee-complete-accent-soft), transparent 170px),
                        linear-gradient(90deg, var(--employee-complete-accent-rail) 0, var(--employee-complete-accent-rail) 5px, transparent 5px);
                    border: 0;
                    border-radius: 0;
                    box-shadow: none;
                    font-size: 10px;
                    line-height: 1.35;
                    margin: 0 !important;
                    max-width: none;
                    min-height: auto;
                    padding: 0 6mm 5mm;
                    position: static !important;
                    width: 100%;
                }
                .employee-complete-logo {
                    height: 22mm;
                    width: 22mm;
                }
                .employee-complete-header::before {
                    height: 26mm;
                    width: 39mm;
                }
                .employee-complete-header {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
                .employee-complete-section {
                    page-break-inside: avoid;
                }
                .employee-complete-table th,
                .employee-complete-table td {
                    font-size: 9.5px;
                }
                .employee-warning-paper {
                    min-height: 277mm;
                    padding: 8mm 8mm 6mm;
                }
                .employee-contract-paper {
                    min-height: 277mm;
                    padding: 8mm 8mm 6mm;
                }
                .employee-overview-paper {
                    min-height: 277mm;
                    padding: 8mm 8mm 6mm;
                }
            }
        </style>

        <div class="employee-360-hero">
            <img class="rounded-circle" src="{{ $avatar }}" alt="{{ $employee->name }}">
            <div>
                <div class="employee-360-title">
                    <h4 class="mb-0">{{ $employee->english_name ?: $employee->name }}</h4>
                    <span class="badge bg-{{ $employee->is_active ? 'success' : 'secondary' }}">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <p class="text-muted mb-0">{{ $employee->employee_code ?: $employee->username }} · {{ $employee->post?->post_name ?: 'N/A' }} · {{ $employee->department?->dept_name ?: 'N/A' }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
                <ul class="nav nav-tabs employee-360-tabs" id="employee360Tabs" role="tablist">
                    @foreach($tabs as $tab => $label)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $tab }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#{{ $tab }}"
                                    type="button"
                                    role="tab">{{ $label }}</button>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="employee-360-grid">
                            @foreach($overviewMetrics as $item)
                                <div class="employee-360-metric">
                                    <small>{{ $item['label'] }}</small>
                                    <strong>{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                        @if($canViewDiscipline)
                            @include('admin.employees.profile.partials.overview-print')
                        @endif
                    </div>

                    <div class="tab-pane fade" id="complete-form" role="tabpanel">
                        @include('admin.employees.profile.partials.complete-form')
                    </div>

                    @if($canViewDocument)
                        <div class="tab-pane fade" id="contract-form" role="tabpanel">
                            @include('admin.employees.profile.partials.contract-form')
                        </div>
                    @endif

                    @if($canViewDiscipline)
                        <div class="tab-pane fade" id="discipline" role="tabpanel">
                            @include('admin.employees.profile.partials.discipline')
                        </div>

                        <div class="tab-pane fade" id="staff-warning-form" role="tabpanel">
                            @include('admin.employees.profile.partials.staff-warning-form')
                        </div>
                    @endif

                    <div class="tab-pane fade" id="personal" role="tabpanel">
                        @if($canViewDocument)
                            @include('admin.employees.profile.partials.documents')
                        @endif

                        <form method="post" action="{{ route('admin.employees.profile.update', $employee->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="employee-360-section">
                                <h6>Personal Information</h6>
                                <div class="row">
                                    @foreach([
                                        'national_id' => 'National ID',
                                        'nationality' => 'Nationality',
                                        'education_level' => 'Education Level',
                                        'telegram' => 'Telegram',
                                        'emergency_contact_name' => 'Emergency Contact',
                                        'emergency_contact_relationship' => 'Relationship',
                                        'emergency_contact_phone' => 'Emergency Phone',
                                    ] as $field => $label)
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="form-label">{{ $label }}</label>
                                            <input class="form-control" name="{{ $field }}" value="{{ old($field, $profile->{$field}) }}">
                                        </div>
                                    @endforeach
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Address</label>
                                        <textarea class="form-control" name="current_address" rows="3">{{ old('current_address', $profile->current_address ?: $employee->address) }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Permanent Address</label>
                                        <textarea class="form-control" name="permanent_address" rows="3">{{ old('permanent_address', $profile->permanent_address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            @include('admin.employees.profile.partials.profile-employment-salary-fields')
                            @can('employee.profile.edit')
                                <button class="btn btn-primary">Save Profile</button>
                            @endcan
                        </form>
                    </div>

                    @if($canViewEmployment)
                        <div class="tab-pane fade" id="employment" role="tabpanel">
                            @include('admin.employees.profile.partials.employment')
                        </div>
                    @endif

                    @if($canViewSalary)
                        <div class="tab-pane fade" id="salary" role="tabpanel">
                            @include('admin.employees.profile.partials.salary')
                        </div>
                    @endif

                    @if($canViewInterview)
                        <div class="tab-pane fade" id="interview" role="tabpanel">
                            @include('admin.employees.profile.partials.interview')
                        </div>
                    @endif

                    @if($canViewKpi)
                        <div class="tab-pane fade" id="kpi" role="tabpanel">
                            @include('admin.employees.profile.partials.kpi')
                        </div>
                    @endif

                    @if($canViewPerformance)
                        <div class="tab-pane fade" id="evaluation" role="tabpanel">
                            @include('admin.employees.profile.partials.evaluation')
                        </div>
                    @endif

                    <div class="tab-pane fade" id="attendance" role="tabpanel">
                        <div class="employee-360-grid">
                            @foreach($attendanceSummary as $label => $value)
                                @if(!in_array($label, ['from', 'to'], true))
                                    <div class="employee-360-metric">
                                        <small>{{ ucwords(str_replace('_', ' ', $label)) }}</small>
                                        <strong>{{ $value }}</strong>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if($canViewTraining)
                        <div class="tab-pane fade" id="training" role="tabpanel">
                            @include('admin.employees.profile.partials.training')
                        </div>
                    @endif

                    @if($canViewReward)
                        <div class="tab-pane fade" id="rewards" role="tabpanel">
                            @include('admin.employees.profile.partials.rewards')
                        </div>
                    @endif

                    @if($canViewGoal)
                        <div class="tab-pane fade" id="goals" role="tabpanel">
                            @include('admin.employees.profile.partials.goals')
                        </div>
                    @endif

                    @if($canViewAudit)
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm employee-360-table">
                                    <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Record</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($auditLogs as $log)
                                        <tr>
                                            <td>{{ $log->created_at }}</td>
                                            <td>{{ ucfirst($log->module) }}</td>
                                            <td>{{ ucfirst($log->action) }}</td>
                                            <td>{{ $log->record_id }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">No records found</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        function cleanupEmployeeCompletePrintRoot() {
            document.body.classList.remove('printing-complete-form');
            document.body.classList.remove('printing-overview-form');
            document.body.classList.remove('printing-warning-form');
            document.body.classList.remove('printing-contract-form');
            document.getElementById('employeeOverviewPrintRoot')?.remove();
            document.getElementById('employeeCompletePrintRoot')?.remove();
            document.getElementById('employeeWarningPrintRoot')?.remove();
            document.getElementById('employeeContractPrintRoot')?.remove();
        }

        const employeeProfilePrintPermissions = {
            complete: @can('employee.complete_form.print') true @else false @endcan,
            overview: @can('employee.warning_overview.print') true @else false @endcan,
            warning: @can('employee.warning_form.print') true @else false @endcan,
            contract: @can('employee.contract_form.print') true @else false @endcan,
        };

        function printEmployeeProfilePaper(sourceSelector, rootId, bodyClass, printType) {
            if (!employeeProfilePrintPermissions[printType]) {
                return;
            }

            cleanupEmployeeCompletePrintRoot();

            const paper = document.querySelector(sourceSelector);
            if (!paper) {
                window.print();
                return;
            }

            const printRoot = document.createElement('div');
            printRoot.id = rootId;
            printRoot.appendChild(paper.cloneNode(true));
            document.body.appendChild(printRoot);
            document.body.classList.add(bodyClass);

            window.setTimeout(function () {
                window.print();
            }, 50);
        }

        function printEmployeeCompleteForm() {
            printEmployeeProfilePaper('#complete-form .employee-complete-paper', 'employeeCompletePrintRoot', 'printing-complete-form', 'complete');
        }

        function printOverviewForm() {
            printEmployeeProfilePaper('#overview .employee-overview-paper', 'employeeOverviewPrintRoot', 'printing-overview-form', 'overview');
        }

        function printStaffWarningForm() {
            printEmployeeProfilePaper('#staff-warning-form .employee-warning-paper', 'employeeWarningPrintRoot', 'printing-warning-form', 'warning');
        }

        function printContractForm() {
            printEmployeeProfilePaper('#contract-form .employee-contract-paper', 'employeeContractPrintRoot', 'printing-contract-form', 'contract');
        }

        window.addEventListener('afterprint', cleanupEmployeeCompletePrintRoot);

        const profileParams = new URLSearchParams(window.location.search);
        const requestedTab = profileParams.get('tab') === 'documents' ? 'personal' : profileParams.get('tab');
        if (requestedTab) {
            const tabButton = document.querySelector(`[data-bs-target="#${CSS.escape(requestedTab)}"]`);
            if (tabButton && window.bootstrap?.Tab) {
                bootstrap.Tab.getOrCreateInstance(tabButton).show();
            }
        }

        @if(old('complete_profile_modal'))
            const completeTabButton = document.querySelector('[data-bs-target="#complete-form"]');
            if (completeTabButton && window.bootstrap?.Tab) {
                bootstrap.Tab.getOrCreateInstance(completeTabButton).show();
            }
            const completeFormUpdateModal = document.getElementById('completeFormUpdateModal');
            if (completeFormUpdateModal && window.bootstrap?.Modal) {
                bootstrap.Modal.getOrCreateInstance(completeFormUpdateModal).show();
            }
        @endif

        document.querySelectorAll('[data-review-score]').forEach((input) => {
            input.addEventListener('input', function () {
                const max = Number(this.dataset.maxScore || 0);
                if (Number(this.value) > max) {
                    this.value = max;
                }
            });
        });
    </script>
@endsection
