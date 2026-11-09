<?php

namespace App\Services;

use App\Helpers\AppHelper;
use App\Models\Department;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DepartmentDeletionService
{
    private ?array $references = null;

    public function departments($id, bool $lock = false): Collection
    {
        $department = Department::where('company_id', AppHelper::getAuthUserCompanyId())->findOrFail($id);

        return Department::where('company_id', $department->company_id)
            ->where('dept_name', $department->dept_name)->with('branch:id,name')
            ->when($lock, fn ($query) => $query->lockForUpdate())->get();
    }

    public function preview(Collection $departments): array
    {
        $departmentIds = $departments->pluck('id')->all();
        $posts = Post::withoutGlobalScopes()->whereIn('dept_id', $departmentIds)
            ->orderBy('post_name')->get(['id', 'post_name', 'dept_id', 'is_active']);
        $postIds = $posts->pluck('id')->all();
        $assignedPositions = DB::table('users')->whereIn('post_id', $postIds)
            ->select('post_id')->selectRaw('COUNT(*) as assignments')->groupBy('post_id')->pluck('assignments', 'post_id');
        $branches = $departments->keyBy('id');
        $blockers = [];

        // Include inactive and soft-deleted employees because their assignments still exist.
        if (Schema::hasTable('users')) {
            $employees = DB::table('users')->where(function ($query) use ($departmentIds, $postIds) {
                $query->whereIn('department_id', $departmentIds)->orWhereIn('post_id', $postIds);
            })->count();
            if ($employees) {
                $blockers[] = ['label' => __('index.department_employee_links'), 'count' => $employees, 'can_unlink' => false];
            }
        }

        $tables = [];
        foreach ($this->references() as $reference) {
            $ids = $reference['target'] === 'departments' ? $departmentIds : $postIds;
            if ($ids) $tables[$reference['table']][] = [$reference['column'], $ids, $reference['nullable']];
        }
        foreach ($tables as $table => $columns) {
            $count = DB::table($table)->where(function ($query) use ($columns) {
                foreach ($columns as [$column, $ids]) $query->orWhereIn($column, $ids);
            })->count();
            if ($count) {
                $requiredColumns = array_filter($columns, fn ($column) => !$column[2]);
                $requiredLinks = $requiredColumns && DB::table($table)->where(function ($query) use ($requiredColumns) {
                    foreach ($requiredColumns as [$column, $ids]) $query->orWhereIn($column, $ids);
                })->exists();
                $blockers[] = ['label' => Str::headline($table), 'count' => $count, 'can_unlink' => !$requiredLinks];
            }
        }

        return [
            'name' => $departments->first()->dept_name,
            'departments_count' => $departments->count(),
            'branches' => $departments->pluck('branch.name')->filter()->unique()->values()->all(),
            'positions' => $posts->map(fn ($post) => [
                'id' => $post->id,
                'name' => $post->post_name,
                'branch' => $branches->get($post->dept_id)?->branch?->name,
                'active' => (bool) $post->is_active,
                'assigned_employees' => (int) ($assignedPositions[$post->id] ?? 0),
                'assignment_url' => route('admin.departments.position-assignment', ['position' => $post->id]),
            ])->values()->all(),
            'positions_count' => $posts->count(),
            'blockers' => $blockers,
        ];
    }

    public function delete($id, bool $removePositions, bool $unlinkRecords = false): void
    {
        DB::transaction(function () use ($id, $removePositions, $unlinkRecords) {
            $departments = $this->departments($id, true);
            $preview = $this->preview($departments);
            if (collect($preview['blockers'])->contains(fn ($blocker) => !$unlinkRecords || !$blocker['can_unlink'])) {
                throw ValidationException::withMessages(['department' => __('index.department_delete_reassign')]);
            }
            if ($preview['positions_count'] && !$removePositions) {
                throw ValidationException::withMessages(['remove_positions' => __('index.department_delete_positions_required')]);
            }
            if ($unlinkRecords) {
                $departmentIds = $departments->pluck('id')->all();
                $postIds = array_column($preview['positions'], 'id');
                foreach ($this->references() as $reference) {
                    if (!$reference['nullable']) continue;
                    $ids = $reference['target'] === 'departments' ? $departmentIds : $postIds;
                    if ($ids) DB::table($reference['table'])->whereIn($reference['column'], $ids)->update([$reference['column'] => null]);
                }
            }
            if ($removePositions) {
                Post::withoutGlobalScopes()->whereIn('dept_id', $departments->pluck('id'))->delete();
            }
            foreach ($departments as $department) $department->delete();
        });
    }

    private function references(): array
    {
        if ($this->references !== null) return $this->references;
        $this->references = [];
        foreach (Schema::getTableListing() as $table) {
            if (in_array($table, ['posts', 'users'], true)) continue;
            $columns = null;
            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                if (in_array($foreignKey['foreign_table'], ['departments', 'posts'], true)
                    && $foreignKey['foreign_columns'] === ['id'] && count($foreignKey['columns']) === 1) {
                    $columns ??= collect(Schema::getColumns($table))->keyBy('name');
                    $this->references[] = [
                        'table' => $table, 'column' => $foreignKey['columns'][0], 'target' => $foreignKey['foreign_table'],
                        'nullable' => (bool) ($columns->get($foreignKey['columns'][0])['nullable'] ?? false),
                    ];
                }
            }
        }
        return $this->references;
    }
}
