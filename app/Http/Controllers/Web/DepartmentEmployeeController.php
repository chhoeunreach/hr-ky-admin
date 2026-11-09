<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeProfileAuditLog;
use App\Models\Post;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DepartmentEmployeeController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request, $id)
    {
        $this->authorize('list_department');
        $departments = $this->departmentQuery();
        $department = (clone $departments)->findOrFail($id);
        $departmentIds = $departments->where('dept_name', $department->dept_name)->pluck('id');
        $employeeStatus = in_array($request->query('employee_status', 'all'), ['all', 'active', 'inactive'], true)
            ? $request->query('employee_status', 'all') : 'all';
        $postIds = $request->boolean('include_position_assignments')
            ? Post::withoutGlobalScopes()->whereIn('dept_id', $departmentIds)->pluck('id')->all() : [];
        $employees = $this->employeeQuery()
            ->where(function ($query) use ($departmentIds, $postIds) {
                $query->whereIn('department_id', $departmentIds);
                if ($postIds) $query->orWhereIn('post_id', $postIds);
            })
            ->when($employeeStatus !== 'all', fn ($query) => $query->where('is_active', $employeeStatus === 'active' ? 1 : 0))
            ->with(['department:id,dept_name', 'branch:id,name', 'post' => fn ($query) => $query->withoutGlobalScopes()->select('id', 'post_name', 'dept_id')])
            ->orderBy('name')->get(['id', 'name', 'avatar', 'branch_id', 'department_id', 'post_id', 'is_active']);

        return response()->json([
            'name' => $department->dept_name,
            'can_edit' => auth('admin')->check() || Gate::allows('edit_employee'),
            'employees' => $employees->map(fn ($employee) => $this->employeeData($employee))->values(),
        ]);
    }

    public function options($employee)
    {
        $this->authorize('edit_employee');
        $user = $this->employeeQuery()->findOrFail($employee);
        $departments = $this->departmentQuery()->where('branch_id', $user->branch_id)
            ->where('is_active', 1)->orderBy('dept_name')->get(['id', 'dept_name']);
        $posts = Post::withoutGlobalScopes()->whereIn('dept_id', $departments->pluck('id'))
            ->where('is_active', 1)->orderBy('post_name')->get(['id', 'post_name', 'dept_id']);

        return response()->json(['departments' => $departments, 'positions' => $posts]);
    }

    public function update(Request $request, $employee)
    {
        $this->authorize('edit_employee');
        abort_if(env('DEMO_MODE', false) && in_array((int) $employee, [1, 2], true), 403, __('message.add_company_warning'));
        $user = DB::transaction(function () use ($request, $employee) {
            $user = $this->employeeQuery()->lockForUpdate()->findOrFail($employee);
            $data = $request->validate([
                'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where(fn ($query) => $query
                    ->where('company_id', $user->company_id)->where('branch_id', $user->branch_id)->where('is_active', 1))],
            ]);
            $destination = $this->departmentQuery()->where('branch_id', $user->branch_id)
                ->where('is_active', 1)->lockForUpdate()->findOrFail($data['department_id']);
            $currentPost = Post::withoutGlobalScopes()->lockForUpdate()->find($user->post_id);
            if (!$currentPost) {
                throw ValidationException::withMessages(['department_id' => __('index.department_employee_position_missing')]);
            }
            $post = Post::withoutGlobalScopes()->where('dept_id', $destination->id)
                ->where('post_name', $currentPost->post_name)->orderByDesc('is_active')->orderBy('id')->lockForUpdate()->first();
            if ($post && !$post->is_active) {
                throw ValidationException::withMessages(['department_id' => __('index.department_employee_position_inactive')]);
            }
            if (!$post) {
                $this->authorize('create_post');
                $post = Post::create(['post_name' => $currentPost->post_name, 'dept_id' => $destination->id,
                    'branch_id' => $user->branch_id, 'is_active' => Post::IS_ACTIVE]);
            }
            $data['post_id'] = $post->id;
            $old = $user->only(['department_id', 'post_id']);
            $user->fill($data)->save();
            if ($old != $user->only(['department_id', 'post_id']) && Schema::hasTable('employee_profile_audit_logs')) {
                EmployeeProfileAuditLog::create([
                    'employee_id' => $user->id, 'module' => 'department_assignment', 'action' => 'update', 'record_id' => $user->id,
                    'old_values' => $old, 'new_values' => $user->only(['department_id', 'post_id']),
                    'performed_by' => auth('admin')->check() ? null : auth()->id(),
                    'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'created_at' => now(),
                ]);
            }
            return $user->load(['department:id,dept_name', 'branch:id,name', 'post:id,post_name,dept_id']);
        });

        return response()->json(['message' => __('index.department_employee_saved'), 'employee' => $this->employeeData($user)]);
    }

    private function employeeQuery()
    {
        return User::where('company_id', AppHelper::getAuthUserCompanyId())
            ->when(!auth('admin')->check() && isset(auth()->user()->branch_id), fn ($query) => $query->where('branch_id', auth()->user()->branch_id));
    }

    private function departmentQuery()
    {
        return Department::where('company_id', AppHelper::getAuthUserCompanyId())
            ->when(!auth('admin')->check() && isset(auth()->user()->branch_id), fn ($query) => $query->where('branch_id', auth()->user()->branch_id));
    }

    private function employeeData(User $employee): array
    {
        return [
            'id' => $employee->id, 'name' => $employee->name, 'branch' => $employee->branch?->name,
            'department' => $employee->department?->dept_name, 'position' => $employee->post?->post_name,
            'department_id' => $employee->department_id, 'post_id' => $employee->post_id,
            'position_department_id' => $employee->post?->dept_id,
            'active' => (bool) $employee->is_active,
            'avatar' => $employee->avatar ? asset(User::AVATAR_UPLOAD_PATH.$employee->avatar) : asset('assets/images/img.png'),
            'assignment_url' => route('admin.departments.employee-assignment', ['employee' => $employee->id]),
        ];
    }
}
