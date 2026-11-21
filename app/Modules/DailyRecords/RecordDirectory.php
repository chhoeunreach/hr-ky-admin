<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RecordDirectory
{
    public function __construct(private RecordAccess $access) {}

    public function employees(Admin|User $actor, ?string $search = null, bool $active = true)
    {
        return $this->access->employees($actor, $active)
            ->when($search, fn ($query) => $query->where('u.name', 'like', '%'.$search.'%'))
            ->leftJoin('departments as d', 'd.id', '=', 'u.department_id')
            ->leftJoin('branches as b', 'b.id', '=', 'u.branch_id')
            ->orderBy('u.name')->orderBy('u.id')
            ->simplePaginate(30, ['u.id', 'u.name', 'd.dept_name as department', 'b.name as location']);
    }

    public function groups(Admin|User $actor, bool $active = true): array
    {
        $staff = $this->access->employees($actor, $active);
        return [
            'departments' => DB::table('departments')->whereIn('id', (clone $staff)->select('u.department_id'))
                ->orderBy('dept_name')->get(['id', 'dept_name as name']),
            'locations' => DB::table('branches')->whereIn('id', $staff->select('u.branch_id'))
                ->orderBy('name')->get(['id', 'name']),
        ];
    }
}
