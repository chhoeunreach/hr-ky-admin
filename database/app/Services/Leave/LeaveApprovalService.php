<?php

namespace App\Services\Leave;

use App\Enum\LeaveApproverEnum;
use App\Models\LeaveApprovalDepartment;
use App\Repositories\LeaveApprovalRepository;
use Exception;

class LeaveApprovalService
{
    public function __construct(
        protected LeaveApprovalRepository $approvalRepository
    ){}

    public function getAllLeaveApprovalPaginated($select= ['*'], $with=[])
    {

        return $this->approvalRepository->getAll($select,$with);
    }

    /**
     * @throws Exception
     */
    public function findLeaveApprovalById($id, $select=['*'], $with=[])
    {
        return $this->approvalRepository->find($id,$select,$with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveLeaveApprovalDetail($validatedData)
    {

        $departmentIds = $validatedData['department_id'];
        $leaveTypeId = $validatedData['leave_type_id'];

        $check = $this->approvalRepository->checkLeaveAndDepartment($leaveTypeId, $departmentIds);

        if($check){
            throw new Exception('Leave Approval with selected leave type and departments already exists.', 400);
        }

        $approvalData = $this->getLeaveApprovalData($validatedData);

        $validatedData['max_days_limit'] = $validatedData['max_days_limit'] ?? 0;
        $leaveApprovalDetail = $this->approvalRepository->store($validatedData);

        $this->approvalRepository->saveApprovalDepartment($leaveApprovalDetail,$approvalData['department']);
//        $this->approvalRepository->saveApprovalRole($leaveApprovalDetail,$approvalData['role']);
//        $this->approvalRepository->saveNotificationReceiver($leaveApprovalDetail,$approvalData['recipients']);
        $this->approvalRepository->saveApprovalProcess($leaveApprovalDetail,$approvalData['process']);

        return $leaveApprovalDetail;

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateLeaveApprovalDetail($id, $validatedData)
    {

        $departmentIds = $validatedData['department_id'];
        $leaveTypeId = $validatedData['leave_type_id'];

        $check = $this->approvalRepository->checkExistingLeaveAndDepartment($id, $leaveTypeId, $departmentIds);

        if($check){
            throw new Exception('Leave Approval with selected leave type and departments already exists.', 400);
        }

        $leaveApprovalDetail = $this->findLeaveApprovalById($id);
        $approvalData = $this->getLeaveApprovalData($validatedData);

        $this->approvalRepository->update($leaveApprovalDetail, $validatedData);

        $this->approvalRepository->updateApprovalDepartment($leaveApprovalDetail,$approvalData['department']);
//        $this->approvalRepository->updateApprovalRole($leaveApprovalDetail,$approvalData['role']);
//        $this->approvalRepository->updateNotificationReceiver($leaveApprovalDetail,$approvalData['recipients']);
        $this->approvalRepository->updateApprovalProcess($leaveApprovalDetail,$approvalData['process']);
        return $approvalData;

    }

    /**
     * @throws Exception
     */
    public function deleteLeaveApproval($id)
    {
        $leaveApprovalDetail = $this->findLeaveApprovalById($id);
        return $this->approvalRepository->delete($leaveApprovalDetail);
    }

    /**
     * @throws Exception
     */
    private function getLeaveApprovalData($validatedData): array
    {
        $departmentArray = [];
        $roleArray = [];
        $recipientArray = [];
        $processArray = [];
        foreach ($validatedData['department_id'] as $key => $department){
            $departmentArray[$key]['department_id'] = $department;
        }
//        foreach ($validatedData['role_id'] as $key => $value){
//            $roleArray[$key]['role_id'] = $value;
//        }
//        foreach ($validatedData['notification_recipient'] as $key => $value){
//            $recipientArray[$key]['user_id'] = $value;
//        }



        $userIdIndex = 0;
        foreach ($validatedData['approver'] as $key => $approverValue) {
            $processArray[$key]['approver'] = $approverValue;

            if ($approverValue == LeaveApproverEnum::specific_personnel->value ) {

                if(isset($validatedData['role_id'][$userIdIndex])){
                    $processArray[$key]['role_id'] = $validatedData['role_id'][$userIdIndex];
                }
                if(isset($validatedData['user_id'][$userIdIndex])){
                    $processArray[$key]['user_id'] = $validatedData['user_id'][$userIdIndex];
                }

                $userIdIndex++;
            } else {
                $processArray[$key]['role_id'] = null;
                $processArray[$key]['user_id'] = null;
            }
        }

        $data['department'] = $departmentArray;
        $data['role'] = $roleArray;
        $data['recipients'] = $recipientArray;
        $data['process'] = $processArray;
        return $data;
    }

    /**
     * @throws Exception
     */
    public function changeStatus($leaveApprovalId)
    {
        $leaveApprovalDetail = $this->findLeaveApprovalById($leaveApprovalId);
        return $this->approvalRepository->toggleStatus($leaveApprovalDetail);
    }



}
