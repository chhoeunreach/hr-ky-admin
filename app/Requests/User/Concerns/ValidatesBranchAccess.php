<?php

namespace App\Requests\User\Concerns;

use App\Helpers\AppHelper;
use Illuminate\Validation\Rule;

trait ValidatesBranchAccess
{
    protected function prepareBranchAccess(): void
    {
        if (!$this->has('additional_branch_ids') && !$this->has('branch_access_present')) return;
        abort_unless(AppHelper::canManageEmployeeBranchAccess(), 403);
        if ($this->boolean('branch_access_present') && !$this->has('additional_branch_ids')) {
            $this->merge(['additional_branch_ids' => []]);
        }
    }

    protected function prepareHomeBranch(bool $updating = false): void
    {
        if (auth('admin')->check() || !auth()->check()) return;
        if (!AppHelper::canAccessAllBranches() && count(AppHelper::accessibleBranchIds()) <= 1) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
            return;
        }
        if ($this->filled('branch_id')) {
            abort_unless(AppHelper::canAccessBranch($this->input('branch_id')), 403);
        } elseif ($updating && $this->route('employee')) {
            $employee = \App\Models\User::findOrFail($this->route('employee'));
            $this->merge(['branch_id' => $employee->branch_id]);
        } else {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }

    protected function branchAccessRules(): array
    {
        return [
            'additional_branch_ids' => ['sometimes', 'array'],
            'additional_branch_ids.*' => ['required', 'integer', 'distinct',
                Rule::exists('branches', 'id')->where('company_id', AppHelper::getAuthUserCompanyId()),
            ],
        ];
    }
}
