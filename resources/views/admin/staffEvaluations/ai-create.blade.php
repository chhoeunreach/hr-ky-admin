@extends('layouts.master')

@section('title', 'AI Staff Evaluation')
@section('action', 'AI Evaluation Form Generator')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Step 1 — Select Employee</h6>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.staff-evaluations.ai-create.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Employee</label>
                            <select class="form-control" name="employee_id" id="aiEmployeeSelect" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                            {{ ((int) ($selectedEmployeeId ?? 0) === (int) $employee->id) ? 'selected' : '' }}
                                            data-branch="{{ $employee->branch?->name }}"
                                            data-department="{{ $employee->department?->dept_name }}"
                                            data-position="{{ $employee->post?->post_name }}">
                                        {{ $employee->english_name ?: $employee->name }} — {{ $employee->employee_code ?: $employee->username }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Evaluation Period</label>
                            <input class="form-control" name="evaluation_period" value="{{ now()->format('F Y') }}" required>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Evaluator</label>
                            <input class="form-control" value="{{ auth()->user()?->name ?? auth('admin')->user()?->name ?? 'Admin' }}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Branch</label><input class="form-control" id="aiBranch" readonly></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Department</label><input class="form-control" id="aiDepartment" readonly></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Position</label><input class="form-control" id="aiPosition" readonly></div>
                    </div>
                    <button class="btn btn-primary">Continue</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        const select = document.getElementById('aiEmployeeSelect');
        const syncEmployeeInfo = () => {
            const option = select.options[select.selectedIndex];
            document.getElementById('aiBranch').value = option?.dataset.branch || '';
            document.getElementById('aiDepartment').value = option?.dataset.department || '';
            document.getElementById('aiPosition').value = option?.dataset.position || '';
        };
        select.addEventListener('change', syncEmployeeInfo);
        syncEmployeeInfo();
    </script>
@endsection
