<?php

namespace App\Http\Controllers\Api;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\ChatConversation;
use App\Models\Company;
use App\Models\DCardEmployee;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\User\Api\UserChangePasswordRequest;
use App\Requests\User\Api\UserProfileUpdateApiRequest;
use App\Resources\award\AwardCollection;
use App\Resources\User\CompanyResource;
use App\Resources\User\EmployeeDetailResource;
use App\Resources\User\UserResource;
use App\Services\Attendance\AttendanceService;
use App\Services\AwardManagement\AwardService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;


class UserProfileApiController extends Controller
{
    use CustomAuthorizesRequests;

    public function __construct(protected UserRepository $userRepo,
                                protected CompanyRepository $companyRepo,
                                protected AttendanceService $attendanceService,
                                protected BranchRepository $branchRepository,
                                protected AwardService $awardService
    )
    {}

    public function userProfileDetail(): JsonResponse
    {
        try {
            $this->authorize('view_profile');
            $with = [
                'branch:id,name',
                'company:id,name',
                'post:id,post_name',
                'department:id,dept_name',
                'role:id,name',
                'accountDetail'
            ];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $user = $this->userRepo->findUserDetailById(getAuthUserCode(), $select, $with);
            $userDetail = new UserResource($user);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $userDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function dCardProfile(): JsonResponse
    {
        try {
            $this->authorize('view_profile');

            $with = [
                'branch:id,name,logo,payment_qr_codes',
                'company:id,name,logo,address,phone,website_url',
                'post:id,post_name',
                'department:id,dept_name',
            ];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id'];
            $user = $this->userRepo->findUserDetailById(getAuthUserCode(), $select, $with);
            $card = $this->transformUserDCard($user);

            if (Schema::hasTable('d_card_employees') && ! empty($card['employee_code'])) {
                $override = DCardEmployee::where('employee_code', $card['employee_code'])->first();
                if ($override) {
                    $card = $this->mergeDCardOverride($card, $override);
                }
            }

            return AppHelper::sendSuccessResponse(__('index.data_found'), $card);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function changePassword(UserChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authorize('allow_change_password');
            $validatedData = $request->validated();
            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            if(in_array($userDetail->username, User::DEMO_USERS_USERNAME)){
                throw new Exception(__('index.demo_version'),400);
            }
            if (!Hash::check($validatedData['current_password'], $userDetail->password)) {
                throw new Exception(__('index.incorrect_current_password'), 403);
            }
            if (Hash::check($validatedData['new_password'],$userDetail->password )) {
                throw new Exception(__('index.new_password_same_as_old'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->changePassword($userDetail, $validatedData['new_password']);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.password_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function updateUserProfile(UserProfileUpdateApiRequest $request): JsonResponse
    {
        try {
            $this->authorize('update_profile');
            $validatedData = $request->validated();
            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            if(in_array($userDetail->username, User::DEMO_USERS_USERNAME)){
                throw new Exception(__('index.demo_version'),400);
            }
            if (!$userDetail) {
                throw new Exception(__('index.user_not_found'), 404);
            }
            DB::beginTransaction();
                $this->userRepo->update($userDetail, $validatedData);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.profile_updated'),new UserResource($userDetail));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function findEmployeeDetailById($userId)
    {
        try {
            $this->authorize('show_profile_detail');
            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name'];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id'];
            $employee =[];
            $employee = $this->userRepo->findUserDetailById($userId, $select, $with);

            $employeeDetail = new EmployeeDetailResource($employee);

            $awardList = $this->awardService->getEmployeeAward($userId,5, ['*'],['employee:id,name,avatar', 'type:id,title'], 1);

            // Wrap the employee details in a resource and attach the awards
            $employeeDetail = (new EmployeeDetailResource($employee))->additional(['awards' => new AwardCollection($awardList)]);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $employeeDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function getTeamSheetOfCompany()
    {
        try {
            $this->authorize('list_team_sheet');
            $data = [];

            $select = ['id', 'name'];
            $with = [
                'employee.branch:id,name',
                'employee.department:id,dept_name',
                'employee.post:id,post_name',
                'employee.role:id,name,slug',
            ];
//            $updateOnline = $this->updateOnlineStatusBasedOnTodayAttendance();
//            if($updateOnline){
                $companyWithEmployee = $this->companyRepo
                    ->findOrFailCompanyDetailById(AppHelper::getAuthUserCompanyId(), $select, $with);
                $companyDetail = (new CompanyResource($companyWithEmployee))->toArray(request());
                $companyDetail['employee'] = array_values(array_merge(
                    $companyDetail['employee'],
                    $this->getAdminDirectoryEntries(auth()->id())
                ));

                $branches = $this->branchRepository->getBranchesWithDepartments();
                $data = [
                    'companyDetail'=> $companyDetail,
                    'branches' => $branches
                ];

//            }
            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    private function transformUserDCard(User $user): array
    {
        $companyWebsite = $user->company?->website_url ?: 'https://www.kneayerng.com';
        $telegramUrl = 'https://t.me/kneayerng';
        $paymentQrCodes = collect(Schema::hasColumn('branches', 'payment_qr_codes') ? ($user->branch?->payment_qr_codes ?? []) : [])
            ->map(fn ($qrCode) => [
                'payment_name' => $qrCode['payment_name'] ?? '',
                'qr_code_url' => ! empty($qrCode['qr_code'])
                    ? asset(Branch::UPLOAD_PATH . $qrCode['qr_code'])
                    : null,
            ])
            ->filter(fn ($qrCode) => $qrCode['payment_name'] && $qrCode['qr_code_url'])
            ->values()
            ->all();

        return [
            'id' => 'user-' . $user->id,
            'record_id' => $user->id,
            'source' => 'user',
            'employee_code' => $user->employee_code ?: sprintf('KY-%05d', $user->id),
            'name' => $user->name,
            'english_name' => $user->english_name,
            'position_khmer' => $user->post?->post_name,
            'position_english' => $user->post?->post_name,
            'post' => $user->post?->post_name,
            'department' => $user->department?->dept_name,
            'branch' => $user->branch?->name,
            'joining_date' => $this->dateForCard($user->joining_date),
            'emergency_contact' => '',
            'blood_type' => '',
            'khqr_account_id' => '',
            'phone' => $user->phone,
            'email' => $user->email,
            'photo_url' => $user->avatar
                ? asset(User::AVATAR_UPLOAD_PATH . $user->avatar)
                : asset('assets/images/img.png'),
            'profile_photo_url' => $user->avatar
                ? asset(User::AVATAR_UPLOAD_PATH . $user->avatar)
                : asset('assets/images/img.png'),
            'branch_name' => $user->branch?->name,
            'branch_logo_url' => Schema::hasColumn('branches', 'logo') && $user->branch?->logo
                ? asset(Branch::UPLOAD_PATH . $user->branch->logo)
                : null,
            'company_logo_url' => $user->company?->logo
                ? asset(Company::UPLOAD_PATH . $user->company->logo)
                : asset('assets/images/img.png'),
            'company' => $user->company?->name,
            'company_address' => $user->company?->address,
            'company_phone' => $user->company?->phone,
            'company_website' => $companyWebsite,
            'payment_qr_codes' => $paymentQrCodes,
            'website_qr_data' => $companyWebsite,
            'telegram_qr_data' => $telegramUrl,
            'website_qr_url' => $this->qrCodeUrl($companyWebsite),
            'telegram_qr_url' => $this->qrCodeUrl($telegramUrl),
        ];
    }

    private function mergeDCardOverride(array $card, DCardEmployee $override): array
    {
        $post = ($override->position_khmer ?: $override->position_english) ?: null;

        return [
            ...$card,
            'id' => 'dcard-' . $override->id,
            'record_id' => $override->id,
            'source' => 'dcard',
            'employee_code' => $override->employee_code ?: $card['employee_code'],
            'name' => $override->name_khmer ?: $card['name'],
            'english_name' => $override->name_english ?: $card['english_name'],
            'position_khmer' => $override->position_khmer ?: $card['position_khmer'],
            'position_english' => $override->position_english ?: $card['position_english'],
            'post' => $post ?: $card['post'],
            'department' => $override->department ?: $card['department'],
            'branch' => $override->branch ?: $card['branch'],
            'joining_date' => $this->dateForCard($override->joining_date) ?: $card['joining_date'],
            'emergency_contact' => $override->emergency_contact ?: $card['emergency_contact'],
            'blood_type' => $override->blood_type ?: $card['blood_type'],
            'khqr_account_id' => $override->khqr_account_id ?: $card['khqr_account_id'],
            'phone' => $override->phone ?: $card['phone'],
            'email' => $override->email ?: $card['email'],
            'photo_url' => $override->profile_photo_url ?: $card['photo_url'],
            'profile_photo_url' => $override->profile_photo_url ?: ($card['profile_photo_url'] ?? $card['photo_url']),
            'branch_name' => $override->branch ?: ($card['branch_name'] ?? $card['branch']),
            'branch_logo_url' => $this->branchLogoUrl($override->branch) ?: $card['branch_logo_url'],
        ];
    }

    private function dateForCard(mixed $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        return \Illuminate\Support\Carbon::parse($date)->format('Y-m-d');
    }

    private function branchLogoUrl(?string $branchName): ?string
    {
        if (empty($branchName) || ! Schema::hasColumn('branches', 'logo')) {
            return null;
        }

        $cleanBranchName = trim($branchName);
        $logo = Branch::withoutGlobalScopes()
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanBranchName)])
            ->value('logo');

        return $logo ? asset(Branch::UPLOAD_PATH . $logo) : null;
    }

    private function qrCodeUrl(string $data): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($data);
    }

    private function getAdminDirectoryEntries(?int $userId = null): array
    {
        return Admin::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'avatar'])
            ->map(function (Admin $admin) use ($userId) {
                $directoryId = 1000000 + (int) $admin->id;
                $conversation = null;

                if ($userId) {
                    try {
                        $conversation = $this->getOrCreateAdminConversation($userId, $admin->id);
                    } catch (\Throwable $throwable) {
                        $conversation = null;
                    }
                }

                return [
                    'id' => $directoryId,
                    'name' => $admin->name ?? 'Admin',
                    'username' => $admin->username ?? 'admin',
                    'email' => $admin->email ?? '',
                    'phone' => '',
                    'department' => 'Administration',
                    'branch' => '',
                    'post' => 'Admin',
                    'avatar' => $admin->avatar
                        ? asset(Admin::AVATAR_UPLOAD_PATH . $admin->avatar)
                        : asset('assets/images/img.png'),
                    'online_status' => '0',
                    'role' => 'admin',
                    'user_type' => 'admin',
                    'is_admin' => '1',
                    'admin' => '1',
                    'directory_type' => 'admin',
                    'source_id' => $admin->id,
                    'conversation_id' => ($conversation && $userId)
                        ? 'employee_admin_' . $userId . '_' . $admin->id
                        : null,
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username,
                    'chat_mode' => 'admin_thread',
                ];
            })
            ->values()
            ->all();
    }

    private function getOrCreateAdminConversation(int $userId, int $adminId): ChatConversation
    {
        if (!$this->supportsPerAdminConversation()) {
            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
            ]);
        }
        try {
            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
                'admin_id' => $adminId,
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
            ]);
        }
    }

    private function supportsPerAdminConversation(): bool
    {
        static $supportsPerAdminConversation = null;

        if ($supportsPerAdminConversation !== null) {
            return $supportsPerAdminConversation;
        }

        $supportsPerAdminConversation = Schema::hasColumn('chat_conversations', 'admin_id');

        return $supportsPerAdminConversation;
    }

    private function updateOnlineStatusBasedOnTodayAttendance()
    {
        $select = ['id'];
        $with = ['employee:id,online_status,company_id',
            'employee.employeeTodayAttendance'
        ];
        try {
            $companyWithEmployee = $this->companyRepo->findOrFailCompanyDetailById(AppHelper::getAuthUserCompanyId(), $select, $with);
            $employeeDetail = $companyWithEmployee?->employee;
            foreach ($employeeDetail as $key => $value){

                $user['user_id'] = $value->id;
                $user['online_status'] = $value->online_status;
                $user['check_in_at'] = $value->employeeTodayAttendance[0]?->check_in_at;
                $user['check_out_at'] = $value->employeeTodayAttendance[0]?->check_out_at;
                if(is_null($user['check_in_at']) && $user['online_status'] == 1){
                    $this->attendanceService->updateUserOnlineStatusToOffline($user['user_id']);
                }
            }
            return true;
        } catch (Exception $exception) {
            AppHelper::sendErrorResponse($exception->getMessage(), 400);
            return;
        }
    }

    public function decodeBase64($b64, $file_folder_name){
        try{
            $bin = base64_decode($b64);
            $size = getImageSizeFromString($bin);
            if (empty($size['mime']) || strpos($size['mime'], 'image/') !== 0) {
                throw new Exception(__('index.invalid_base64_image'));
            }
            $ext = substr($size['mime'], 6);
            if (!in_array($ext, ['png', 'gif', 'jpeg', 'jfif', 'jpg', 'jif'])) {
                return "default.jpeg";
            }
            $path = User::AVATAR_UPLOAD_PATH;
            $fileName = uniqid().$file_folder_name;
            $img_file = $path. '/' . $fileName.'.'.$ext;
            file_put_contents($img_file, $bin);
            return $fileName . '.' . $ext;
        }catch(Exception $e){
            return AppHelper::sendErrorResponse($e->getMessage(),$e->getCode());
        }

    }


    public function storeLocation(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'latitude' => ['required'],
                'longitude' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ],422);
            }
            $validatedData = $validator->validated();


            $userDetail = auth()->user();

            $validatedData['employee_id'] = $userDetail['id'];

            $this->userRepo->setEmployeeLocation($validatedData);

            return AppHelper::sendSuccessResponse('Location successfully sent', []);
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            $this->userRepo->updateUserFcmToken($userDetail, $validator->validated()['fcm_token']);

            return AppHelper::sendSuccessResponse('FCM token updated successfully', []);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

}
