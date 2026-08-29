@php
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '________________';
    $valueOrLine = fn ($value) => filled($value) ? $value : '________________';
    $field = function ($label, $value) use ($valueOrLine) {
        return '<div class="employee-complete-field"><span>' . e($label) . '</span><strong>' . e($valueOrLine($value)) . '</strong></div>';
    };
    $latestReviewItems = $latestReview?->items ?? collect();
    $companyLogo = \App\Helpers\AppHelper::getCompanyLogo()
        ? asset(\App\Models\Company::UPLOAD_PATH . \App\Helpers\AppHelper::getCompanyLogo())
        : null;
    $branchLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : null;
    $formLogo = $branchLogo ?: ($companyLogo ?: asset('assets/images/img.png'));
@endphp

<div class="employee-complete-toolbar">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
        <i class="link-icon" data-feather="printer"></i> Print
    </button>
</div>

<div class="employee-complete-paper">
    <div class="employee-complete-header">
        <img class="employee-complete-logo" src="{{ $formLogo }}" alt="Kneayerng Phone Shop Logo">
        <div class="employee-complete-khmer-brand">ហាងទូរសព្ទដៃគ្នាយើង</div>
        <div class="employee-complete-brand">KNEAYERNG PHONE SHOP</div>
        <h4>ឯកសារប្រវត្តិបុគ្គលិក និងការវាយតម្លៃសមត្ថភាពពេញលេញ</h4>
        <div class="employee-complete-subtitle">COMPLETE EMPLOYEE PROFILE &amp; PERFORMANCE EVALUATION</div>
        <small class="employee-complete-confidential">ឯកសារផ្ទៃក្នុង - Confidential HR Record</small>
    </div>

    <div class="employee-complete-meta">
        <div>
            <small>Employee</small>
            <strong>{{ $employee->english_name ?: $employee->name }}</strong>
        </div>
        <div>
            <small>Code</small>
            <strong>{{ $employee->employee_code ?: $employee->username }}</strong>
        </div>
        <div>
            <small>Printed Date</small>
            <strong>{{ now()->format('Y-m-d') }}</strong>
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>1. ព័ត៌មានអត្តសញ្ញាណបុគ្គលិក (Employee Personal Information)</h6>
        <div class="employee-complete-list">
            {!! $field('លេខសម្គាល់បុគ្គលិក (Employee ID)', $employee->employee_code ?: $employee->username) !!}
            {!! $field('ឈ្មោះពេញ', $employee->name) !!}
            {!! $field('ឈ្មោះជាអង់គ្លេស', $employee->english_name) !!}
            {!! $field('ភេទ', $employee->gender) !!}
            {!! $field('ថ្ងៃខែឆ្នាំកំណើត', $formatDate($employee->dob)) !!}
            {!! $field('លេខអត្តសញ្ញាណប័ណ្ណ', $profile->national_id) !!}
            {!! $field('សញ្ជាតិ', $profile->nationality) !!}
            {!! $field('ស្ថានភាពគ្រួសារ', $employee->marital_status) !!}
            {!! $field('លេខទូរស័ព្ទ', $employee->phone) !!}
            {!! $field('Telegram/ទំនាក់ទំនង', $profile->telegram) !!}
            {!! $field('អ៊ីមែល', $employee->email) !!}
            {!! $field('កម្រិតការសិក្សា', $profile->education_level) !!}
            {!! $field('អាសយដ្ឋានបច្ចុប្បន្ន', $profile->current_address ?: $employee->address) !!}
            {!! $field('អាសយដ្ឋានអចិន្ត្រៃយ៍', $profile->permanent_address) !!}
            {!! $field('ឈ្មោះអ្នកទំនាក់ទំនងបន្ទាន់', $profile->emergency_contact_name) !!}
            {!! $field('ត្រូវជា', $profile->emergency_contact_relationship) !!}
            {!! $field('លេខទូរស័ព្ទបន្ទាន់', $profile->emergency_contact_phone) !!}
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>2. ព័ត៌មានការងារ (Employment Information)</h6>
        <div class="employee-complete-list">
            {!! $field('កាលបរិច្ឆេទចូលធ្វើការ', $formatDate($employee->joining_date)) !!}
            {!! $field('អាយុកាលការងារ', $summary['years_of_service'] ?? null) !!}
            {!! $field('តួនាទីបច្ចុប្បន្ន', $employee->post?->post_name) !!}
            {!! $field('ផ្នែក', $employee->department?->dept_name) !!}
            {!! $field('សាខា/ទីតាំងការងារ', $employee->branch?->name) !!}
            {!! $field('មេក្រុម / អ្នកគ្រប់គ្រងផ្ទាល់', $employee->supervisor?->name) !!}
            {!! $field('ប្រភេទការងារ', $employee->employment_type) !!}
            {!! $field('ស្ថានភាព', $profile->employment_status ?: ($employee->is_active ? 'active' : 'inactive')) !!}
            {!! $field('ម៉ោងការងារ', $employee->officeTime?->shift) !!}
            {!! $field('ថ្ងៃឈប់ប្រចាំសប្តាហ៍', $profile->weekly_day_off) !!}
            {!! $field('រយៈពេលសាកល្បង', $profile->probation_period) !!}
            {!! $field('ថ្ងៃបញ្ចប់សាកល្បង', $formatDate($profile->probation_end_date)) !!}
            {!! $field('កាលបរិច្ឆេទកិច្ចសន្យា', $formatDate($profile->contract_start_date)) !!}
            {!! $field('ថ្ងៃផុតកំណត់', $formatDate($profile->contract_end_date)) !!}
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>3. ប្រវត្តិជ្រើសរើស និងសម្ភាសន៍ (Recruitment &amp; Interview Record)</h6>
        <div class="table-responsive">
            <table class="table table-sm employee-complete-table">
                <thead><tr><th>កាលបរិច្ឆេទ</th><th>ដំណាក់កាល</th><th>អ្នកសម្ភាសន៍</th><th>តួនាទី</th><th>លទ្ធផល</th><th>មតិ</th></tr></thead>
                <tbody>
                @forelse($interviews as $record)
                    <tr><td>{{ $formatDate($record->interview_date) }}</td><td>{{ $record->interview_stage }}</td><td>{{ $record->interviewer_name }}</td><td>{{ $record->interviewer_position }}</td><td>{{ $record->result }}</td><td>{{ $record->comments }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center">No records found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($canViewSalary)
        <div class="employee-complete-section">
            <h6>4. ប្រាក់ខែ និងអត្ថប្រយោជន៍ (Salary &amp; Benefits)</h6>
            <div class="employee-complete-list">
                {!! $field('ប្រាក់ខែដំបូង', $profile->starting_salary) !!}
                {!! $field('ប្រាក់ខែគោលបច្ចុប្បន្ន', $profile->current_base_salary) !!}
                {!! $field('ប្រាក់ឧបត្ថម្ភ', $profile->allowances) !!}
                {!! $field('Commission/Incentive', $profile->commission) !!}
                {!! $field('ប្រាក់វត្តមាន', $profile->attendance_bonus) !!}
                {!! $field('ប្រាក់គោរពពេលវេលា', $profile->punctuality_bonus) !!}
                {!! $field('OT/ប្រាក់បន្ថែម', $profile->overtime) !!}
                {!! $field('វិធីបើកប្រាក់ខែ', $profile->payment_method) !!}
                {!! $field('ថ្ងៃបើកប្រាក់ខែ', $profile->salary_payment_date) !!}
                {!! $field('អត្ថប្រយោជន៍ផ្សេងៗ', $profile->other_benefits) !!}
            </div>
        </div>
    @endif

    @if($canViewSalary)
        <div class="employee-complete-section">
            <h6>5. ប្រវត្តិដំឡើង/កែប្រែប្រាក់ខែ (Salary Adjustment History)</h6>
            @include('admin.employees.profile.partials.complete-table', ['records' => $salaryHistory, 'columns' => ['effective_date' => 'ថ្ងៃខែឆ្នាំ', 'old_base_salary' => 'ប្រាក់ខែចាស់', 'increase_amount' => 'ចំនួនដំឡើង', 'new_base_salary' => 'ប្រាក់ខែថ្មី', 'reason' => 'មូលហេតុ', 'approval_status' => 'ស្ថានភាព', 'note' => 'សម្គាល់']])
        </div>
    @endif

    <div class="employee-complete-section">
        <h6>6. ប្រវត្តិប្តូរតួនាទី/ផ្នែក/សាខា (Position &amp; Transfer History)</h6>
        @include('admin.employees.profile.partials.complete-table', ['records' => $employmentHistory, 'columns' => ['effective_date' => 'កាលបរិច្ឆេទ', 'change_type' => 'ប្រភេទ', 'reason' => 'មូលហេតុ', 'note' => 'សម្គាល់']])
    </div>

    <div class="employee-complete-section">
        <h6>7. ភារកិច្ចការងារបច្ចុប្បន្ន (Current Job Responsibilities)</h6>
        @include('admin.employees.profile.partials.complete-table', ['records' => $responsibilities, 'columns' => ['title' => 'ភារកិច្ច', 'kpi_target' => 'KPI/ស្តង់ដារ', 'status' => 'ស្ថានភាព']])
    </div>

    <div class="employee-complete-section">
        <h6>8. វាយតម្លៃសមត្ថភាព និងលទ្ធផលការងារ (Performance Evaluation - 100 Points)</h6>
        <div class="table-responsive">
            <table class="table table-sm employee-complete-table">
                <thead><tr><th>ល.រ</th><th>លក្ខណៈវាយតម្លៃ</th><th>ស្តង់ដារ</th><th>ពិន្ទុអតិបរមា</th><th>ពិន្ទុទទួលបាន</th><th>មតិ</th></tr></thead>
                <tbody>
                @forelse($latestReviewItems as $item)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $item->criteria }}</td><td>{{ $item->description }}</td><td>{{ $item->max_score }}</td><td>{{ $item->score }}</td><td>{{ $item->comment }}</td></tr>
                @empty
                    @foreach($defaultItems as [$criteria, $description, $max])
                        <tr><td>{{ $loop->iteration }}</td><td>{{ $criteria }}</td><td>{{ $description }}</td><td>{{ $max }}</td><td>_______</td><td></td></tr>
                    @endforeach
                @endforelse
                <tr><th colspan="3">សរុប</th><th>100</th><th>{{ $latestReview?->total_score ?? '_______' }}</th><th>{{ $latestReview?->grade }}</th></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>9. ប្រវត្តិវាយតម្លៃការងារ (Performance Review History)</h6>
        @include('admin.employees.profile.partials.complete-table', ['records' => $reviews, 'columns' => ['review_date' => 'រយៈពេល', 'total_score' => 'ពិន្ទុ', 'grade' => 'កម្រិត', 'strengths' => 'ចំណុចខ្លាំង', 'areas_for_improvement' => 'ចំណុចកែលម្អ', 'final_recommendation' => 'អនុសាសន៍']])
    </div>

    <div class="employee-complete-section">
        <h6>10. ប្រវត្តិបណ្តុះបណ្តាល (Training &amp; Development History)</h6>
        @include('admin.employees.profile.partials.complete-table', ['records' => $training, 'columns' => ['training_date' => 'កាលបរិច្ឆេទ', 'training_title' => 'វគ្គ/ប្រធានបទ', 'trainer_name' => 'អ្នកបណ្តុះបណ្តាល', 'objective' => 'គោលបំណង', 'result' => 'លទ្ធផល', 'note' => 'សម្គាល់']])
    </div>

    <div class="employee-complete-section">
        <h6>11. ការសរសើរ រង្វាន់ និងការទទួលស្គាល់ (Recognition &amp; Rewards)</h6>
        @include('admin.employees.profile.partials.complete-table', ['records' => $rewards, 'columns' => ['reward_date' => 'កាលបរិច្ឆេទ', 'reward_type' => 'ប្រភេទ', 'title' => 'មូលហេតុ/សមិទ្ធផល', 'reward_amount' => 'រង្វាន់', 'description' => 'សម្គាល់']])
    </div>

    <div class="employee-complete-section">
        <h6>12. ប្រវត្តិវិន័យ/ការព្រមាន (Disciplinary &amp; Warning Record)</h6>
        @include('admin.employees.profile.partials.complete-table', ['records' => $discipline, 'columns' => ['incident_date' => 'កាលបរិច្ឆេទ', 'record_type' => 'ប្រភេទ', 'title' => 'បញ្ហា', 'action_taken' => 'វិធានការ', 'status' => 'ស្ថានភាព']])
    </div>

    <div class="employee-complete-section">
        <h6>13. វត្តមាន និងការឈប់សម្រាក (Attendance &amp; Leave Summary)</h6>
        <div class="employee-complete-list">
            {!! $field('រយៈពេល', ($attendanceSummary['from'] ?? '') . ' - ' . ($attendanceSummary['to'] ?? '')) !!}
            {!! $field('ថ្ងៃធ្វើការសរុប', $attendanceSummary['working_days'] ?? null) !!}
            {!! $field('អវត្តមាន', $attendanceSummary['absent_days'] ?? null) !!}
            {!! $field('មកយឺត', $attendanceSummary['late_count'] ?? null) !!}
            {!! $field('ចេញមុន', $attendanceSummary['early_leave_count'] ?? null) !!}
            {!! $field('ឈប់មានច្បាប់', $attendanceSummary['approved_leave_days'] ?? null) !!}
            {!! $field('ឈប់គ្មានច្បាប់', $attendanceSummary['unapproved_leave_days'] ?? null) !!}
            {!! $field('OT', $attendanceSummary['overtime_hours'] ?? null) !!}
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>14. ចំណុចខ្លាំង / ចំណុចត្រូវកែលម្អ / គោលដៅ</h6>
        <div class="employee-complete-list">
            {!! $field('ចំណុចខ្លាំង (Strengths)', $latestReview?->strengths) !!}
            {!! $field('ចំណុចត្រូវកែលម្អ', $latestReview?->areas_for_improvement) !!}
            {!! $field('គោលដៅរយៈពេលបន្ទាប់', $goals->pluck('title')->join(', ')) !!}
            {!! $field('ការគាំទ្រដែលត្រូវការ', $improvementPlans->pluck('support_required')->filter()->join(', ')) !!}
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>15. សេចក្តីសម្រេច និងអនុសាសន៍ (Final Decision)</h6>
        <div class="employee-complete-list">
            {!! $field('សេចក្តីសម្រេច', $latestReview?->final_recommendation) !!}
            {!! $field('មូលហេតុ/អនុសាសន៍', $latestReview?->manager_comment) !!}
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>16. ការទទួលស្គាល់ និងហត្ថលេខា (Acknowledgement &amp; Approval)</h6>
        <div class="employee-complete-signatures">
            @foreach(['បុគ្គលិក', 'អ្នកវាយតម្លៃ', 'ប្រធានផ្នែក', 'HR/អ្នកគ្រប់គ្រង'] as $signature)
                <div class="employee-complete-signature">
                    <strong>{{ $signature }}</strong><br><br>
                    ឈ្មោះ: __________________<br><br>
                    ហត្ថលេខា: _______________<br><br>
                    កាលបរិច្ឆេទ: ___/___/____
                </div>
            @endforeach
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>17. សម្រាប់ HR ប្រើប្រាស់ (HR Document Control)</h6>
        <div class="employee-complete-list">
            {!! $field('លេខឯកសារ', 'EMP-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT)) !!}
            {!! $field('Version', '1.0') !!}
            {!! $field('អ្នករៀបចំ', auth('admin')->user()?->name ?: auth()->user()?->name) !!}
            {!! $field('អ្នកត្រួតពិនិត្យ', '') !!}
            {!! $field('ថ្ងៃ Update ចុងក្រោយ', $formatDate($profile->updated_at ?: $employee->updated_at)) !!}
            {!! $field('ថ្ងៃវាយតម្លៃបន្ទាប់', $formatDate($latestReview?->next_review_date)) !!}
            {!! $field('ទីតាំងរក្សាទុកឯកសារ', '') !!}
            {!! $field('ស្ថានភាពឯកសារ', 'Active') !!}
        </div>
    </div>
</div>
