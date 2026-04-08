<?php

namespace App\Http\Controllers\Api;

use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequestMaster;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveRequestStoreRequest;
use App\Requests\Leave\TimeLeaveStoreApiRequest;
use App\Requests\Leave\TimeLeaveStoreRequest;
use App\Resources\award\RecentAwardResource;
use App\Resources\Leave\EmployeeLeaveDetailCollection;
use App\Resources\Leave\EmployeeTimeLeaveDetailCollection;
use App\Resources\Leave\LeaveRequestCollection;
use App\Resources\User\BirthdayCollection;
use App\Resources\User\BirthdayResource;
use App\Resources\User\HolidayResource;
use App\Services\Holiday\HolidayService;
use App\Services\Leave\LeaveService;
use App\Services\Leave\TimeLeaveService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\Rule;

class LeaveApiController extends Controller
{

    public function __construct(protected LeaveService $leaveService, protected TimeLeaveService $timeLeaveService, protected HolidayService $holidayService, protected UserRepository $userRepository)
    {}

    public function getAllLeaveRequestOfEmployee(Request $request): JsonResponse
    {
        try{
            $filterParameter = [
                'leave_type' => $request->leave_type ?? null,
                'status' => $request->status ?? null,
                'year' => $request->year ?? \Carbon\Carbon::now()->year,
                'month' => $request->month ?? null,
                'early_exit' => $request->early_exit ?? null,
                'user_id' => getAuthUserCode()
            ];
            $getAllLeaveRequests =  $this->leaveService->getAllLeaveRequestOfEmployee($filterParameter);
            $timeLeaveRequests = $this->timeLeaveService->getAllTimeLeaveRequestOfEmployee($filterParameter);

            if( isset($request) && ($request->leave_type == '' || $request->leave_type == 0)){
                $getAllLeaveRequests = collect($getAllLeaveRequests);
                $timeLeaveRequests = collect($timeLeaveRequests);
                $mergedCollection = $getAllLeaveRequests->merge($timeLeaveRequests);
            }else{
                $mergedCollection = $getAllLeaveRequests;
            }

            $leaveData = new LeaveRequestCollection($mergedCollection);

            return AppHelper::sendSuccessResponse(__('index.data_found'),$leaveData);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function saveLeaveRequestDetail(LeaveRequestStoreRequest $request): JsonResponse
    {
        try {
            $this->authorize('leave_request_create');
            $permissionKeyForNotification = 'employee_leave_request';

            $validatedData = $request->validated();

            $validatedData['requested_by'] = getAuthUserCode();
            DB::beginTransaction();
              $leaveRequestDetail = $this->leaveService->storeLeaveRequest($validatedData);
            DB::commit();

            if($leaveRequestDetail) {
                $notificationTitle = __('index.leave_request_notification');
                $notificationMessage = __('index.leave_request_submit', [
                    'name' => ucfirst(auth()->user()->name),
                    'no_of_days' => $leaveRequestDetail['no_of_days'],
                    'leave_from' => AppHelper::formatDateForView($leaveRequestDetail['leave_from']),
                    'leave_requested_date' => AppHelper::convertLeaveDateFormat($leaveRequestDetail['leave_requested_date']),
                    'reasons' => $validatedData['reasons']
                ]);
                AppHelper::sendNotificationToAuthorizedUser(
                    $notificationTitle,
                    $notificationMessage,
                    $permissionKeyForNotification
                );
            }
            return AppHelper::sendSuccessResponse(__('index.leave_request_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getLeaveCountDetailOfEmployeeOfTwoMonth(): JsonResponse
    {
        try {

            $dateWithNumberOfEmployeeOnLeave = $this->leaveService->getLeaveCountDetailOfEmployeeOfTwoMonth();
            $timeLeaveCount = $this->timeLeaveService->getTimeLeaveCountDetailOfEmployeeOfTwoMonth();
            $leaveCalendar = array_merge($dateWithNumberOfEmployeeOnLeave, $timeLeaveCount);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$leaveCalendar);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Deprecated Don't use this now
     */
    public function getAllEmployeeLeaveDetailBySpecificDay(Request $request): JsonResponse
    {
        try {
            $filterParameter['leave_date'] = $request->leave_date ?? Carbon::now()->format('Y-m-d') ;

            $leaveListDetail = $this->leaveService->getAllEmployeeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveListDetail = $this->timeLeaveService->getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveDetail = new EmployeeTimeLeaveDetailCollection($timeLeaveListDetail);
            $leaveDetail = new EmployeeLeaveDetailCollection($leaveListDetail);
            $leaveData = $timeLeaveDetail->concat($leaveDetail);


            return AppHelper::sendSuccessResponse(__('index.data_found'),$leaveData);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

      public function getCalendarDetailBySpecificDay(Request $request): JsonResponse
    {
        try {
            $filterParameter['leave_date'] = $request->leave_date ?? Carbon::now()->format('Y-m-d') ;

            $leaveListDetail = $this->leaveService->getAllEmployeeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveListDetail = $this->timeLeaveService->getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter);
            $timeLeaveDetail = new EmployeeTimeLeaveDetailCollection($timeLeaveListDetail);
            $leaveDetail = new EmployeeLeaveDetailCollection($leaveListDetail);
            $leaveData = $timeLeaveDetail->concat($leaveDetail);

//            $holidaySelect = ['id','event','event_date','note','is_public_holiday'];
            $holiday = $this->holidayService->getHolidayByDate($filterParameter['leave_date']);
            $withBirthday = ['post'];
            $birthdays =  $this->userRepository->getBirthdayUsers($filterParameter['leave_date'],$withBirthday);

            if (isset($holiday)) {
                $holidayData = new HolidayResource($holiday);

            } else {
                $holidayData = null;

            }

            $birthdayData = new BirthdayCollection($birthdays);
            $data = [
                'leaves'=>$leaveData,
                'holiday'=> $holidayData,
                'birthdays'=>$birthdayData
            ];

            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function cancelLeaveRequest($leaveRequestId)
    {
        try {
            $validatedData = [
                'status' => 'cancelled'
            ];
            $leaveRequestDetail = $this->leaveService->findLeaveRequestDetailByIdAndEmployeeId($leaveRequestId,getAuthUserCode());
            if($leaveRequestDetail->status != 'pending'){
                throw new \Exception(__('index.leave_request_cannot_be_cancelled'),403);
            }
            $this->leaveService->cancelLeaveRequest($validatedData, $leaveRequestDetail);
            return AppHelper::sendSuccessResponse(__('index.leave_request_cancelled_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function saveTimeLeaveRequest(TimeLeaveStoreApiRequest $request): JsonResponse
    {
        try {
//            $this->authorize('leave_request_create');
            $permissionKeyForNotification = 'employee_leave_request';

            $validatedData = $request->validated();

            $validatedData['requested_by'] = getAuthUserCode();

            DB::beginTransaction();
            $leaveRequestDetail = $this->timeLeaveService->storeTimeLeaveRequest($validatedData);
            DB::commit();

            if ($leaveRequestDetail) {
                $notificationTitle = __('index.leave_request_notification');
                $notificationMessage = __('index.leave_request_submitted', [
                    'name' => ucfirst(auth()->user()?->name),
                    'start_time' => $leaveRequestDetail['start_time'],
                    'end_time' => $leaveRequestDetail['end_time'],
                    'issue_date' => AppHelper::convertLeaveDateFormat($leaveRequestDetail['issue_date']),
                    'reasons' => $validatedData['reasons']
                ]);
                AppHelper::sendNotificationToAuthorizedUser(
                    $notificationTitle,
                    $notificationMessage,
                    $permissionKeyForNotification
                );
            }
            return AppHelper::sendSuccessResponse(__('index.leave_request_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function cancelTimeLeaveRequest($leaveRequestId)
    {
        try {
            $validatedData = [
                'status' => LeaveStatusEnum::cancelled->value
            ];
            $leaveRequestDetail = $this->timeLeaveService->findEmployeeTimeLeaveRequestById($leaveRequestId);
            if($leaveRequestDetail->status != LeaveStatusEnum::pending->value){
                throw new \Exception( __('index.leave_request_cannot_be_cancelled'),403);
            }
            $this->timeLeaveService->cancelLeaveRequest($validatedData, $leaveRequestDetail);
            return AppHelper::sendSuccessResponse(__('index.leave_request_cancelled'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
