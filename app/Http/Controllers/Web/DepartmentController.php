<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Exports\DepartmentTableExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Department\DepartmentStoreRequest;
use App\Services\DepartmentDeletionService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.department.';

    private DepartmentRepository $departmentRepo;
    private UserRepository $userRepo;
    private BranchRepository $branchRepo;

    public function __construct(DepartmentRepository $departmentRepo,
                                UserRepository $userRepo,
                                BranchRepository $branchRepo)
    {
        $this->departmentRepo = $departmentRepo;
        $this->userRepo = $userRepo;
        $this->branchRepo = $branchRepo;
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_department');
        try {
            $filterParameters = $this->listFilters($request);

            $selectBranch = ['id','name'];
            $with = ['branch:id,name', 'departmentHead:id,name', 'employees:id,name,avatar,department_id'];
            $branch = $this->branchRepo->getLoggedInUserCompanyBranches($filterParameters['company_id'],$selectBranch);
            $departments = $this->departmentRepo->getAllPaginatedDepartments($filterParameters,$with);
            return view($this->view . 'index', compact('departments','filterParameters','branch'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function performanceDefaults($id)
    {
        $this->authorize('list_department');
        $department = \App\Models\Department::findOrFail($id);
        $defaults = app(\App\Services\DepartmentPerformanceDefaults::class)->forName($department->dept_name);
        return view('admin.department.performance-defaults', compact('department', 'defaults'));
    }

    public function applyPerformanceDefaults($id)
    {
        $this->authorize('edit_department');
        $this->authorize('employee.kpi.manage');
        $department = \App\Models\Department::findOrFail($id);
        abort_unless($department->is_active, 422);
        $counts = app(\App\Services\DepartmentPerformanceDefaults::class)->applyToDepartment($department);
        return back()->with('success', __('index.department_defaults_applied', $counts));
    }

    public function create()
    {
        $this->authorize('create_department');
        try {
            $select = ['name','id'];
            $branches = $this->branchRepo->getLoggedInUserCompanyBranches(AppHelper::getAuthUserCompanyId(),['id','name']);
            $headEmployeesByBranch = $this->getHeadEmployeesByBranch($branches);

            return view($this->view . 'create',
                compact('branches', 'headEmployeesByBranch')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function getAllDepartmentsByBranchId($branchId): JsonResponse|RedirectResponse
    {
        try {
            $with = ['branch:id,name'];
            $select = ['dept_name', 'id', 'branch_id'];
            $departments = $this->departmentRepo->getAllActiveDepartmentsByBranchId($branchId, $with, $select);

            $formatted = $departments->map(function ($department) {
                $deptName = (string) $department->dept_name;
                $branchName = $department->branch?->name;
                if (str_contains($deptName, 'អ្នកទទួលភ្ញៀវ') && $branchName && !str_contains($deptName, $branchName)) {
                    $formattedName = rtrim($deptName, ' -') . ' - ' . $branchName;
                } else {
                    $formattedName = $deptName;
                }

                return [
                    'id' => $department->id,
                    'dept_name' => $formattedName,
                    'raw_dept_name' => $deptName,
                    'branch_id' => $department->branch_id,
                    'branch_name' => $branchName,
                ];
            });

            return response()->json([
                'data' => $formatted
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(DepartmentStoreRequest $request)
    {
        $this->authorize('create_department');
        try {
            $validatedData = $request->validated();
            $branchIds = collect($validatedData['branch_id'])->unique()->values();
            foreach ($validatedData['dept_head_ids'] ?? [] as $headId) {
                if ($headId) {
                    $this->checkDepartmentHead($headId);
                }
            }
            DB::beginTransaction();
            $branchIds->each(function ($branchId) use ($validatedData) {
                Department::firstOrCreate(
                    [
                        'company_id' => AppHelper::getAuthUserCompanyId(),
                        'branch_id' => $branchId,
                        'dept_name' => $validatedData['dept_name'],
                    ],
                    [
                        'slug' => Str::slug($validatedData['dept_name']),
                        'address' => $validatedData['address'],
                        'phone' => $validatedData['phone'],
                        'dept_head_id' => $validatedData['dept_head_ids'][$branchId] ?? null,
                        'is_active' => $validatedData['is_active'] ?? Department::IS_ACTIVE,
                    ]
                );
            });
            DB::commit();
            return redirect()
                ->route('admin.departments.index')
                ->with('success', __('message.add_department'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.departments.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_department');
        try {
            $departmentsDetail = $this->departmentRepo->findDepartmentById($id);

            $branches = $this->branchRepo->getLoggedInUserCompanyBranches(AppHelper::getAuthUserCompanyId(),['id','name']);

            $headEmployeesByBranch = $this->getHeadEmployeesByBranch($branches);
            $relatedDepartments = Department::query()
                ->where('company_id', AppHelper::getAuthUserCompanyId())
                ->where('dept_name', $departmentsDetail->dept_name)
                ->get(['id', 'branch_id', 'dept_head_id']);
            $selectedBranchIds = $relatedDepartments->pluck('branch_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();
            $departmentHeadsByBranch = $relatedDepartments->pluck('dept_head_id', 'branch_id')->all();

            return view($this->view . 'edit',
                compact('branches', 'headEmployeesByBranch', 'departmentsDetail', 'selectedBranchIds', 'departmentHeadsByBranch')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(DepartmentStoreRequest $request, $id)
    {
        $this->authorize('edit_department');
        try {
            $validatedData = $request->validated();
            $departmentDetail = $this->departmentRepo->findDepartmentById($id);
            if (!$departmentDetail) {
                throw new Exception(__('message.update_department'), 404);
            }
            $branchIds = collect($validatedData['branch_id'])->unique()->values();
            DB::beginTransaction();
            $oldDepartmentName = $departmentDetail->dept_name;
            $branchIds->each(function ($branchId) use ($validatedData, $oldDepartmentName, $departmentDetail) {
                $department = $branchId == $departmentDetail->branch_id
                    ? $departmentDetail
                    : Department::query()
                        ->where('company_id', AppHelper::getAuthUserCompanyId())
                        ->where('branch_id', $branchId)
                        ->whereIn('dept_name', [$oldDepartmentName, $validatedData['dept_name']])
                        ->first();

                if (!$department) {
                    $department = new Department();
                    $department->company_id = AppHelper::getAuthUserCompanyId();
                    $department->branch_id = $branchId;
                }

                $headId = $validatedData['dept_head_ids'][$branchId] ?? null;
                if ($headId) {
                    $this->checkDepartmentHead($headId, $department->id ?? 0);
                }

                $department->fill([
                    'dept_name' => $validatedData['dept_name'],
                    'slug' => Str::slug($validatedData['dept_name']),
                    'address' => $validatedData['address'],
                    'phone' => $validatedData['phone'],
                    'dept_head_id' => $headId,
                    'is_active' => $validatedData['is_active'] ?? Department::IS_ACTIVE,
                ]);
                $department->save();
            });
            DB::commit();
            return redirect()
                ->route('admin.departments.index')
                ->with('success', __('message.'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }

    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_department');
        try {
            DB::beginTransaction();
            $dept = Department::find($id);
            if ($dept) {
                $newStatus = ((int)$dept->is_active === 1) ? 0 : 1;
                Department::where('company_id', $dept->company_id)
                    ->where('dept_name', $dept->dept_name)
                    ->update(['is_active' => $newStatus]);
            } else {
                $this->departmentRepo->toggleStatus($id);
            }
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete(Request $request, $id, DepartmentDeletionService $deletion)
    {
        $this->authorize('delete_department');
        if (!$request->isMethod('get')) {
            $request->validate(['remove_positions' => ['sometimes', 'boolean'], 'unlink_records' => ['sometimes', 'boolean']]);
            if ($request->boolean('remove_positions')) $this->authorize('delete_post');
        }
        try {
            if ($request->isMethod('get')) {
                return response()->json($deletion->preview($deletion->departments($id)) + [
                    'can_remove_positions' => auth('admin')->check() || Gate::allows('delete_post'),
                    'can_change_positions' => auth('admin')->check() || Gate::allows('edit_post'),
                    'can_change_employees' => auth('admin')->check() || Gate::allows('edit_employee'),
                    'employees_url' => route('admin.departments.employees', ['id' => $id, 'employee_status' => 'all', 'include_position_assignments' => 1]),
                ]);
            }
            $deletion->delete($id, $request->boolean('remove_positions'), $request->boolean('unlink_records'));
            return response()->json(['message' => __('message.delete_department')]);
        } catch (ValidationException $exception) {
            return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => __('message.department_not_found')], 404);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json(['message' => __('index.department_delete_reassign')], 409);
        } catch (Exception $exception) {
            report($exception);
            return response()->json(['message' => __('index.department_delete_failed')], 500);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('list_department');
        $columns = [
            'id' => '#', 'name' => __('index.name'), 'head' => __('index.department_head'),
            'employees' => __('index.total_employees'), 'address' => __('index.address'),
            'branch' => __('index.branch_name'), 'status' => __('index.status'),
        ];
        $request->validate([
            'format' => ['required', Rule::in(['json', 'csv', 'excel', 'print', 'pdf'])],
            'orientation' => ['nullable', Rule::in(['portrait', 'landscape'])],
            'columns' => ['sometimes', 'array', 'min:1'],
            'columns.*' => ['string', Rule::in(array_keys($columns))],
        ]);
        $keys = array_values(array_intersect(array_keys($columns), $request->input('columns', array_keys($columns))));
        $headings = array_map(fn ($key) => $columns[$key], $keys);
        $filter = $this->listFilters($request);
        $filter['per_page'] = 'all';
        $filter['page'] = 1;
        $departments = $this->departmentRepo->getAllPaginatedDepartments($filter);
        $rows = $departments->getCollection()->values()->map(function ($department, $index) use ($keys) {
            $values = [
                'id' => (string) ($index + 1), 'name' => $department->dept_name,
                'head' => $department->departmentHeads->pluck('name')->join("\n"),
                'employees' => (string) $department->employees_count,
                'address' => $department->departmentEntries->map(fn ($entry) => ($department->departmentEntries->count() > 1 ? ($entry->branch?->name ?? '').': ' : '').($entry->address ?? ''))->join("\n"),
                'branch' => $department->branches->pluck('name')->join("\n"),
                'status' => $department->is_active ? __('index.active') : __('index.inactive'),
            ];
            return array_map(fn ($key) => (string) $values[$key], $keys);
        })->all();
        $format = $request->input('format');
        if ($format === 'json') return response()->json(compact('headings', 'rows'));
        if (in_array($format, ['csv', 'excel'], true)) {
            if ($format === 'csv') $rows = array_map(fn ($row) => array_map(fn ($value) => preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value, $row), $rows);
            return Excel::download(new DepartmentTableExport($headings, $rows), 'departments.'.($format === 'csv' ? 'csv' : 'xlsx'));
        }
        $orientation = $request->input('orientation', 'landscape');
        $employeeStatus = $filter['employee_status'] === 'all' ? __('index.all') : __('index.'.$filter['employee_status']);
        return view('admin.department.exports.table', compact('headings', 'rows', 'format', 'orientation', 'employeeStatus'));
    }

    private function listFilters(Request $request): array
    {
        $branchParam = $request->query('branch', $request->query('branch_id', []));
        $branchIds = collect(is_array($branchParam) ? $branchParam : [$branchParam])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $filterParameters = [
            'company_id' => AppHelper::getAuthUserCompanyId(),
            'name' =>  $request->name ?? null,
            'search' => $request->search ?? null,
            'branch' =>  $branchIds,
            'is_active' => $request->is_active ?? null,
            'employee_status' => in_array($request->query('employee_status', 'all'), ['all', 'active', 'inactive'], true)
                ? $request->query('employee_status', 'all') : 'all',
            'per_page' => $request->per_page ?? 10,
            'sort_by' => in_array($request->query('sort_by', 'id'), DepartmentRepository::SORT_COLUMNS, true)
                ? $request->query('sort_by', 'id') : 'id',
            'sort_direction' => $request->query('sort_direction') === 'asc' ? 'asc' : 'desc',
        ];

        if(!auth('admin')->check() && auth()->check()){
            $filterParameters['branch'] = [(string) auth()->user()->branch_id];
        }
        return $filterParameters;
    }

    /**
     * @throws Exception
     */
    private function getHeadEmployeesByBranch($branches)
    {
        return User::query()
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->whereIn('branch_id', $branches->pluck('id'))
            ->when(!auth('admin')->check() && isset(auth()->user()->branch_id), function ($query) {
                $query->where('branch_id', auth()->user()->branch_id);
            })
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id'])
            ->groupBy('branch_id');
    }

    public function checkDepartmentHead($userId, $departmentId=0)
    {
        $statusCheck =  $this->departmentRepo->checkDepartmentHead($userId, $departmentId);

        if($statusCheck){
            throw new Exception(__('index.department_head_error'),404);
        }
    }

}
