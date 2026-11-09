<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Post;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DepartmentPositionController extends Controller
{
    use CustomAuthorizesRequests;

    public function options($position)
    {
        $this->authorize('edit_post');
        $post = $this->positionQuery()->findOrFail($position);
        $current = Department::findOrFail($post->dept_id);
        $departments = $this->departmentQuery()->where('branch_id', $current->branch_id)
            ->where('is_active', 1)->where('dept_name', '!=', $current->dept_name)
            ->orderBy('dept_name')->get(['id', 'dept_name']);
        return response()->json(['departments' => $departments]);
    }

    public function update(Request $request, $position)
    {
        $this->authorize('edit_post');
        DB::transaction(function () use ($request, $position) {
            $post = $this->positionQuery()->lockForUpdate()->findOrFail($position);
            $current = $this->departmentQuery()->findOrFail($post->dept_id);
            $data = $request->validate(['department_id' => ['required', 'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query
                    ->where('company_id', $current->company_id)->where('branch_id', $current->branch_id)
                    ->where('is_active', 1)->where('dept_name', '!=', $current->dept_name)),
            ]]);
            if (DB::table('users')->where('post_id', $post->id)->exists()) {
                throw ValidationException::withMessages(['department_id' => __('index.department_position_employees_first')]);
            }
            if (Post::withoutGlobalScopes()->where('dept_id', $data['department_id'])->where('post_name', $post->post_name)->exists()) {
                throw ValidationException::withMessages(['department_id' => __('index.department_position_duplicate')]);
            }
            $post->fill(['dept_id' => $data['department_id'], 'branch_id' => $current->branch_id])->save();
        });
        return response()->json(['message' => __('index.department_position_moved')]);
    }

    private function departmentQuery()
    {
        return Department::where('company_id', AppHelper::getAuthUserCompanyId())
            ->when(!auth('admin')->check() && isset(auth()->user()->branch_id), fn ($query) => $query->where('branch_id', auth()->user()->branch_id));
    }

    private function positionQuery()
    {
        return Post::withoutGlobalScopes()->whereIn('dept_id', $this->departmentQuery()->select('id'));
    }
}
