<div data-salary-history-content>
    @php
        $canManageHistory = request()->boolean('salary_history_modal') && (auth('admin')->check()
            || \Illuminate\Support\Facades\Gate::any(['employee.salary.history.edit', 'employee.salary.history.delete']));
    @endphp
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div><strong>{{ $employeeDetail->name }}</strong><p class="text-muted small mb-0">{{ __('index.salary_review_help') }}</p></div>
        <span class="badge bg-light text-secondary border">{{ count($salaryReviseLists) }} {{ __('index.entries') }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle salary-review-table mb-0">
            <thead><tr>
                <th>{{ __('index.salary_revised_on') }}</th>
                <th>{{ __('index.applicable_from') }}</th>
                <th class="text-end">{{ __('index.old_salary') }} ({{ $currency }})</th>
                <th class="text-end">{{ __('index.new_salary') }} ({{ $currency }})</th>
                <th class="text-end">{{ __('index.increment_value') }} ({{ $currency }})</th>
                <th class="text-end">{{ __('index.increment_by') }}</th>
                <th>{{ __('index.remark') }}</th>
                <th>{{ __('index.link_created_by') }}</th>
                @if($canManageHistory)<th>{{ __('index.action') }}</th>@endif
            </tr></thead>
            <tbody>
                @forelse($salaryReviseLists as $record)
                    <tr>
                        <td>{{ $record->salary_revised_on ? \App\Helpers\AppHelper::timeLeaverequestDate($record->salary_revised_on) : '—' }}</td>
                        <td>{{ $record->date_from ? \App\Helpers\AppHelper::timeLeaverequestDate($record->date_from) : '—' }}</td>
                        <td class="text-end">{{ number_format((float) $record->base_salary, 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format((float) $record->revised_salary, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $record->increment_amount, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $record->increment_percent, 2) }}%</td>
                        <td>
                            @if($record->remark)
                                <details><summary>{{ __('index.view_details') }}</summary><p class="salary-review-remark mb-0 mt-2">{{ $record->remark }}</p></details>
                            @else — @endif
                        </td>
                        <td>{{ $record->created_by_name ?: $record->createdBy?->name ?: '—' }}</td>
                        @if($canManageHistory)
                            <td><div class="d-flex gap-1">
                                @can('employee.salary.history.edit')
                                    <a class="btn btn-outline-primary btn-sm" data-salary-history-panel href="{{ route('admin.employee-salaries.salary-revise-history.edit', [$employeeDetail->id, $record->id]) }}">{{ __('index.edit') }}</a>
                                @endcan
                                @can('employee.salary.history.delete')
                                    <a class="btn btn-outline-danger btn-sm" data-salary-history-panel href="{{ route('admin.employee-salaries.salary-revise-history.confirm-delete', [$employeeDetail->id, $record->id]) }}">{{ __('index.delete') }}</a>
                                @endcan
                            </div></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canManageHistory ? 9 : 8 }}" class="text-center text-muted py-5">{{ __('index.no_records_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
