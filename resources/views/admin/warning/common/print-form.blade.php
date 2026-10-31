@php
    $recipients = $warningDetail->warningEmployee;
    $warningNumber = $warningDetail->warning_number;
    $branchLogo = $warningDetail->branch?->logo ? asset(\App\Models\Branch::UPLOAD_PATH . $warningDetail->branch->logo) : null;
@endphp
<div class="warning-print-documents">
    @foreach($recipients->isNotEmpty() ? $recipients : collect([null]) as $recipient)
        @php
            $employee = $recipient?->employee;
            $employeeReplies = $employee ? $warningDetail->warningReply->where('employee_id', $employee->id) : collect();
        @endphp
        <article class="warning-print-paper" data-warning-a4>
            <div data-warning-a4-content>
            <header class="warning-print-header">
                <div class="warning-print-brand">
                    @if($branchLogo)<img src="{{ $branchLogo }}" alt="{{ $warningDetail->branch?->name }}">@endif
                    <div><strong>{{ __('index.branch') }}: {{ $warningDetail->branch?->name ?: __('index.not_available') }}</strong><span>{{ __('index.hr_department') }}</span></div>
                </div>
                <div class="warning-print-reference"><strong>{{ __('index.warning_number') }}: {{ $warningNumber }}</strong><span>{{ \App\Helpers\AppHelper::formatDateForView($warningDetail->warning_date) }}</span></div>
            </header>
            <h1>{{ __('index.warning_print_title') }}</h1>
            <p class="warning-print-confidential">{{ __('index.warning_print_confidential') }}</p>
            <dl class="warning-print-employee">
                <div><dt>{{ __('index.employee_name') }}</dt><dd>{{ $employee?->name ?: __('index.not_available') }}</dd></div>
                <div><dt>{{ __('index.employee_id') }}</dt><dd>{{ $employee?->employee_code ?: $employee?->username ?: __('index.not_available') }}</dd></div>
                <div><dt>{{ __('index.department') }}</dt><dd>{{ $employee?->department?->dept_name ?: __('index.not_available') }}</dd></div>
                <div><dt>{{ __('index.position') }}</dt><dd>{{ $employee?->post?->post_name ?: __('index.not_available') }}</dd></div>
            </dl>
            <section class="warning-print-section">
                <h2>{{ __('index.subject') }}</h2>
                <p class="warning-print-subject">{{ $warningDetail->subject }}</p>
                <h2>{{ __('index.warning_print_details') }}</h2>
                <div class="warning-print-message">{{ $warningDetail->printable_message ?: __('index.not_available') }}</div>
            </section>
            <section class="warning-print-section">
                <h2>{{ __('index.warning_print_employee_explanation') }}</h2>
                <div class="warning-print-writing-space">
                    @foreach($employeeReplies as $reply)
                        <p>{{ html_entity_decode(strip_tags($reply->message ?? ''), ENT_QUOTES, 'UTF-8') }}</p>
                    @endforeach
                </div>
            </section>
            <section class="warning-print-section">
                <h2>{{ __('index.warning_print_corrective_action') }}</h2>
                <div class="warning-print-writing-space"></div>
            </section>
            <section class="warning-print-acknowledgement">
                <h2>{{ __('index.warning_print_acknowledgement') }}</h2>
                <p>{{ __('index.warning_print_acknowledgement_text') }}</p>
                <div class="warning-print-signatures">
                    @foreach(['employee', 'department_head', 'hr_department'] as $signer)
                        <div><strong>{{ __('index.' . $signer) }}</strong><div class="warning-print-signature-line"></div><span>{{ __('index.date') }}: __________________</span></div>
                    @endforeach
                </div>
            </section>
            <footer class="warning-print-footer">{{ __('index.prepared_by') }}: {{ $warningDetail->createdBy?->name ?: __('index.not_available') }} <span>{{ $warningNumber }}</span></footer>
            </div>
        </article>
    @endforeach
</div>
<style>
    .warning-print-documents { overflow-x: auto; }
    .warning-print-paper { box-sizing: border-box; background: #fff; color: #171717; width: 210mm; max-width: 100%; padding: 12mm; margin: 0 auto 24px; border: 1px solid #d5d5d5; font-size: 12px; line-height: 1.8; letter-spacing: 0; overflow-wrap: anywhere; }
    .warning-print-header { display: flex; justify-content: space-between; gap: 20px; padding-bottom: 16px; border-bottom: 2px solid #333; }
    .warning-print-brand { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .warning-print-brand img { width: 54px; height: 54px; object-fit: contain; flex-shrink: 0; }
    .warning-print-brand strong { display: block; font-size: 14px; }
    .warning-print-brand span, .warning-print-reference span { display: block; font-size: 11px; }
    .warning-print-reference { text-align: right; flex-shrink: 0; }
    .warning-print-paper h1 { text-align: center; font-size: 21px; margin: 20px 0 4px; line-height: 1.7; }
    .warning-print-confidential { text-align: center; font-size: 10px; margin-bottom: 18px; color: #555; }
    .warning-print-employee { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 24px; border-block: 1px solid #bbb; padding: 14px 0; margin-bottom: 18px; }
    .warning-print-employee dt { font-size: 10px; color: #555; }
    .warning-print-employee dd { margin: 0; font-weight: 600; }
    .warning-print-paper h2 { font-size: 12px; font-weight: 700; margin: 16px 0 8px; line-height: 1.7; }
    .warning-print-subject { font-weight: 600; }
    .warning-print-message, .warning-reply-message { white-space: pre-wrap; }
    .warning-print-message { min-height: 30mm; }
    .warning-print-writing-space { min-height: 20mm; border-bottom: 1px dotted #999; }
    .warning-print-acknowledgement { margin-top: 20px; break-inside: avoid; }
    .warning-print-acknowledgement > p { font-size: 11px; }
    .warning-print-signatures { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; text-align: center; margin-top: 18px; font-size: 10px; }
    .warning-print-signature-line { height: 18mm; border-bottom: 1px solid #777; margin-bottom: 8px; }
    .warning-print-footer { border-top: 1px solid #bbb; padding-top: 8px; margin-top: 20px; display: flex; justify-content: space-between; gap: 12px; font-size: 10px; }
    @media (max-width: 575px) {
        .warning-print-paper { padding: 18px; }
        .warning-print-header { flex-wrap: wrap; }
        .warning-print-signatures { gap: 8px; }
    }
    @media print {
        @page { size: A4 portrait; margin: 12mm; }
        .sidebar, .navbar, .footer, #preloader, .no-print { display: none !important; }
        .main-wrapper, .page-wrapper, .page-content, .content, .warning-print-documents { width: 100% !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; }
        .warning-print-paper { width: 100%; max-width: none; margin: 0; padding: 0; border: 0; break-after: page; }
        .warning-print-paper:last-child { break-after: auto; }
        .warning-print-header { flex-wrap: nowrap; break-inside: avoid; }
        .warning-print-employee, .warning-print-writing-space { break-inside: avoid; }
        .warning-print-paper h2 { break-after: avoid; }
        .warning-print-signatures { gap: 20px; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
<link rel="stylesheet" href="{{ asset('assets/css/staff-warning-a4.css') }}?v={{ filemtime(public_path('assets/css/staff-warning-a4.css')) }}">
<script defer src="{{ asset('assets/js/staff-warning-a4.js') }}?v={{ filemtime(public_path('assets/js/staff-warning-a4.js')) }}"></script>
