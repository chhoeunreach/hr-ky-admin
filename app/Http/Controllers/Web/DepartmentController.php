<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Department\DepartmentStoreRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'per_page' => $request->per_page ?? 25,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch'] = [(string) auth()->user()->branch_id];
            }

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
    public function create()
    {
        $this->authorize('create_department');
        try {
            $select = ['name','id'];
            $branches = $this->branchRepo->getLoggedInUserCompanyBranches(AppHelper::getAuthUserCompanyId(),['id','name']);

            return view($this->view . 'create',
                compact('branches')
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
            if ($branchIds->count() > 1) {
                unset($validatedData['dept_head_id']);
            }
            if(isset($validatedData['dept_head_id'])){
                $this->checkDepartmentHead($validatedData['dept_head_id']);
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
                        'dept_head_id' => $validatedData['dept_head_id'] ?? null,
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

            $selectUser = ['name', 'id'];
            $filteredUsers = isset($departmentsDetail->branch_id)
                ? $this->userRepo->getActiveEmployeeOfBranch($departmentsDetail->branch_id, $selectUser)
                : [];
            $relatedDepartments = Department::query()
                ->where('company_id', AppHelper::getAuthUserCompanyId())
                ->where('dept_name', $departmentsDetail->dept_name)
                ->get(['id', 'branch_id']);
            $selectedBranchIds = $relatedDepartments->pluck('branch_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();

            return view($this->view . 'edit',
                compact('branches', 'filteredUsers', 'departmentsDetail', 'selectedBranchIds')
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
            if ($branchIds->count() > 1) {
                unset($validatedData['dept_head_id']);
            }
            if(isset($validatedData['dept_head_id'])){
                $this->checkDepartmentHead($validatedData['dept_head_id'],$id);
            }

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

                $department->fill([
                    'dept_name' => $validatedData['dept_name'],
                    'slug' => Str::slug($validatedData['dept_name']),
                    'address' => $validatedData['address'],
                    'phone' => $validatedData['phone'],
                    'dept_head_id' => $validatedData['dept_head_id'] ?? null,
                    'is_active' => $validatedData['is_active'] ?? Department::IS_ACTIVE,
                ]);
                $department->save();
            });
            DB::commit();
            return redirect()
                ->route('admin.departments.index')
                ->with('success', __('message.'));
        } catch (Exception $exception) {
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

    public function delete($id)
    {
        $this->authorize('delete_department');
        try {
            $select = ['*'];
            $with = ['posts'];
            $departmentDetail = $this->departmentRepo->findDepartmentById($id,$select,$with);
            if (!$departmentDetail) {
                throw new Exception(__('message.department_not_found'), 404);
            }

            $allRelated = Department::where('company_id', $departmentDetail->company_id)
                ->where('dept_name', $departmentDetail->dept_name)
                ->with('posts')
                ->get();

            foreach ($allRelated as $item) {
                if (count($item->posts) > 0) {
                    throw new Exception(__('message.delete_department_warning'), 403);
                }
            }

            DB::beginTransaction();
            foreach ($allRelated as $item) {
                $this->departmentRepo->delete($item);
            }
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_department'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function checkDepartmentHead($userId, $departmentId=0)
    {
        $statusCheck =  $this->departmentRepo->checkDepartmentHead($userId, $departmentId);

        if($statusCheck){
            throw new Exception(__('index.department_head_error'),404);
        }
    }

}
