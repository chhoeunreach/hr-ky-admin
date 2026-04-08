<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
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
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserProfileApiController extends Controller
{

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
            $with = ['employee'];
//            $updateOnline = $this->updateOnlineStatusBasedOnTodayAttendance();
//            if($updateOnline){
                $companyWithEmployee = $this->companyRepo
                    ->findOrFailCompanyDetailById(AppHelper::getAuthUserCompanyId(), $select, $with);
                $companyDetail = new CompanyResource($companyWithEmployee);

                $branches = $this->branchRepository->getBranchesWithDepartments();
                $data = [
                    'companyDetail'=> $companyDetail,
                    'branches' => $branches
                ];

//            }
            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getFile(), 400);
        }
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

}


