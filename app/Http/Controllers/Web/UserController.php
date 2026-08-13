<?php

namespace App\Http\Controllers\Web;

use App\Exports\AttendanceDayWiseExport;
use App\Exports\UserExport;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\EmployeeLocation;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\EmployeeLeaveTypeRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\OfficeTimeRepository;
use App\Repositories\PostRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveTypeRequest;
use App\Requests\User\ChangePasswordRequest;
use App\Requests\User\UserAccountRequest;
use App\Requests\User\UserCreateRequest;
use App\Requests\User\UserLeaveTypeRequest;
use App\Requests\User\UserUpdateRequest;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
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
    use CustomAuthorizesRequests;
    private $view = 'admin.employees.';


    public function __construct(protected UserRepository              $userRepo,
                                protected CompanyRepository           $companyRepo,
                                protected RoleRepository              $roleRepo,
                                protected OfficeTimeRepository        $officeTimeRepo,
                                protected UserAccountRepository       $accountRepo,
                                protected CompanyRepository           $companyRepository,
                                protected BranchRepository            $branchRepository,
                                protected LeaveTypeRepository         $leaveTypeRepository,
                                protected EmployeeLeaveTypeRepository $employeeLeaveTypeRepository,
                                protected PostRepository              $postRepository,
                                protected TelegramService             $telegramService,

    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_employee');
        try {


            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'search' => $request->search ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'is_active' => $request->is_active ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'post_id' => $request->post_id ?? null,
                'per_page' => $request->per_page ?? getRecordPerPage(),
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            if (empty($filterParameters['branch_id'])) {
                $filterParameters['department_id'] = null;
                $filterParameters['post_id'] = null;
            }

            if (empty($filterParameters['department_id'])) {
                $filterParameters['post_id'] = null;
            }



            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name', 'role:id,name','officeTime:id,shift,opening_time,closing_time','supervisor:id,name'];

            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $users = $this->userRepo->getAllUsers($filterParameters, $select, $with);

            $company = $this->companyRepository->getCompanyDetail(['id']);
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($company->id, ['id', 'name']);


            if ($request->input('action') == 'export') {
                $fileName = 'users.xlsx';
                return \Maatwebsite\Excel\Facades\Excel::download(new UserExport($users), $fileName);
            }

            return view($this->view . 'index', compact('users', 'filterParameters', 'branches'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $employeeCode = AppHelper::getEmployeeCode();

            $bsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'create', compact('companyDetail', 'roles', 'employeeCode', 'bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
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
            $validatedData['allow_holiday_check_in'] = isset($validatedData['allow_holiday_check_in']) ? 1 : 0;

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
            $this->sendNewEmployeeWelcomeTelegramNotification($user->id);
            return redirect()
                ->route('admin.employees.index')
                ->with('success', __('message.add_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
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

    private function sendNewEmployeeWelcomeTelegramNotification(int $userId): void
    {
        try {
            $user = $this->userRepo->findUserDetailById($userId, ['*'], [
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
            ]);

            if (!$user) {
                return;
            }

            $employeeName = htmlspecialchars((string) $user->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $employeeCode = htmlspecialchars((string) ($user->employee_code ?: 'មិនទាន់មាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $branchName = htmlspecialchars((string) (optional($user->branch)->name ?: 'មិនមាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $departmentName = htmlspecialchars((string) (optional($user->department)->dept_name ?: 'មិនមាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $positionName = htmlspecialchars((string) (optional($user->post)->post_name ?: 'មិនមាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $joiningDate = $user->joining_date ? date('d-m-Y', strtotime($user->joining_date)) : 'មិនមាន';

            $message = "<b>សូមស្វាគមន៍បុគ្គលិកថ្មី</b>\n"
                . "ឈ្មោះ: {$employeeName}\n"
                . "លេខសម្គាល់: {$employeeCode}\n"
                . "សាខា: {$branchName}\n"
                . "ផ្នែក: {$departmentName}\n"
                . "តួនាទី: {$positionName}\n"
                . "ថ្ងៃចូលបម្រើការងារ: {$joiningDate}\n\n"
                . "សូមស្វាគមន៍មកកាន់ក្រុមការងាររបស់យើង។";

            $avatarPath = $user->avatar ? public_path(User::AVATAR_UPLOAD_PATH . $user->avatar) : null;

            if ($avatarPath && is_file($avatarPath)) {
                $this->telegramService->sendPhotoToAllKnownChats($avatarPath, $message, 'HTML');
                return;
            }

            $this->telegramService->sendToAllKnownChats($message, 'HTML');
        } catch (Exception $exception) {
            Log::warning('New employee welcome Telegram notification failed.', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {

        $this->authorize('edit_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $userSelect = ['*'];
            $userWith = [
                'accountDetail',
                'company:id,name',
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
                'role:id,name,slug',
                'supervisor:id,name',
                'officeTime:id,opening_time,closing_time,shift',
            ];
            $userDetail = $this->userRepo->findUserDetailById($id, $userSelect, $userWith);
            $leaveTypes = $this->leaveTypeRepository->getGenderSpecificPaidLeaveTypes($userDetail->branch_id,$userDetail->gender);
            $employeeLeaveTypes = $this->employeeLeaveTypeRepository->getAll(['id', 'leave_type_id', 'days', 'is_active'], $id);
            $bsEnabled = AppHelper::ifDateInBsEnabled();

            $filteredPosts = isset($userDetail->department_id)
                ? $this->postRepository->getAllActivePostsByDepartmentId($userDetail->department_id, [], ['id', 'post_name'])
                : [];

            $filteredSupervisor = isset($userDetail->department_id)
                ? $this->userRepo->getAllActiveEmployeeByDepartment($userDetail->department_id, ['id','name'])
                : [];

            return view($this->view . 'edit', compact('companyDetail', 'roles', 'userDetail', 'leaveTypes', 'employeeLeaveTypes', 'bsEnabled','filteredSupervisor','filteredPosts'));
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
            $validatedData['allow_holiday_check_in'] = isset($validatedData['allow_holiday_check_in']) ? 1 : 0;
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
                ->route('admin.employees.index')
                ->with('success', __('message.update_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleIsActiveStatus($id);
            $userDetail = $this->userRepo->findUserDetailById($id, ['id', 'is_active']);
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('message.user_is_active_changed'),
                    'is_active' => (int) $userDetail->is_active,
                ]);
            }

            return redirect()->back()->with('success', __('message.user_is_active_changed'));
        } catch (Exception $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            }

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
    public function getAllBranchEmployees($branchId)
    {
        try {

            $selectEmployee = ['id', 'name'];
            $employees = $this->userRepo->getActiveEmployeeOfBranch($branchId, $selectEmployee);


            return response()->json([
                'employee' => $employees,
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

            $select = ['name', 'username', 'id'];
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
    public function fetchDepartmentEmployees(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $departmentIds = $request->input('department_ids');
            $select = ['name', 'id'];

            $employees = $this->userRepo->getActiveEmployeesFromDepartments($departmentIds, $select);

            return response()->json($employees);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

//    public function export()
//    {
//        $fileName = 'users.csv';
//        return \Maatwebsite\Excel\Facades\Excel::download(new UserExport, $fileName);
//    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchEmployeeData($branchId)
    {
        try {

            $users = $this->userRepo->getAllBranchUsers($branchId, ['id','name']);

            return response()->json([
                'users' => $users,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

    public function toggleHolidayCheckIn($id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleHolidayCheckIn($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_allow_holiday_check_in_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function logs(Request $request)
    {
        $this->authorize('list_employee');
        try {
            $bsEnabled = AppHelper::ifDateInBsEnabled();
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'date' =>  $request->date ?? ( $bsEnabled ? AppHelper::getCurrentDateInBS()  : date('Y-m-d')),
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }

            $logData = $this->userRepo->getLocationLogs($filterData);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'log', compact('logData', 'companyDetail', 'filterData','bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function liveMap(Request $request)
    {
        $this->authorize('list_employee');

        $filterData = [
            'branch_id' => $request->branch_id ?? null,
            'department_id' => $request->department_id ?? null,
            'employee_id' => $request->employee_id ?? null,
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterData['branch_id'] = auth()->user()->branch_id;
        }

        $with = ['branches:id,name'];
        $select = ['id', 'name'];
        $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

        return view($this->view . 'live-map', compact('companyDetail', 'filterData'));
    }

    /**
     * @throws AuthorizationException
     */
    public function liveMapLocations(Request $request): JsonResponse
    {
        $this->authorize('list_employee');

        try {
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }

            $staff = User::with(['branch:id,name', 'department:id,dept_name'])
                ->select(['id', 'name', 'email', 'phone', 'avatar', 'branch_id', 'department_id', 'uuid', 'logout_status', 'online_status'])
                ->where('is_active', 1)
                ->where('status', 'verified')
                ->where(function ($query) {
                    $query->where(function ($loginQuery) {
                        $loginQuery->whereNotNull('uuid')
                            ->where('logout_status', User::LOGOUT_STATUS['approve']);
                    })->orWhere('online_status', User::ONLINE);
                })
                ->when(!empty($filterData['branch_id']), function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                })
                ->when(!empty($filterData['department_id']), function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                })
                ->when(!empty($filterData['employee_id']), function ($query) use ($filterData) {
                    $query->where('id', $filterData['employee_id']);
                })
                ->orderBy('name')
                ->get();

            $latestLocationIds = EmployeeLocation::query()
                ->selectRaw('MAX(id)')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereIn('employee_id', $staff->pluck('id'))
                ->groupBy('employee_id');

            $latestLocations = EmployeeLocation::query()
                ->whereIn('id', $latestLocationIds)
                ->get()
                ->keyBy('employee_id');

            $locations = $staff
                ->map(function ($employee) use ($latestLocations) {
                    $location = $latestLocations->get($employee->id);
                    $lastSeenAt = $location?->created_at;

                    return [
                        'employee_id' => $employee->id,
                        'name' => ucfirst($employee->name),
                        'email' => $employee->email,
                        'phone' => $employee->phone,
                        'avatar' => $employee->avatar
                            ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar)
                            : asset('assets/images/img.png'),
                        'branch' => $employee->branch?->name,
                        'department' => $employee->department?->dept_name,
                        'latitude' => $location ? (float) $location->latitude : null,
                        'longitude' => $location ? (float) $location->longitude : null,
                        'last_seen_at' => $lastSeenAt?->toIso8601String(),
                        'last_seen_human' => $lastSeenAt?->diffForHumans() ?? 'Waiting for GPS',
                        'has_location' => (bool) $location,
                        'map_url' => $location
                            ? 'https://www.google.com/maps?q=' . $location->latitude . ',' . $location->longitude
                            : null,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'updated_at' => now()->toIso8601String(),
                'total' => $locations->count(),
                'locations' => $locations,
            ]);
        } catch (Exception $exception) {
            Log::error('Unable to load live employee locations', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load live employee locations.',
            ], 500);
        }
    }



}
