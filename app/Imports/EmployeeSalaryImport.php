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
            'payroll_type' => $this->stringValue($row, 'payroll_type', 'annual'),
            'payment_type' => $this->stringValue($row, 'payment_type', 'monthly'),
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

        if (!in_array($salaryData['payroll_type'], ['annual', 'hourly'], true)
            || !in_array($salaryData['payment_type'], ['monthly', 'weekly'], true)) {
            throw new Exception("Invalid payroll or payment type for {$employee->username}.");
        }

        $annualSalary = round((float) $salaryData['annual_salary'], 2);
        if ($annualSalary <= 0) {
            throw new Exception("Annual salary must be greater than zero for {$employee->username}.");
        }
        if ($salaryData['payroll_type'] === 'hourly') {
            $hours = $salaryData['payment_type'] === 'weekly' ? $salaryData['weekly_hours'] : $salaryData['monthly_hours'];
            if ($salaryData['hour_rate'] <= 0 || $hours <= 0) {
                throw new Exception("Hourly salary requires a positive rate and working hours for {$employee->username}.");
            }
            $annualSalary = round($salaryData['hour_rate'] * $hours * ($salaryData['payment_type'] === 'weekly' ? 52 : 12), 2);
        }
        $basicValue = (float) $salaryData['basic_salary_value'];
        if ($salaryData['basic_salary_type'] === EmployeeBasicSalaryTypeEnum::percent->value && $basicValue > 100
            || $salaryData['basic_salary_type'] === EmployeeBasicSalaryTypeEnum::fixed->value && $basicValue > $annualSalary / 12) {
            throw new Exception("Invalid basic salary for {$employee->username}.");
        }

        $monthlyBasic = $salaryData['basic_salary_type'] === EmployeeBasicSalaryTypeEnum::percent->value
            ? round($annualSalary / 12 * $basicValue / 100, 2)
            : round($basicValue, 2);
        $annualBasic = $salaryData['basic_salary_type'] === EmployeeBasicSalaryTypeEnum::percent->value && $basicValue == 100
            ? $annualSalary
            : round($monthlyBasic * 12, 2);
        $annualAllowance = round($annualSalary - $annualBasic, 2);
        $salaryData['annual_salary'] = $annualSalary;
        $salaryData['monthly_basic_salary'] = $monthlyBasic;
        $salaryData['annual_basic_salary'] = $annualBasic;
        $salaryData['weekly_basic_salary'] = round($annualBasic / 52, 2);
        $salaryData['monthly_fixed_allowance'] = round($annualAllowance / 12, 2);
        $salaryData['annual_fixed_allowance'] = $annualAllowance;
        $salaryData['weekly_fixed_allowance'] = round($annualAllowance / 52, 2);

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

        if ($type === '') {
            return EmployeeBasicSalaryTypeEnum::fixed->value;
        }
        if ($type === 'percentage') {
            return EmployeeBasicSalaryTypeEnum::percent->value;
        }

        if (!in_array($type, [
            EmployeeBasicSalaryTypeEnum::fixed->value,
            EmployeeBasicSalaryTypeEnum::percent->value,
        ], true)) {
            throw new Exception("Invalid basic salary type: {$type}.");
        }

        return $type;
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

        if (!is_numeric($row[$key]) || (float) $row[$key] < 0) {
            throw new Exception("Invalid {$key} value in salary import.");
        }

        return $row[$key] + 0;
    }

    private function nullableNumberValue(array $row, string $key): int|null
    {
        if (!array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
            return null;
        }

        if (!ctype_digit((string) $row[$key])) {
            throw new Exception("Invalid {$key} value in salary import.");
        }

        return (int) $row[$key];
    }
}
