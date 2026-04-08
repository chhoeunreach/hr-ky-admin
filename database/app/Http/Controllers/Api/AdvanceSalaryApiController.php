<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\AdvanceSalaryAttachment;
use App\Requests\Payroll\AdvanceSalary\AdvanceSalaryRequest;
use App\Resources\Payroll\AdvanceSalary\AdvanceSalaryCollection;
use App\Resources\Payroll\AdvanceSalary\AdvanceSalaryResource;
use App\Services\Payroll\AdvanceSalaryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvanceSalaryApiController extends Controller
{
    public function __construct(
        public AdvanceSalaryService $advanceSalaryService,
        public AdvanceSalaryAttachment $advanceSalaryAttachment
    ){}

    public function getEmployeesAdvanceSalaryDetailLists(Request $request)
    {
        try {
            $this->authorize('advance_salary_list');
            $select = ['*'];
            $with = [];
            $advanceSalaryLists = $this->advanceSalaryService->getAllEmployeeAdvanceSalaryListDetail(getAuthUserCode(),$select,$with);

            $data = new AdvanceSalaryCollection($advanceSalaryLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getEmployeeAdvanceSalaryDetailById($id)
    {
        try {
            $this->authorize('advance_salary_list');

            $select= ['*'];
            $with = ['attachments'];
            $detail = $this->advanceSalaryService->findEmployeeAdvanceSalaryDetailByIdAndEmployeeId($id,$with,$select);
            $data = new AdvanceSalaryResource($detail);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function store(AdvanceSalaryRequest $request)
    {
        try {
            $this->authorize('add_advance_salary');

            $permissionKeyForNotification = 'advance_salary_alert';
            $checkEmployeePendingRequests = $this->advanceSalaryService->checkIfEmployeeUnsettledAdvanceSalaryRequestExists(getAuthUserCode());
            if($checkEmployeePendingRequests){
                throw new Exception(__('index.advance_salary_pending_error'),400);
            }
            $validatedData = $request->validated();
            DB::beginTransaction();
                $advanceDetail = $this->advanceSalaryService->store($validatedData);
                $data = new AdvanceSalaryResource($advanceDetail);
            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                __('index.advance_salary_request_alert'),
                __('index.user_submitted_advance_salary_request', [
                    'name' => auth()->user()->name,
                    'amount' => $validatedData['requested_amount']
                ]),
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_created_successfully'),$data);
        }catch(Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateDetail(AdvanceSalaryRequest $request)
    {
        try {
            $this->authorize('update_advance_salary_api');

            $permissionKeyForNotification = 'advance_salary_alert';
            $detail = $this->advanceSalaryService->findEmployeeAdvanceSalaryDetailByIdAndEmployeeId($request->advance_salary_id);
            $validatedData = $request->validated();
            DB::beginTransaction();
              $updateDetail = $this->advanceSalaryService->update($detail,$validatedData);
            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                __('index.advance_salary_request_alert'),
                __('index.user_updated_advance_salary_request', ['name' => auth()->user()->name]),
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_updated_successfully'),
                new AdvanceSalaryResource($updateDetail)
            );
        }catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

}
