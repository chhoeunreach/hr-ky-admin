<?php

namespace App\Imports;

use App\Enum\LeaveGenderEnum;
use App\Helpers\AppHelper;
use App\Models\Branch;
use App\Models\LeaveType;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeaveTypesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $companyId;
    private ?int $authBranchId;
    private int $inserted = 0;
    private int $updated = 0;

    public function __construct()
    {
        $this->companyId = AppHelper::getAuthUserCompanyId();
        $this->authBranchId = auth()->user()->branch_id ?? null;
    }

    /**
     * @throws Exception
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $row = collect($row);
            $line = $index + 2;
            $name = $this->stringValue($row, 'name');

            if (!$name) {
                throw new Exception("Row {$line}: name is required.");
            }

            $leaveType = $this->findExistingLeaveType($row, $name);
            $leaveTypeData = [
                'company_id' => $this->companyId,
                'name' => $name,
                'slug' => Str::slug($name),
                'branch_id' => $this->authBranchId ?: $this->resolveBranchId($row, $line, $leaveType),
                'gender' => $this->genderValue($row, $line, $leaveType),
                'leave_allocated' => $this->leaveAllocatedValue($row, $line, $leaveType),
                'is_active' => $this->booleanValue($row, 'is_active', $leaveType?->is_active ?? true),
            ];

            if ($leaveType) {
                $leaveType->update($leaveTypeData);
                $this->updated++;
                continue;
            }

            LeaveType::create($leaveTypeData);
            $this->inserted++;
        }
    }

    public function insertedCount(): int
    {
        return $this->inserted;
    }

    public function updatedCount(): int
    {
        return $this->updated;
    }

    private function findExistingLeaveType(Collection $row, string $name): ?LeaveType
    {
        $id = $this->numberValue($row, 'id');

        if ($id) {
            return LeaveType::where('company_id', $this->companyId)
                ->where('id', $id)
                ->first();
        }

        return LeaveType::where('company_id', $this->companyId)
            ->where('name', $name)
            ->first();
    }

    /**
     * @throws Exception
     */
    private function resolveBranchId(Collection $row, int $line, ?LeaveType $leaveType): int
    {
        $branchId = $this->numberValue($row, 'branch_id');

        if ($branchId) {
            $branchExists = Branch::where('company_id', $this->companyId)->where('id', $branchId)->exists();

            if (!$branchExists) {
                throw new Exception("Row {$line}: branch_id {$branchId} was not found.");
            }

            return $branchId;
        }

        $branchName = $this->stringValue($row, 'branch');

        if (!$branchName && $leaveType?->branch_id) {
            return $leaveType->branch_id;
        }

        if (!$branchName) {
            throw new Exception("Row {$line}: branch_id or branch is required.");
        }

        $branch = Branch::where('company_id', $this->companyId)
            ->where('name', $branchName)
            ->first();

        if (!$branch) {
            throw new Exception("Row {$line}: branch {$branchName} was not found.");
        }

        return $branch->id;
    }

    /**
     * @throws Exception
     */
    private function genderValue(Collection $row, int $line, ?LeaveType $leaveType): string
    {
        $gender = strtolower($this->stringValue($row, 'gender', $leaveType?->gender ?? LeaveGenderEnum::all->value));
        $allowed = array_column(LeaveGenderEnum::cases(), 'value');

        if (!in_array($gender, $allowed, true)) {
            throw new Exception("Row {$line}: gender must be one of " . implode(', ', $allowed) . '.');
        }

        return $gender;
    }

    /**
     * @throws Exception
     */
    private function leaveAllocatedValue(Collection $row, int $line, ?LeaveType $leaveType): int|null
    {
        if (!$row->has('is_paid') && !$row->has('leave_allocated') && $leaveType) {
            return $leaveType->leave_allocated;
        }

        $leaveAllocated = $this->numberValue($row, 'leave_allocated');
        $isPaid = $this->booleanValue($row, 'is_paid', $leaveAllocated !== null && $leaveAllocated > 0);

        if (!$isPaid) {
            return null;
        }

        if ($leaveAllocated === null || $leaveAllocated < 1) {
            throw new Exception("Row {$line}: leave_allocated must be at least 1 for paid leave.");
        }

        return $leaveAllocated;
    }

    private function stringValue(Collection $row, string $key, ?string $default = null): ?string
    {
        if (!$row->has($key) || $row->get($key) === null) {
            return $default;
        }

        $value = trim((string) $row->get($key));

        return $value === '' ? $default : $value;
    }

    private function numberValue(Collection $row, string $key): int|null
    {
        if (!$row->has($key) || $row->get($key) === null || $row->get($key) === '') {
            return null;
        }

        return is_numeric($row->get($key)) ? (int) $row->get($key) : null;
    }

    private function booleanValue(Collection $row, string $key, bool $default): bool
    {
        if (!$row->has($key) || $row->get($key) === null || $row->get($key) === '') {
            return $default;
        }

        $value = strtolower(trim((string) $row->get($key)));

        return in_array($value, ['1', 'true', 'yes', 'y', 'active'], true);
    }
}
