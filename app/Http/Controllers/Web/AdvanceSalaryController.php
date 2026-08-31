<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Exports\AdvanceSalaryExport;
use App\Repositories\CompanyRepository;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\UserRepository;
use App\Requests\GeneralSetting\GeneralSettingRequest;
use App\Requests\Payroll\AdvanceSalary\AdvanceSalaryRequest;
use App\Requests\Payroll\AdvanceSalary\AdvanceSalaryUpdateRequest;
use App\Services\Payroll\AdvanceSalaryService;
use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AdvanceSalaryController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payroll.advanceSalary.';

    public function __construct(
        public AdvanceSalaryService $advanceSalaryService,
        public GeneralSettingRepository $generalSettingRepository,
        protected CompanyRepository $companyRepository,
        protected TelegramService $telegramService,
        protected UserRepository $userRepository
    ){}

    public function index(Request $request)
    {
        $this->authorize('view_advance_salary_list');
        try {

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'status' => $request->status ?? null,
                'month' => $request->month ?? null,
                'search' => trim((string) $request->query('search', '')),
                'per_page' => $request->query('per_page', 25),
            ];
            $select = ['*'];
            $with = [];
            $advanceSalaryRequestLists = $this->advanceSalaryService->getAllAdvanceSalaryDetailPaginated($filterParameters,$select,$with);
            $months = AppHelper::getMonthsList();
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index',compact('advanceSalaryRequestLists','filterParameters','months','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('add_advance_salary');

        try {
            $companyDetail = $this->companyRepository->getCompanyDetail(['id', 'name'], ['branches:id,name']);
            $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(
                ['id', 'name', 'username', 'branch_id', 'department_id'],
                ['branch:id,name', 'department:id,dept_name']
            );
            $filterParameters = [
                'branch_id' => null,
                'department_id' => null,
                'employee_id' => null,
            ];

            return view($this->view . 'create', compact('companyDetail', 'employees', 'filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(AdvanceSalaryRequest $request)
    {
        $this->authorize('add_advance_salary');

        try {
            $validatedData = $request->validated();
            $employeeId = (int) $validatedData['employee_id'];

            if ($this->advanceSalaryService->checkIfEmployeeUnsettledAdvanceSalaryRequestExists($employeeId)) {
                throw new Exception(__('index.advance_salary_pending_error'), 400);
            }

            $advanceDetail = $this->advanceSalaryService->storeByAdmin($validatedData);
            $advanceDetail->loadMissing('requestedBy:id,name');

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.advance_salary_request_alert'),
                __('index.user_submitted_advance_salary_request', [
                    'name' => $advanceDetail->requestedBy->name ?? __('index.employee'),
                    'amount' => $validatedData['requested_amount'],
                ]),
                'advance_salary_alert'
            );

            return redirect()
                ->route('admin.advance-salaries.index')
                ->with('success', __('index.data_created_successfully'));
        } catch (Exception $exception) {
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize('update_advance_salary');
        try{

            $select = ['*'];
            $with = [
                'verifiedBy:id,name',
                'requestedBy:id,name',
                'attachments'
            ];
            $advanceSalaryDetail = $this->advanceSalaryService->findAdvanceSalaryDetailById($id,$with,$select);
            $attachments = $advanceSalaryDetail->attachments;
            return view($this->view.'show',compact('advanceSalaryDetail','attachments'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(AdvanceSalaryUpdateRequest $request,$id)
    {
        $this->authorize('update_advance_salary');
        try {

            $validatedData = $request->validated();
            $advanceSalaryRequestDetail = $this->advanceSalaryService->findAdvanceSalaryDetailById($id);
            $this->advanceSalaryService->advanceSalaryUpdateByAdmin($advanceSalaryRequestDetail,$validatedData);

            $notificationData = [
                'title' => 'Advance Salary '.ucfirst($validatedData['status']),
                'type' => 'Advance Salary',
                'user_id' => [$advanceSalaryRequestDetail->employee_id],
                'description' => 'Your advance salary requested on ' . date('M d Y', strtotime($advanceSalaryRequestDetail->advance_requested_date)) . ' has been ' . ucfirst($validatedData['status']),
                'notification_for_id' => $id,
            ];


            $this->sendAdvanceSalaryStatusNotification($notificationData,$advanceSalaryRequestDetail->employee_id);
            $this->sendAdvanceSalaryApprovedTelegramNotification($advanceSalaryRequestDetail, $validatedData);
            return redirect()->back()->with('success',  __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function quickApprove(Request $request, $id)
    {
        $this->authorize('update_advance_salary');

        try {
            $advanceSalaryRequestDetail = $this->advanceSalaryService->findAdvanceSalaryDetailById($id, ['requestedBy:id,name'], ['*']);
            $validatedData = [
                'status' => 'approved',
                'released_amount' => $advanceSalaryRequestDetail->requested_amount,
                'remark' => $request->input('remark', 'Quick approved from advance salary list.'),
            ];

            $updatedDetail = $this->advanceSalaryService->advanceSalaryUpdateByAdmin($advanceSalaryRequestDetail, $validatedData);
            $updatedDetail->loadMissing('requestedBy:id,name');

            $notificationData = [
                'title' => 'Advance Salary Approved',
                'type' => 'Advance Salary',
                'user_id' => [$advanceSalaryRequestDetail->employee_id],
                'description' => 'Your advance salary requested on ' . date('M d Y', strtotime($advanceSalaryRequestDetail->advance_requested_date)) . ' has been Approved',
                'notification_for_id' => $id,
            ];

            $this->sendAdvanceSalaryStatusNotification($notificationData, $advanceSalaryRequestDetail->employee_id);
            $this->sendAdvanceSalaryApprovedTelegramNotification($advanceSalaryRequestDetail, $validatedData);

            return response()->json([
                'success' => true,
                'message' => __('message.status_changed'),
                'data' => [
                    'id' => $updatedDetail->id,
                    'status' => ucfirst($updatedDetail->status),
                    'status_class' => 'success',
                    'released_amount' => number_format($updatedDetail->released_amount),
                    'released_on' => isset($updatedDetail->amount_granted_date) ? AppHelper::formatDateForView($updatedDetail->amount_granted_date) : 'N/A',
                    'is_paid' => $updatedDetail->is_settled == 1 ? 'Yes' : 'No',
                    'is_paid_class' => $updatedDetail->is_settled ? 'success' : 'warning',
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    private function sendAdvanceSalaryStatusNotification($notificationData,$userId)
    {
        SMPushHelper::sendAdvanceSalaryNotification($notificationData['title'], $notificationData['description'],$userId);
    }

    private function sendAdvanceSalaryApprovedTelegramNotification($advanceSalaryRequestDetail, array $validatedData): void
    {
        if (($validatedData['status'] ?? null) !== 'approved') {
            return;
        }

        $botToken = TelegramBotSettings::botToken();

        if ($botToken === '') {
            Log::warning('Advance salary Telegram notification skipped due to missing Telegram configuration.', [
                'bot_token_saved' => false,
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

        $message = "<b>Advance Salary Approved</b>\n"
            . "Employee: {$employeeName}\n"
            . "Username: {$username}\n"
            . "Phone: {$phone}\n"
            . "Requested Amount: {$requestedAmount}\n"
            . "Released Amount: {$releasedAmount}\n"
            . "Requested Date: {$requestedDate}\n"
            . "Approved By: {$approvedBy}\n"
            . "Remark: {$remark}";

        $this->telegramService->sendToAction(
            \App\Models\TelegramGroup::EVENT_ADVANCE_SALARY_APPROVED,
            $message,
            'HTML'
        );
    }

    public function delete($id)
    {
        $this->authorize('delete_advance_salary');
        try {

            DB::beginTransaction();
            $this->advanceSalaryService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.salary_deleted'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function export(Request $request)
    {
        $this->authorize('view_advance_salary_list');

        $filterParameters = [
            'branch_id' => $request->branch_id ?? null,
            'department_id' => $request->department_id ?? null,
            'employee_id' => $request->employee_id ?? null,
            'status' => $request->status ?? null,
            'month' => $request->month ?? null,
            'search' => trim((string) $request->query('search', '')),
        ];

        $filterParameters = array_filter($filterParameters, fn ($value) => $value !== null && $value !== '');

        $with = [
            'requestedBy:id,name,username,phone,branch_id',
            'requestedBy.branch:id,name',
            'verifiedBy:id,name',
        ];

        $advanceSalaries = $this->advanceSalaryService->getAllAdvanceSalaryDetailForExport($filterParameters, ['*'], $with);

        $fileName = 'advance-salary-export-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new AdvanceSalaryExport($advanceSalaries), $fileName);
    }

    public function copyExport(Request $request): JsonResponse
    {
        $this->authorize('view_advance_salary_list');

        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'status' => $request->status ?? null,
                'month' => $request->month ?? null,
                'search' => trim((string) $request->query('search', '')),
            ];

            $filterParameters = array_filter($filterParameters, fn ($value) => $value !== null && $value !== '');

            $with = [
                'requestedBy:id,name,username,phone,branch_id',
                'requestedBy.branch:id,name',
                'verifiedBy:id,name',
            ];

            $advanceSalaries = $this->advanceSalaryService->getAllAdvanceSalaryDetailForExport($filterParameters, ['*'], $with);
            $rows = $this->buildAdvanceSalaryCopyRows($advanceSalaries);

            if (count($rows) <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.no_records_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'row_count' => count($rows) - 1,
                'text' => collect($rows)
                    ->map(fn ($row) => collect($row)->map(fn ($value) => str_replace(["\t", "\r", "\n"], ' ', (string) $value))->implode("\t"))
                    ->implode("\n"),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    private function buildAdvanceSalaryCopyRows($advanceSalaries): array
    {
        $rows = [[
            'Date',
            'Username+Month-Year',
            'Username',
            'Name',
            'Reason',
            'Branch',
            'Phone Number',
            'Type',
            'Loan Amount',
            'Payment',
            'Approve By',
            'Remark',
            'Month-Year',
        ]];

        foreach ($advanceSalaries as $advanceSalary) {
            $requestedBy = $advanceSalary->requestedBy;
            $verifiedBy = $advanceSalary->verifiedBy;
            $requestedDate = $advanceSalary->advance_requested_date;
            $monthYear = $requestedDate ? date('m-Y', strtotime($requestedDate)) : '';

            $rows[] = [
                $requestedDate ? date('Y-m-d', strtotime($requestedDate)) : '',
                ($requestedBy->username ?? '') . ($monthYear ? '-' . $monthYear : ''),
                $requestedBy->username ?? '',
                $requestedBy->name ?? '',
                trim(strip_tags((string) ($advanceSalary->description ?? ''))),
                $requestedBy?->branch?->name ?? '',
                $requestedBy->phone ?? '',
                'ខ្ចី',
                $advanceSalary->requested_amount ?? 0,
                $advanceSalary->released_amount ?? 0,
                $verifiedBy->name ?? 'Admin',
                $advanceSalary->remark ?? '',
                $monthYear,
            ];
        }

        return $rows;
    }

    /**
     * @throws AuthorizationException
     */
    public function setting()
    {
        $this->authorize('advance_salary_limit');

        try {

            $key = 'advance_salary_limit';
            $advanceSalarySetting = $this->generalSettingRepository->getGeneralSettingByKey($key);
            return view('admin.payrollSetting.advanceSalary.create',compact('advanceSalarySetting'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateSetting(GeneralSettingRequest $request, $id)
    {
        $this->authorize('advance_salary_limit');
        try {
            $validatedData = $request->validated();
            $generalSettingDetail = $this->generalSettingRepository->findOrFailGeneralSettingDetailById($id);

            $this->generalSettingRepository->update($generalSettingDetail, $validatedData);


            return redirect()->back()->with('success', 'Advance Salary Limit Updated Successfully');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

}
