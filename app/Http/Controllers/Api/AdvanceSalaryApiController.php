<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\AdvanceSalaryAttachment;
use App\Requests\Payroll\AdvanceSalary\AdvanceSalaryRequest;
use App\Requests\Payroll\AdvanceSalary\AdvanceSalaryUpdateRequest;
use App\Resources\Payroll\AdvanceSalary\AdvanceSalaryCollection;
use App\Resources\Payroll\AdvanceSalary\AdvanceSalaryResource;
use App\Services\Payroll\AdvanceSalaryService;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AdvanceSalaryApiController extends Controller
{
    use CustomAuthorizesRequests;
    public function __construct(
        public AdvanceSalaryService $advanceSalaryService,
        public AdvanceSalaryAttachment $advanceSalaryAttachment,
        public TelegramService $telegramService
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

            $this->sendAdvanceSalaryRequestTelegramNotification($advanceDetail);

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

    public function updateStatus(AdvanceSalaryUpdateRequest $request, $id)
    {
        try {
            $this->authorize('update_advance_salary');

            $validatedData = $request->validated();
            $advanceSalaryRequestDetail = $this->advanceSalaryService->findAdvanceSalaryDetailById($id);
            $updatedDetail = $this->advanceSalaryService->advanceSalaryUpdateByAdmin($advanceSalaryRequestDetail, $validatedData);

            $notificationData = [
                'title' => 'Advance Salary ' . ucfirst($validatedData['status']),
                'type' => 'Advance Salary',
                'user_id' => [$advanceSalaryRequestDetail->employee_id],
                'description' => 'Your advance salary requested on ' . date('M d Y', strtotime($advanceSalaryRequestDetail->advance_requested_date)) . ' has been ' . ucfirst($validatedData['status']),
                'notification_for_id' => $id,
            ];

            $this->sendAdvanceSalaryStatusNotification($notificationData, $advanceSalaryRequestDetail->employee_id);
            $this->sendAdvanceSalaryApprovedTelegramNotification($advanceSalaryRequestDetail, $validatedData);

            return AppHelper::sendSuccessResponse(
                __('message.status_changed'),
                new AdvanceSalaryResource($updatedDetail)
            );
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            if (!Gate::allows('advance-salary-approve')) {
                return AppHelper::sendErrorResponse('Forbidden', 403);
            }

            $validated = $request->validate([
                'released_amount' => ['required', 'numeric', 'min:0'],
                'remark' => ['nullable', 'string'],
            ]);

            $detail = $this->advanceSalaryService->approveByApprover(
                (int) $id,
                (float) $validated['released_amount'],
                $validated['remark'] ?? null,
                getAuthUserCode()
            );

            return AppHelper::sendSuccessResponse(
                __('message.status_changed'),
                new AdvanceSalaryResource($detail)
            );
        } catch (ValidationException $validationException) {
            return AppHelper::sendErrorResponse(
                $validationException->getMessage(),
                422,
                $validationException->errors()
            );
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            if (!Gate::allows('advance-salary-approve')) {
                return AppHelper::sendErrorResponse('Forbidden', 403);
            }

            $validated = $request->validate([
                'remark' => ['nullable', 'string'],
            ]);

            $detail = $this->advanceSalaryService->rejectByApprover(
                (int) $id,
                $validated['remark'] ?? null,
                getAuthUserCode()
            );

            return AppHelper::sendSuccessResponse(
                __('message.status_changed'),
                new AdvanceSalaryResource($detail)
            );
        } catch (ValidationException $validationException) {
            return AppHelper::sendErrorResponse(
                $validationException->getMessage(),
                422,
                $validationException->errors()
            );
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getAdminAdvanceSalaryLists(Request $request)
    {
        try {
            $this->authorize('advance_salary_list');
            $select = ['*'];
            $with = [];
            $filterParameters = [];
            $advanceSalaryLists = $this->advanceSalaryService->getAllAdvanceSalaryDetailPaginated($filterParameters,$select,$with);
            $data = new AdvanceSalaryCollection($advanceSalaryLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function adminStore(AdvanceSalaryRequest $request)
    {
        return $this->store($request);
    }

    public function approveByBody(Request $request)
    {
        try {
            $request->validate([
                'advance_salary_id' => ['required', 'integer', 'exists:advance_salaries,id'],
                'released_amount' => ['required', 'numeric', 'min:0'],
                'remark' => ['nullable', 'string'],
            ]);
            return $this->approve($request, $request->advance_salary_id);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectByBody(Request $request)
    {
        try {
            $request->validate([
                'advance_salary_id' => ['required', 'integer', 'exists:advance_salaries,id'],
                'remark' => ['nullable', 'string'],
            ]);
            return $this->reject($request, $request->advance_salary_id);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    private function sendAdvanceSalaryStatusNotification($notificationData, $userId): void
    {
        SMPushHelper::sendAdvanceSalaryNotification(
            $notificationData['title'],
            $notificationData['description'],
            $userId
        );
    }

    private function sendAdvanceSalaryApprovedTelegramNotification($advanceSalaryRequestDetail, array $validatedData): void
    {
        if (($validatedData['status'] ?? null) !== 'approved') {
            return;
        }

        $botToken = (string) config('services.telegram.bot_token', '');

        if ($botToken === '') {
            Log::warning('Advance salary Telegram notification skipped due to missing Telegram configuration.', [
                'bot_token_configured' => $botToken !== '',
                'advance_salary_id' => $advanceSalaryRequestDetail->id ?? null,
            ]);
            return;
        }

        $advanceSalaryRequestDetail->loadMissing('requestedBy:id,name,username,phone');

        $employee = $advanceSalaryRequestDetail->requestedBy;
        $employeeName = htmlspecialchars((string) ($employee->name ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $username = htmlspecialchars((string) ($employee->username ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phone = htmlspecialchars((string) ($employee->phone ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $requestedAmount = number_format((float) ($advanceSalaryRequestDetail->requested_amount ?? 0), 2);
        $releasedAmount = number_format((float) ($validatedData['released_amount'] ?? $advanceSalaryRequestDetail->released_amount ?? 0), 2);
        $requestedDate = isset($advanceSalaryRequestDetail->advance_requested_date)
            ? date('M d, Y', strtotime($advanceSalaryRequestDetail->advance_requested_date))
            : 'N/A';
        $approvedBy = htmlspecialchars((string) (auth('admin')->user()?->name ?? auth()->user()?->name ?? 'Admin'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $remark = htmlspecialchars((string) ($validatedData['remark'] ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $message = "<b>បានអនុម័តការខ្ចីប្រាក់ខែ</b>\n"
            . "បុគ្គលិក: {$employeeName}\n"
            . "Username: {$username}\n"
            . "លេខទូរស័ព្ទ: {$phone}\n"
            . "ចំនួនស្នើសុំ: {$requestedAmount}\n"
            . "ចំនួនអនុម័ត/បង់ចេញ: {$releasedAmount}\n"
            . "កាលបរិច្ឆេទស្នើសុំ: {$requestedDate}\n"
            . "អនុម័តដោយ: {$approvedBy}\n"
            . "កំណត់សម្គាល់: {$remark}";

        $this->telegramService->sendToAction(
            \App\Models\TelegramGroup::EVENT_ADVANCE_SALARY_APPROVED,
            $message,
            'HTML'
        );
    }

    private function sendAdvanceSalaryRequestTelegramNotification($advanceSalaryRequestDetail): void
    {
        $botToken = (string) config('services.telegram.bot_token', '');

        if ($botToken === '') {
            Log::warning('Advance salary request Telegram notification skipped due to missing Telegram configuration.', [
                'bot_token_configured' => $botToken !== '',
                'advance_salary_id' => $advanceSalaryRequestDetail->id ?? null,
            ]);
            return;
        }

        $advanceSalaryRequestDetail->loadMissing('requestedBy:id,name,username,phone');

        $employee = $advanceSalaryRequestDetail->requestedBy;
        $employeeName = htmlspecialchars((string) ($employee->name ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $username = htmlspecialchars((string) ($employee->username ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phone = htmlspecialchars((string) ($employee->phone ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $requestedAmount = number_format((float) ($advanceSalaryRequestDetail->requested_amount ?? 0), 2);
        $requestedDate = isset($advanceSalaryRequestDetail->advance_requested_date)
            ? date('M d, Y', strtotime($advanceSalaryRequestDetail->advance_requested_date))
            : 'N/A';

        $plainDescription = trim((string) Str::of((string) ($advanceSalaryRequestDetail->description ?? ''))->stripTags());
        $description = htmlspecialchars($plainDescription !== '' ? $plainDescription : 'N/A', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $message = "<b>សំណើខ្ចីប្រាក់ខែ</b>\n"
            . "បុគ្គលិក: {$employeeName}\n"
            . "Username: {$username}\n"
            . "លេខទូរស័ព្ទ: {$phone}\n"
            . "ចំនួនស្នើសុំ: {$requestedAmount}\n"
            . "កាលបរិច្ឆេទស្នើសុំ: {$requestedDate}\n"
            . "មូលហេតុ: {$description}";

        $this->telegramService->sendToAction(
            \App\Models\TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST,
            $message,
            'HTML'
        );
    }

}
