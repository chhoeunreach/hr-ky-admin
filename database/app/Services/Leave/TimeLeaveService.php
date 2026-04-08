<?php

namespace App\Services\Leave;

use App\Helpers\AppHelper;
use App\Models\OfficeTime;
use App\Repositories\TimeLeaveRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class TimeLeaveService
{

    public function __construct(protected TimeLeaveRepository $timeLeaveRepository, protected UserRepository $userRepository)
    {}

    public function getAllEmployeeLeaveRequests($filterParameters, $select=['*'], $with=[])
    {

        if(AppHelper::ifDateInBsEnabled()){
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['year'],$filterParameters['month']);
            $filterParameters['start_date'] = $dateInAD['start_date'];
            $filterParameters['end_date'] = $dateInAD['end_date'];
        }
        $filterParameters['requestd_by'] = auth()->user()->id;

        return $this->timeLeaveRepository->getAllEmployeeTimeLeaveRequest($filterParameters,$select,$with);

    }
    public function getAllTimeLeaveRequestOfEmployee($filterParameters)
    {

        if(AppHelper::ifDateInBsEnabled()){
            $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
            $month = isset($filterParameters['month']) ? $nepaliDate['month']: '';
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($nepaliDate['year'],$month);
            $filterParameters['start_date'] = $dateInAD['start_date'];
            $filterParameters['end_date'] = $dateInAD['end_date'];
        }
        return $this->timeLeaveRepository->getAllTimeLeaveRequestDetailOfEmployee($filterParameters);

    }

    public function findEmployeeTimeLeaveRequestById($leaveRequestId, $select=['*'])
    {

        return $this->timeLeaveRepository->findEmployeeLeaveRequestByEmployeeId($leaveRequestId,$select);

    }

    public function findTimeLeaveRequestReasonById($leaveRequestId)
    {

        return $this->timeLeaveRepository->findLeaveRequestReasonByEmployeeId($leaveRequestId);

    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function storeTimeLeaveRequest($validatedData)
    {
        $user = $this->userRepository->findUserDetailById($validatedData['requested_by'], ['office_time_id']);
        $shift = OfficeTime::where('id', $user->office_time_id)->first();
        if ($shift) {

            if (isset($validatedData['leave_from']) && (strtotime($validatedData['leave_from']) < strtotime($shift['opening_time']))) {
                throw new Exception(__('message.leave_start_time_error'), 400);
            }

            if (isset($validatedData['leave_to']) && (strtotime($validatedData['leave_to']) > strtotime($shift['closing_time']))) {
                throw new Exception(__('message.leave_end_time_error'), 400);

            }
        }


        if (strtotime(date('Y-m-d')) == strtotime($validatedData['issue_date'])) {

            $startTime = $validatedData['leave_from'] ?? $shift['opening_time'];

            $endTime = $validatedData['leave_to'] ?? $shift['closing_time'];
        } else {

            $startTime = $validatedData['leave_from'];
            $endTime = $validatedData['leave_to'];
        }
        $validatedData['start_time'] = date("H:i", strtotime($startTime));
        $validatedData['end_time'] = date("H:i", strtotime($endTime));


        $this->checkExistingLeaveRequest($validatedData);



        $this->timeLeaveRepository->store($validatedData);

        return $validatedData;

    }

    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    private function checkExistingLeaveRequest($validatedData): void
    {
        $date = date('Y-m-d', strtotime($validatedData['issue_date']));

        $employeeLatestPendingLeaveRequest = $this->timeLeaveRepository->getEmployeeLatestTimeLeave($date);
        if($employeeLatestPendingLeaveRequest){
            throw new Exception(__('message.leave_pending_error',['status'=>$employeeLatestPendingLeaveRequest->status]),400);
        }

    }

    public function cancelLeaveRequest($validatedData, $leaveRequestDetail)
    {

        DB::beginTransaction();
        $this->timeLeaveRepository->update($leaveRequestDetail,$validatedData);
        DB::commit();
        return $leaveRequestDetail;

    }

    /**
     * @throws Exception
     */
    public function updateLeaveRequestStatus($validatedData, $leaveRequestId)
    {
        $leaveRequestDetail = $this->findEmployeeTimeLeaveRequestById($leaveRequestId);
        if(!$leaveRequestDetail){
            throw new \Exception(__('message.leave_request_not_found'),404);
        }
        $validatedData['request_updated_by'] = auth()->user()->id;

        $this->timeLeaveRepository->update($leaveRequestDetail,$validatedData);

        return $leaveRequestDetail;

    }


    public function getTimeLeaveCountDetailOfEmployeeOfTwoMonth()
    {
        $allLeaveRequest = $this->timeLeaveRepository->getLeaveCountDetailOfEmployeeOfTwoMonth();

        if($allLeaveRequest){

            $dateWithNumberOfEmployeeOnLeave = [];
            foreach ($allLeaveRequest as $leave) {
                $data = [
                    'date' => $leave->issue_date,
                    'leave_count' => $leave->leave_count,
                ];

                $dateWithNumberOfEmployeeOnLeave[] = $data;
            }
            return $dateWithNumberOfEmployeeOnLeave;
        }

    }

    public function getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter)
    {

        return $this->timeLeaveRepository->getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter);

    }

}
