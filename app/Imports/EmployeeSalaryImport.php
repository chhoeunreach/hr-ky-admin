<?php

namespace App\Imports;

use App\Enum\EmployeeBasicSalaryTypeEnum;
use App\Models\EmployeeSalary;
use App\Models\User;
use Exception;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeSalaryImport implements ToModel, WithHeadingRow
{
    use Importable;

    /**
     * @throws Exception
     */
    public function model(array $row): ?EmployeeSalary
    {
        $employee = $this->findEmployee($row);

        if (!$employee) {
            $identifier = $row['username'] ?? $row['employee_id'] ?? 'unknown';
            throw new Exception("Employee with username or ID {$identifier} not found.");
        }

        $salaryData = [
            'payroll_type' => $this->stringValue($row, 'payroll_type', 'monthly'),
            'payment_type' => $this->stringValue($row, 'payment_type', 'bank'),
            'annual_salary' => $this->numberValue($row, 'annual_salary'),
            'basic_salary_type' => $this->basicSalaryType($row['basic_salary_type'] ?? null),
            'basic_salary_value' => $this->numberValue($row, 'basic_salary_value'),
            'monthly_hours' => $this->numberValue($row, 'monthly_hours'),
            'monthly_basic_salary' => $this->numberValue($row, 'monthly_basic_salary'),
            'annual_basic_salary' => $this->numberValue($row, 'annual_basic_salary'),
            'monthly_fixed_allowance' => $this->numberValue($row, 'monthly_fixed_allowance'),
            'annual_fixed_allowance' => $this->numberValue($row, 'annual_fixed_allowance'),
            'salary_group_id' => $this->nullableNumberValue($row, 'salary_group_id'),
            'hour_rate' => $this->numberValue($row, 'hour_rate'),
            'weekly_hours' => $this->numberValue($row, 'weekly_hours'),
            'weekly_basic_salary' => $this->numberValue($row, 'weekly_basic_salary'),
            'weekly_fixed_allowance' => $this->numberValue($row, 'weekly_fixed_allowance'),
        ];

        EmployeeSalary::updateOrCreate(
            ['employee_id' => $employee->id],
            $salaryData
        );

        return null;
    }

    private function findEmployee(array $row): ?User
    {
        $username = $this->stringValue($row, 'username');
        $employeeId = $row['employee_id'] ?? null;

        if ($username !== null) {
            return User::where('username', $username)->first();
        }

        if ($employeeId !== null && $employeeId !== '') {
            return User::find($employeeId);
        }

        return null;
    }

    private function basicSalaryType(mixed $value): string
    {
        $type = strtolower(trim((string) ($value ?? '')));

        if ($type === 'percentage') {
            return EmployeeBasicSalaryTypeEnum::percent->value;
        }

        return in_array($type, [
            EmployeeBasicSalaryTypeEnum::fixed->value,
            EmployeeBasicSalaryTypeEnum::percent->value,
        ], true) ? $type : EmployeeBasicSalaryTypeEnum::fixed->value;
    }

    private function stringValue(array $row, string $key, ?string $default = null): ?string
    {
        if (!array_key_exists($key, $row) || $row[$key] === null) {
            return $default;
        }

        $value = trim((string) $row[$key]);

        return $value === '' ? $default : $value;
    }

    private function numberValue(array $row, string $key, float|int $default = 0): float|int
    {
        if (!array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
            return $default;
        }

        return is_numeric($row[$key]) ? $row[$key] + 0 : $default;
    }

    private function nullableNumberValue(array $row, string $key): int|null
    {
        if (!array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
            return null;
        }

        return is_numeric($row[$key]) ? (int) $row[$key] : null;
    }
}
