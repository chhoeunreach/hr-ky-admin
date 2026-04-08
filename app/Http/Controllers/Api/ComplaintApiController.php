<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\ComplaintResponse;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Resources\Complaint\ComplaintResource;
use App\Resources\Complaint\DepartmentResource;
use App\Services\Complaint\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class ComplaintApiController extends Controller
{

    public function __construct(protected ComplaintService $complaintService, protected UserRepository $userRepository, protected DepartmentRepository $departmentRepository)
    {
    }


    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function saveComplaintResponse(Request $request, $complaintId): JsonResponse
    {
        try {

            $user = auth()->user();
            $userId = $user->id;
            $checkResponse = ComplaintResponse::where([['employee_id', $userId], ['complaint_id', $complaintId]])->exists();

            if ($checkResponse) {
                throw new Exception(__('index.complaint_exist_error'), 404);
            }

            $validator = Validator::make($request->all(), [
                'message' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            $validatedData = $validator->validated();

            $validatedData['employee_id'] = $userId;

            DB::beginTransaction();
            $complaintDetail = $this->complaintService->saveComplaintResponse($validatedData, $complaintId);
            DB::commit();

            if ($complaintDetail) {
                $employee = $this->userRepository->findUserDetailById($complaintDetail['created_by'], ['id', 'name']);

                $notificationTitle = __('index.complaint_notification');
                $notificationMessage = __('index.complaint_submit', [
                    'subject' =>$complaintDetail['subject'],
                    'date' => AppHelper::formatDateForView($complaintDetail['complaint_date']),
                ]);

                SMPushHelper::sendComplaintResponseNotification($notificationTitle, $notificationMessage, $employee->id);

            }
            return AppHelper::sendSuccessResponse(__('index.response_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function saveComplaint(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $userId = $user->id;
            $branchId = $user->branch_id;

            $validator = Validator::make($request->all(), [
                'department_id' => 'required|array|min:1',
                'department_id.*' => 'required|exists:departments,id',
                'employee_id' => 'required|array|min:1',
                'employee_id.*' => 'required|exists:users,id',
                'message' => ['nullable'],
                'subject' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            $validatedData = $validator->validated();

            if(in_array($userId, $validatedData['employee_id'])){
                throw new Exception('You cannot Complain yourself.', 400);
            }

            $validatedData['branch_id'] = $branchId;
            $validatedData['complaint_date'] = now();
            $validatedData['complaint_from'] = $userId;

            DB::beginTransaction();
            $complaintDetail = $this->complaintService->saveComplaintDetail($validatedData);
            DB::commit();

            if ($complaintDetail) {

                $message = 'A formal complaint (#' . $complaintDetail['id'] . ') has been filed against you regarding ' . ucfirst($complaintDetail['subject']) .
                    '. Please review and respond as early as possible through your account dashboard.';

                SMPushHelper::sendComplaintNotification('Complaint Notification', $message, $validatedData['employee_id']);

            }
            return AppHelper::sendSuccessResponse(__('index.complaint_submitted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getAllComplaints(Request $request)
    {
        try {
            $select = ['*'];
            $with = [
                'complaintReply' => function ($query) {
                    $query->where('employee_id', getAuthUserCode());
                }
            ];
            $perPage = $request->get('per_page') ?? 20;
            $complaintDetail = $this->complaintService->getApiComplaint($perPage, $select, $with);

            $data = ComplaintResource::collection($complaintDetail);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getDepartmentEmployees()
    {
        try {
            $user = auth()->user();
            $branchId = $user->branch_id;

            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId, ['employees'], ['id', 'dept_name']);


            $data = DepartmentResource::collection($departments);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
