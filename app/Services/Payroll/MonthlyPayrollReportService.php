<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayslip;
use App\Models\SellOutReport;
use App\Models\MonthlyPayrollRecord;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class MonthlyPayrollReportService
{
    public function report(array $filters): array
    {
        $month = Carbon::createFromFormat('!Y-m', $filters['month']);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $branchId = !auth('admin')->check() && auth()->user()?->branch_id ? auth()->user()->branch_id : $filters['branch_id'];
        if (empty($filters['live'])) {
            $savedRecords = MonthlyPayrollRecord::where('month', $filters['month'])
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->orderBy('branch_id')->get();
            if ($savedRecords->isNotEmpty()) return $this->savedReport($savedRecords, $filters);
        }
        $query = EmployeePayslip::withoutGlobalScope('branch')->where('salary_cycle', 'monthly')
            ->whereBetween('salary_from', [$start->toDateString(), $end->toDateString()])
            ->whereHas('employee', function ($employee) use ($filters, $branchId) {
                $employee->withTrashed()
                    ->when($branchId, fn ($q, $id) => $q->where('branch_id', $id))
                    ->when($filters['department_id'], fn ($q, $id) => $q->where('department_id', $id))
                    ->when($filters['q'], fn ($q, $term) => $q->where(function ($search) use ($term) {
                        $search->where('name', 'like', '%'.$term.'%')->orWhere('username', 'like', '%'.$term.'%')
                            ->orWhere('employee_code', 'like', '%'.$term.'%');
                    }));
            })->when($filters['status'], fn ($q, $status) => $q->where('status', $status));

        $summary = ['records' => (clone $query)->count(), 'gross' => (clone $query)->sum('gross_salary'),
            'net' => (clone $query)->sum('net_salary'), 'bonus' => (clone $query)->sum('bonus')];
        $loans = app(MonthlyPayrollLoanService::class)->amounts($filters['month'], (clone $query)->distinct()->pluck('employee_id'));
        $summary['advance_salary'] = round(array_sum($loans), 2);
        $sales = SellOutReport::query()->whereIn('user_id', (clone $query)->select('employee_id'))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, service_type, SUM(commission) as commission_total')
            ->groupBy('user_id', 'service_type')->get();
        $commissionTypes = [];
        $commissions = [];
        foreach ($sales as $sale) {
            $type = trim((string) $sale->service_type) ?: SellOutReport::DEFAULT_SERVICE_TYPE;
            $commissionTypes[$type] = $type;
            $commissions[$sale->user_id][$type] = round(($commissions[$sale->user_id][$type] ?? 0) + (float) $sale->commission_total, 2);
        }
        ksort($commissionTypes);
        $summary['commission'] = array_sum(array_map('array_sum', $commissions));
        $query->with(['employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
            'payslipDetail.salaryComponent', 'additionalData.salaryComponent'])->orderBy('employee_id')->orderBy('id');
        $payslips = $filters['per_page'] === 'all' || $filters['export']
            ? $query->get() : $query->paginate((int) $filters['per_page'])->withQueryString();
        foreach ($payslips as $payslip) $payslip->setAttribute('monthly_loan_amount', $loans[$payslip->employee_id] ?? 0.0);

        return compact('month', 'payslips', 'summary', 'commissionTypes', 'commissions') + [
            'columns' => $this->columns(), 'isSavedReport' => false, 'savedRecords' => collect(),
        ];
    }

    public function savedReport($records, array $filters): array
    {
        $loanService = app(MonthlyPayrollLoanService::class);
        $rows = $records->flatMap(fn ($record) => ($record->options['loan_amount_source'] ?? null) === MonthlyPayrollLoanService::SOURCE
            ? $record->rows : $loanService->correctedRows($record->month, $record->rows))->filter(function ($row) use ($filters) {
            if (!empty($filters['department_id']) && ($row['employee']['department_id'] ?? null) != $filters['department_id']) return false;
            if (!empty($filters['status']) && ($row['attributes']['status'] ?? null) !== $filters['status']) return false;
            $term = trim((string) ($filters['q'] ?? ''));
            if ($term === '') return true;
            foreach (['name', 'english_name', 'username', 'employee_code'] as $key) {
                if (mb_stripos((string) ($row['employee'][$key] ?? ''), $term) !== false) return true;
            }
            return false;
        })->sortBy(fn ($row) => $row['attributes']['employee_id'])->values();
        $summary = ['records' => $rows->count(), 'gross' => 0, 'net' => 0, 'bonus' => 0, 'commission' => 0,
            'advance_salary' => $loanService->totalRows($rows)];
        foreach ($rows as $row) {
            foreach (['gross' => 'gross_salary', 'net' => 'net_salary', 'bonus' => 'bonus'] as $key => $field) {
                $summary[$key] += (float) ($row['values'][$field] ?? 0);
            }
            $summary['commission'] += (float) ($row['commission_total'] ?? 0);
        }
        $payslips = $rows->map(function ($row) {
            $payslip = (new EmployeePayslip())->forceFill($row['attributes']);
            $payslip->setRelation('employee', (new User())->forceFill($row['employee'] ?? []));
            $payslip->setAttribute('monthly_saved_values', $row['values']);
            return $payslip;
        });
        if (($filters['per_page'] ?? 'all') !== 'all' && empty($filters['export'])) {
            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = (int) $filters['per_page'];
            $payslips = new LengthAwarePaginator($payslips->forPage($page, $perPage)->values(), $payslips->count(), $perPage, $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]);
        }
        return [
            'month' => Carbon::createFromFormat('!Y-m', $filters['month']), 'payslips' => $payslips, 'summary' => $summary,
            // Keep the requested sheet layout consistent even for older snapshots.
            // Financial values still come exclusively from each saved record.
            'columns' => $this->columns(), 'commissions' => [], 'commissionTypes' => [],
            'savedRecords' => $records, 'isSavedReport' => true, 'savedCurrency' => $records->first()->currency,
        ];
    }

    public function yearly(array $filters, int $year): array
    {
        $branchId = !auth('admin')->check() && auth()->user()?->branch_id ? auth()->user()->branch_id : $filters['branch_id'];
        $records = MonthlyPayrollRecord::whereBetween('month', [$year.'-01', $year.'-12'])
            ->when($branchId, fn ($query, $id) => $query->where('branch_id', $id))->orderBy('month')->get();
        $months = collect(range(1, 12))->map(function ($number) use ($records, $filters, $year) {
            $month = sprintf('%04d-%02d', $year, $number);
            $saved = $records->where('month', $month)->values();
            $summary = $saved->isEmpty() ? ['records' => 0, 'gross' => 0, 'net' => 0, 'bonus' => 0, 'commission' => 0, 'advance_salary' => 0]
                : $this->savedReport($saved, array_replace($filters, ['month' => $month, 'per_page' => 'all']))['summary'];
            return compact('month', 'saved', 'summary');
        });
        $summary = [];
        foreach (['records', 'gross', 'net', 'bonus', 'commission', 'advance_salary'] as $key) {
            $sum = $months->sum(fn ($month) => $month['summary'][$key]);
            $summary[$key] = $key === 'records' ? (int) $sum : round($sum, 2);
        }
        return compact('months', 'summary');
    }

    public function componentTotal(EmployeePayslip $payslip, string $type): float
    {
        return round($payslip->payslipDetail->concat($payslip->additionalData)
            ->filter(fn ($detail) => $detail->salaryComponent?->component_type === $type)->sum('amount'), 2);
    }

    /** The requested sheet order is shared by screen, print, CSV and Excel. */
    public function columns(): array
    {
        $definitions = [
            'number' => ['លរ', .45, 'number', 'text'],
            'employee_code' => ['លេខ', .9, 'monthly-code', 'text'],
            'photo' => ['រូប', .75, 'employee-photo', 'photo'],
            'employee' => ['ឈ្មោះខ្មែរ', 1.8, 'monthly-name employee-name', 'text'],
            'basic_salary' => ['ប្រាក់ខែគោល', 1.1, 'money', 'money'],
            'increase' => ['តម្លើង', .8, 'money earning', 'money'],
            'branch' => ['ទីតាំង', 1.1, '', 'text'],
            'present_days' => ['ធ្វើការ', .7, 'day-count', 'text'],
            'early' => ['មកលឿន', .8, 'money earning', 'money'],
            'late' => ['មកយឺត', .8, 'money deduction', 'money'],
            'early_out' => ['ចេញមុន', .8, 'money deduction', 'money'],
            'overtime' => ['ថែមម៉ោង', .9, 'money earning', 'money'],
            'department' => ['ផ្នែក', 1.1, '', 'text'],
            'rest' => ['សម្រាក', .7, 'day-count', 'text'],
            'penalty' => ['ពិន័យ', .8, 'money deduction', 'money'],
            'leave_days' => ['សុំច្បាប់', .7, 'day-count', 'text'],
            'pay' => ['ប្រាក់', .8, 'money', 'money'],
            'advance_salary' => ['ខ្ចីលុយ', .9, 'money deduction', 'money'],
            'deduction' => ['កាត់', .9, 'money deduction', 'money'],
            'attendance_bonus' => ['មិនឈប់', .9, 'money earning', 'money'],
            'sell' => ['លក់', .9, 'money earning', 'money'],
            'iron' => ['អ៊ុង', .9, 'money earning', 'money'],
            'material' => ['សម្ភារ', .9, 'money earning', 'money'],
            'repair' => ['fix', .9, 'money earning', 'money'],
            'post' => ['ផុស', .9, 'money earning', 'money'],
            'bonus' => ['លើកទឹកចិត្ត', 1, 'money earning', 'money'],
            'gross_salary' => ['ប្រាក់សរុប', 1.1, 'money', 'money'],
            'net_salary' => ['ប្រាក់ត្រូវបើក', 1.1, 'money monthly-net net-pay', 'money'],
        ];
        return array_map(fn ($definition) => array_combine(['label', 'width', 'class', 'format'], $definition), $definitions);
    }

    private function namedComponent(EmployeePayslip $payslip, array $names): ?float
    {
        $normalize = fn ($name) => mb_strtolower(trim((string) $name));
        $names = array_map($normalize, $names);
        $details = $payslip->payslipDetail->concat($payslip->additionalData)
            ->filter(fn ($detail) => $detail->salaryComponent && in_array($normalize($detail->salaryComponent->name), $names, true));
        return $details->isEmpty() ? null : round((float) $details->sum('amount'), 2);
    }

    public function rowValues(EmployeePayslip $payslip, array $commissions, int $number): array
    {
        if (is_array($payslip->getAttribute('monthly_saved_values'))) {
            $defaults = array_fill_keys(array_keys($this->columns()), null);
            return array_replace($defaults, array_intersect_key($payslip->getAttribute('monthly_saved_values'), $defaults), ['number' => $number]);
        }
        $employee = $payslip->employee;
        $values = [
            'number' => $number,
            'employee_code' => $employee?->employee_code ?: $employee?->username,
            'photo' => $employee?->avatar_url ?: asset('assets/images/img.png'),
            'employee' => $employee?->name,
            'branch' => $employee?->branch?->name,
            'department' => $employee?->department?->dept_name,
        ];
        foreach (['present_days', 'leave_days', 'overtime', 'bonus', 'gross_salary', 'net_salary'] as $key) {
            $values[$key] = is_numeric($payslip->$key) ? (float) $payslip->$key : null;
        }
        $values['advance_salary'] = (float) ($payslip->getAttribute('monthly_loan_amount') ?? 0);
        $values['rest'] = is_numeric($payslip->weekends) || is_numeric($payslip->holidays)
            ? (float) $payslip->weekends + (float) $payslip->holidays : null;
        // Only saved payslip components are used here. Current profile amounts must
        // not rewrite historical payroll, and missing amounts are not estimated.
        foreach ([
            'basic_salary' => ['ប្រាក់ខែគោល', 'Basic Salary', 'Base Salary'],
            'increase' => ['តម្លើង', 'Salary Increase'],
            'early' => ['មកលឿន', 'Early Arrival'],
            'late' => ['មកយឺត', 'Late Arrival'],
            'early_out' => ['ចេញមុន', 'Early Departure'],
            'penalty' => ['ពិន័យ', 'Penalty'],
            'pay' => ['ប្រាក់'],
            'deduction' => ['កាត់'],
            'attendance_bonus' => ['មិនឈប់', 'Attendance Bonus'],
            'post' => ['ផុស', 'Post', 'Posting'],
        ] as $key => $names) {
            $values[$key] = $this->namedComponent($payslip, $names);
        }
        foreach ([
            'sell' => ['លក់', 'sell', 'sale'],
            'iron' => ['អ៊ុង', 'អ៊ុត', 'iron', 'scots'],
            'material' => ['សម្ភារ', 'material', 'materials'],
            'repair' => ['fix', 'repair', 'ជួសជុល'],
        ] as $key => $types) {
            $values[$key] = null;
            foreach ($commissions as $type => $amount) {
                $type = mb_strtolower(trim((string) preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $type)));
                if (in_array($type, $types, true)) $values[$key] = round(($values[$key] ?? 0) + (float) $amount, 2);
            }
        }
        return $values;
    }

    public function exportTable(array $report): array
    {
        $columns = $this->columns();
        $headings = array_column($columns, 'label');
        $rows = $report['payslips']->values()->map(function ($payslip, $index) use ($report, $columns) {
            $values = $this->rowValues($payslip, $report['commissions'][$payslip->employee_id] ?? [], $index + 1);
            $row = [];
            foreach ($columns as $key => $column) {
                $value = $values[$key] ?? null;
                $row[] = $value === null ? '' : ($column['format'] === 'money' ? number_format((float) $value, 2, '.', '') : (string) $value);
            }
            return $row;
        })->all();
        return compact('headings', 'rows');
    }
}
