<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentRepository
{

    /**
     * @param array $with
     * @param array $select
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginatedDepartments($filterParameters, array $with=[], array $select=['*'])
    {
        $allowedPerPage = [10, 25, 50, 100, 200, 500, 1000];
        $requestedPerPage = $filterParameters['per_page'] ?? 25;

        $baseQuery = Department::query()
            ->where('company_id', $filterParameters['company_id'])
            ->when(!empty($filterParameters['branch']), function ($query) use ($filterParameters) {
                $branchIds = collect(is_array($filterParameters['branch']) ? $filterParameters['branch'] : [$filterParameters['branch']])
                    ->filter(fn ($id) => $id !== null && $id !== '')
                    ->all();
                if (!empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
            })
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('dept_name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->when(!empty($filterParameters['search']), function ($query) use ($filterParameters) {
                $search = '%' . $filterParameters['search'] . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('dept_name', 'like', $search)
                        ->orWhere('address', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhereHas('branch', function ($branchQuery) use ($search) {
                            $branchQuery->where('name', 'like', $search);
                        })
                        ->orWhereHas('departmentHead', function ($headQuery) use ($search) {
                            $headQuery->where('name', 'like', $search);
                        });
                });
            })
            ->when(($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null, function ($query) use ($filterParameters) {
                $query->where('is_active', $filterParameters['is_active']);
            });

        // Group by dept_name so that departments across branches are not duplicated in the list
        $nameQuery = (clone $baseQuery)
            ->select('dept_name', DB::raw('MAX(id) as max_id'))
            ->groupBy('dept_name')
            ->orderByDesc('max_id');

        if ($requestedPerPage === 'all') {
            $totalRecord = (clone $nameQuery)->get()->count();
            $paginatedNames = $nameQuery->paginate($totalRecord > 0 ? $totalRecord : 1);
        } else {
            $perPage = in_array((int) $requestedPerPage, $allowedPerPage, true)
                ? (int) $requestedPerPage
                : 25;
            $paginatedNames = $nameQuery->paginate($perPage);
        }

        $targetNames = $paginatedNames->pluck('dept_name')->all();
        $departmentsGrouped = Department::with(['branch', 'employees', 'departmentHead'])
            ->where('company_id', $filterParameters['company_id'])
            ->whereIn('dept_name', $targetNames)
            ->get()
            ->groupBy('dept_name');

        $items = $paginatedNames->getCollection()->map(function ($row) use ($departmentsGrouped) {
            $group = $departmentsGrouped->get($row->dept_name, collect());
            $first = $group->first() ?? new Department();

            $mergedEmployees = $group->flatMap->employees->unique('id')->values();
            $branches = $group->pluck('branch')->filter()->unique('id')->values();
            $isActive = $group->contains(fn ($d) => (int)$d->is_active === 1) ? 1 : 0;
            $head = $group->pluck('departmentHead')->filter()->first();

            $dept = clone $first;
            $dept->id = $row->max_id;
            $dept->dept_name = $row->dept_name;
            $dept->setRelation('branch', $branches->first());
            $dept->setRelation('branches', $branches);
            $dept->setRelation('employees', $mergedEmployees);
            $dept->employees_count = $mergedEmployees->count();
            $dept->setRelation('departmentHead', $head);
            $dept->is_active = $isActive;

            return $dept;
        });

        return $paginatedNames->setCollection($items);
    }

    /**
     * @param array $with
     * @param array $select
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveDepartments(array $with=[], array $select=['*'])
    {
        return  Department::with($with)
            ->select($select)
            ->where('is_active',1)->get();
    }

    public function getAllActiveDepartmentsByBranchId($branchId,$with=[], $select=['*'])
    {
        $branchIds = collect(is_array($branchId) ? $branchId : explode(',', (string) $branchId))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->all();

        return Department::with($with)
            ->select($select)
            ->where('is_active', 1)
            ->whereIn('branch_id', $branchIds)
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->orderBy('dept_name')
            ->get();
    }


    /**
     * @param $id
     * @param $select
     * @return mixed
     */
    public function findDepartmentById($id, $select=['*'],$with=[])
    {
        return Department::select($select)->where('id',$id)->first();
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws \Exception
     */
    public function store($validatedData)
    {
        $validatedData['slug'] = Str::slug($validatedData['dept_name']);
        $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
        return Department::create($validatedData)->fresh();
    }

    /**
     * @param $departmentDetail
     * @return mixed
     */
    public function delete($departmentDetail)
    {
        return $departmentDetail->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function toggleStatus($id)
    {
        $departmentDetail = $this->findDepartmentById($id);
        return $departmentDetail->update([
            'is_active' => !$departmentDetail->is_active,
        ]);
    }

    /**
     * @param $departmentDetail
     * @param $validatedData
     * @return mixed
     */
    public function update($departmentDetail, $validatedData)
    {
       return $departmentDetail->update($validatedData);
    }

    public function pluckAllDepartments()
    {
        return  Department::pluck('dept_name','id')->toArray();
    }

    public function getDepartmentListUsingAuthUserBranchId()
    {
        return DB::table('departments')
            ->join('branches', 'departments.branch_id', '=', 'branches.id')
            ->join('users', 'branches.id', '=', 'users.branch_id')
            ->where('users.id', getAuthUserCode())
            ->where('departments.is_active', Department::IS_ACTIVE)
            ->get(['departments.id','departments.dept_name']);

    }

    public function checkDepartmentHead($userId, $departmentId=0)
    {
        $department =  Department::where('dept_head_id', $userId);

        if($departmentId != 0){
            $department =$department->where('id','!=',$departmentId);
        }

        return  $department->exists();

    }


}
