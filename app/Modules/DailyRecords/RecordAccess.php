<?php

namespace App\Modules\DailyRecords;

use App\Helpers\AppHelper;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RecordAccess
{
    public const MANAGE = 'daily_records.manage';
    public const SUBMIT_REPORT = 'daily_records.submit_report';
    public const SENSITIVE = 'daily_records.sensitive';
    public const CATEGORIES = 'daily_records.categories';

    public function manages(Admin|User $actor): bool
    {
        return $this->allows($actor, self::MANAGE);
    }

    public function reporting(User $actor): array
    {
        return [
            'can_submit_report' => $this->canSubmitReports($actor),
            'reporting_today' => RecordTime::day(RecordTime::now()),
        ];
    }

    public function requireReportSubmission(User $actor, string $date, string $now): void
    {
        abort_unless($this->canSubmitReports($actor), 403, 'Your role cannot submit daily reports.');
        abort_unless($date === RecordTime::day($now), 422, 'Daily reports can only be saved or submitted on the reporting date (Phnom Penh time).');
    }

    private function canSubmitReports(User $actor): bool
    {
        return (bool) $actor->is_active && $this->hasRolePermission($actor, self::SUBMIT_REPORT);
    }

    public function allows(Admin|User $actor, string $ability): bool
    {
        return (bool) $actor->is_active && ($this->administrator($actor) ||
            $this->hasRolePermission($actor, $ability));
    }

    private function hasRolePermission(Admin|User $actor, string $ability): bool
    {
        return $actor->company_id && DB::table('permission_roles as pr')
            ->join('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->where('pr.role_id', $actor->role_id)->where('p.permission_key', $ability)->exists();
    }

    public function employees(Admin|User $actor, bool $active = false): Builder
    {
        abort_unless($this->manages($actor), 403);

        $employees = DB::table('users as u')->whereNull('u.deleted_at')->whereNotNull('u.company_id');
        if ($active) {
            $employees->where('u.is_active', true);
        }
        if ($actor instanceof Admin) {
            return $employees;
        }

        $employees->where('u.company_id', $actor->company_id)->where('u.id', '!=', $actor->id);
        if ($this->administrator($actor)) {
            return $employees;
        }

        return $employees->whereIn('u.branch_id', AppHelper::accessibleBranchIds($actor))
            ->where(fn (Builder $staff) => $staff->where('u.supervisor_id', $actor->id)
                ->orWhereExists(fn (Builder $department) => $department->selectRaw('1')->from('departments as managed')
                    ->whereColumn('managed.id', 'u.department_id')->where('managed.dept_head_id', $actor->id)));
    }

    public function constrain(Builder $query, Admin|User $actor, string $alias, bool $manager): Builder
    {
        if (!$manager) {
            return $query->where("$alias.employee_id", $actor->id)->where("$alias.company_id", $actor->company_id);
        }


        $scope = $this->employees($actor)->selectRaw('1')->whereColumn('u.id', "$alias.employee_id")
            ->whereColumn('u.company_id', "$alias.company_id");
        if (!$this->administrator($actor)) {
            $scope->whereColumn('u.branch_id', "$alias.branch_id")
                ->where(fn (Builder $dept) => $dept->whereColumn('u.department_id', "$alias.department_id")
                    ->orWhere(fn (Builder $empty) => $empty->whereNull('u.department_id')->whereNull("$alias.department_id")));
        }

        return $query->whereExists($scope);
    }

    public function type(Admin|User $actor): string
    {
        return $actor instanceof Admin ? 'admin' : 'employee';
    }

    private function administrator(Admin|User $actor): bool
    {
        return $actor instanceof Admin || in_array($actor->role?->slug, ['admin', 'super-admin', 'supper-admin'], true);
    }
}
