@extends('layouts.master')

@section('title', __('index.edit_salary_history'))

@section('main-content')
    @php($editableAmounts = in_array($salary->approval_status, ['draft', 'pending'], true))
    <section class="content">
        @include('admin.section.flash_message')
        <h5 class="mb-3">{{ __('index.edit_salary_history') }} · {{ $employee->name }}</h5>
        @if(!$editableAmounts)
            <p class="text-muted">{{ __('index.salary_history_applied_help') }}</p>
        @endif
        <form method="post" action="{{ route('admin.employees.profile.salary.update', [$employee->id, $salary->id]) }}">
            @csrf
            @method('PUT')
            @if(request('employee_modal') === '1')
                <input type="hidden" name="employee_modal" value="1">
            @endif
            <div class="row g-3">
                @foreach([
                    'effective_date' => [__('index.effective_date'), 'date'],
                    'old_base_salary' => [__('index.old_base_salary'), 'number'],
                    'new_base_salary' => [__('index.new_base_salary'), 'number'],
                    'allowance_before' => [__('index.allowance_before'), 'number'],
                    'allowance_after' => [__('index.allowance_after'), 'number'],
                ] as $field => [$label, $type])
                    <div class="col-md-6">
                        <label for="salaryHistory_{{ $field }}" class="form-label">{{ $label }}</label>
                        <input id="salaryHistory_{{ $field }}" class="form-control" name="{{ $field }}" type="{{ $type }}"
                               value="{{ old($field, $field === 'effective_date' ? $salary->effective_date?->format('Y-m-d') : $salary->$field) }}"
                               @if($type === 'number') min="0" max="99999999.99" step="0.01" @endif
                               @disabled(!$editableAmounts) @required($editableAmounts && in_array($field, ['effective_date', 'old_base_salary', 'new_base_salary']))>
                    </div>
                @endforeach
                <div class="col-12">
                    <label for="salaryHistoryReason" class="form-label">{{ __('index.reason') }}</label>
                    <textarea id="salaryHistoryReason" name="reason" class="form-control" rows="3" maxlength="2000">{{ old('reason', $salary->reason) }}</textarea>
                </div>
                <div class="col-12">
                    <label for="salaryHistoryNote" class="form-label">{{ __('index.notes') }}</label>
                    <textarea id="salaryHistoryNote" name="note" class="form-control" rows="3" maxlength="5000">{{ old('note', $salary->note) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary" type="submit">{{ __('index.update') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'salary', ... (request('employee_modal') === '1' ? ['employee_modal' => '1'] : [])]) }}">{{ __('index.cancel') }}</a>
            </div>
        </form>
    </section>
@endsection
