<?php

namespace App\Http\Controllers\Web;

use App\Exports\UserExport;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\EmployeeLeaveTypeRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\OfficeTimeRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveTypeRequest;
use App\Requests\User\ChangePasswordRequest;
use App\Requests\User\UserAccountRequest;
use App\Requests\User\UserCreateRequest;
use App\Requests\User\UserLeaveTypeRequest;
use App\Requests\User\UserUpdateRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

class UserController extends Controller
{
    private $view = 'admin.users.';


    public function __construct(protected UserRepository              $userRepo,
                                protected CompanyRepository           $companyRepo,
                                protected RoleRepository              $roleRepo,
                                protected OfficeTimeRepository        $officeTimeRepo,
                                protected UserAccountRepository       $accountRepo,
                                protected CompanyRepository           $companyRepository,
                                protected BranchRepository            $branchRepository,
                                protected LeaveTypeRepository         $leaveTypeRepository,
                                protected EmployeeLeaveTypeRepository $employeeLeaveTypeRepository,

    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('list_employee');
        try {
            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
            ];
            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name', 'role:id,name'];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $users = $this->userRepo->getAllUsers($filterParameters, $select, $with);

            $company = $this->companyRepository->getCompanyDetail(['id']);
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($company->id, ['id', 'name']);

            return view($this->view . 'index', compact('users', 'filterParameters', 'branches'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $employeeCode = AppHelper::getEmployeeCode();

            $leaveTypes = $this->leaveTypeRepository->getPaidLeaveTypes();
            $bsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'create', compact('companyDetail', 'roles', 'leaveTypes', 'employeeCode', 'bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(UserCreateRequest $request, UserAccountRequest $accountRequest, UserLeaveTypeRequest $leaveRequest)
    {
        $this->authorize('create_employee');
        try {
            $validatedData = $request->validated();

            $accountValidatedData = $accountRequest->validated();
            $leaveTypeData = $leaveRequest->validated();

            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_active'] = 1;
            $validatedData['status'] = 'verified';
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();


            DB::beginTransaction();
            $user = $this->userRepo->store($validatedData);
            $accountValidatedData['user_id'] = $user['id'];
            $this->accountRepo->store($accountValidatedData);

            if (!is_null($user['leave_allocated']) && isset($leaveTypeData['leave_type_id'])) {
                foreach ($leaveTypeData['leave_type_id'] as $key => $value) {
                    $input['days'] = $leaveTypeData['days'][$key] ?? 0;
                    $input['is_active'] = $leaveTypeData['is_active'][$key] ?? 0;
                    $input['employee_id'] = $user['id'];
                    $input['leave_type_id'] = $value;

                    $this->employeeLeaveTypeRepository->store($input);

                }
            }


            DB::commit();
            return redirect()
                ->route('admin.users.index')
                ->with('success', __('message.add_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize('show_detail_employee');
        try {
            $with = [
                'branch:id,name',
                'company:id,name',
                'post:id,post_name',
                'department:id,dept_name',
                'role:id,name',
                'accountDetail'
            ];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $userDetail = $this->userRepo->findUserDetailById($id, $select, $with);
            return view($this->view . 'show2', compact('userDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    public function edit($id)
    {

        $this->authorize('edit_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $userSelect = ['*'];
            $userWith = ['accountDetail'];
            $userDetail = $this->userRepo->findUserDetailById($id, $userSelect, $userWith);
            $leaveTypes = $this->leaveTypeRepository->getPaidLeaveTypes();
            $employeeLeaveTypes = $this->employeeLeaveTypeRepository->getAll(['id', 'leave_type_id', 'days', 'is_active'], $id);
            $bsEnabled = AppHelper::ifDateInBsEnabled();


            return view($this->view . 'edit', compact('companyDetail', 'roles', 'userDetail', 'leaveTypes', 'employeeLeaveTypes', 'bsEnabled'));
        } catch (Exception $exception) {

            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    public function update(UserUpdateRequest $request, UserAccountRequest $accountRequest, UserLeaveTypeRequest $leaveRequest, $id)
    {
        $this->authorize('edit_employee');
        try {
            $validatedData = $request->validated();

            if (env('DEMO_MODE', false) && (in_array($id, [1, 2]))) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $accountValidatedData = $accountRequest->validated();

            $leaveTypeData = $leaveRequest->validated();


            $userDetail = $this->userRepo->findUserDetailById($id);
            if (in_array($userDetail->username, User::DEMO_USERS_USERNAME)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->update($userDetail, $validatedData);
            $this->accountRepo->createOrUpdate($userDetail, $accountValidatedData);

            if (!is_null($validatedData['leave_allocated']) && isset($leaveTypeData['leave_type_id'])) {
                foreach ($leaveTypeData['leave_type_id'] as $key => $value) {
                    $input['days'] = $leaveTypeData['days'][$key];
                    $input['is_active'] = $leaveTypeData['is_active'][$key] ?? 0;

                    $employeeLeaveTypeData = $this->employeeLeaveTypeRepository->findByLeaveType($id, $value);
                    if ($employeeLeaveTypeData) {

                        $this->employeeLeaveTypeRepository->update($employeeLeaveTypeData, $input);
                    } else {
                        $input['employee_id'] = $id;
                        $input['leave_type_id'] = $value;


                        $this->employeeLeaveTypeRepository->store($input);
                    }
                }
            } else {
                $this->employeeLeaveTypeRepository->deleteByEmployee($id);
            }


            DB::commit();
            return redirect()
                ->route('admin.users.index')
                ->with('success', __('message.update_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleIsActiveStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_is_active_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_employee');
        try {

            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            $usersDetail = $this->userRepo->findUserDetailById($id);

            if (!$usersDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }

            if ($usersDetail->id == auth()->user()->id) {
                throw new Exception(__('message._delete_own'), 402);
            }

            DB::beginTransaction();
            $this->userRepo->delete($usersDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_remove'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeWorkSpace($id)
    {
        $this->authorize('edit_employee');
        try {
            $select = ['id', 'workspace_type'];
            $userDetail = $this->userRepo->findUserDetailById($id, $select);
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->changeWorkSpace($userDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.workspace_change'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getAllCompanyEmployeeDetail($branchId)
    {
        try {

            $branch = $this->branchRepository->findBranchDetailById($branchId);

            $selectEmployee = ['id', 'name'];
            $selectOfficeTime = ['id', 'opening_time', 'closing_time'];
            $employees = $this->userRepo->getAllVerifiedEmployeeOfCompany($selectEmployee);
            $officeTime = $this->officeTimeRepo->getALlActiveOfficeTimeByCompanyId($branch->company_id, $selectOfficeTime);

            return response()->json([
                'employee' => $employees,
                'officeTime' => $officeTime
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request, $userId)
    {
        $this->authorize('change_password');
        try {
            $validatedData = $request->validated();
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $userDetail = $this->userRepo->findUserDetailById($userId);

            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->changePassword($userDetail, $validatedData['new_password']);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_password_change'));

        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function forceLogOutEmployee($employeeId)
    {
        $this->authorize('force_logout');
        try {
            $tokenRepository = app(TokenRepository::class);
            $refreshTokenRepository = app(RefreshTokenRepository::class);

            $userDetail = $this->userRepo->findUserDetailById($employeeId);
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            $accessToken = $userDetail->tokens;
            DB::beginTransaction();
            foreach ($accessToken as $token) {
                $tokenRepository->revokeAccessToken($token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
            }
            $validatedData['uuid'] = null;
            $validatedData['logout_status'] = 0;
            $validatedData['remember_token'] = null;
            $validatedData['fcm_token'] = null;
            $this->userRepo->update($userDetail, $validatedData);
            DB::commit();
            return redirect()->back()->with('success', __('message.force_logout'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function deleteEmployeeLeaveType($id)
    {
        $this->authorize('delete_employee');
        try {
            $employeeLeaveType = $this->employeeLeaveTypeRepository->find($id);

            if (!$employeeLeaveType) {
                throw new Exception(__('message.employee_leave_not_found'), 404);
            }

            DB::beginTransaction();
            $this->employeeLeaveTypeRepository->delete($employeeLeaveType);
            DB::commit();
            return redirect()->back()->with('success', __('message.employee_leave_removed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function getAllEmployeeByDepartmentId($departmentId): JsonResponse|RedirectResponse
    {
        try {

            $select = ['name', 'id'];
            $users = $this->userRepo->getAllActiveEmployeeOfDepartment($departmentId, $select);
            return response()->json([
                'data' => $users
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function fetchEmployeesByDepartment(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $departmentIds = $request->input('department_ids');
            $select = ['name', 'id'];

            $employees = $this->userRepo->getActiveEmployeesByDepartment($departmentIds, $select);

            return response()->json($employees);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    public function export()
    {
        $fileName = 'users.csv';
        return \Maatwebsite\Excel\Facades\Excel::download(new UserExport, $fileName);
    }


}
