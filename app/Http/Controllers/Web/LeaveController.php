<?php

namespace App\Http\Controllers\Web;

use App\Exports\LeaveRequestListExport;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequestMaster;

use App\Repositories\CompanyRepository;
use App\Repositories\LeaveRequestApprovalRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveRequestAdd;
use App\Requests\Leave\LeaveRequestStoreFromWeb;

use App\Services\Leave\LeaveService;
use App\Services\Notification\NotificationService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class LeaveController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.leaveRequest.';

    public function __construct(protected LeaveService $leaveService, protected LeaveTypeRepository $leaveTypeRepo, protected NotificationService $notificationService,
                                protected UserRepository $userRepository, protected LeaveRequestApprovalRepository $requestApprovalRepository, protected CompanyRepository $companyRepository)
    {}


    public function index(Request $request)
    {
        if (auth('admin')->user() || Gate::allows('list_leave_request') || Gate::allows('access_admin_leave')) {
            try {
                $filterParameters = $this->buildLeaveRequestFilters($request);
                $months = AppHelper::MONTHS;
                $with = ['leaveType:id,name', 'leaveRequestedBy:id,name', 'branch:id,name', 'department:id,dept_name', 'requestApproval'];
                $select = ['leave_requests_master.*'];
                $leaveDetails = $this->leaveService->getAllEmployeeLeaveRequests($filterParameters,$select, $with);

                $with = ['branches:id,name'];
                $select = ['id', 'name'];
                $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

                return view($this->view . 'index',
                    compact('leaveDetails', 'filterParameters','months','companyDetail') );
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }

        } else {
            abort(403); // Unauthorized
        }

    }

    public function export(Request $request)
    {
        if (auth('admin')->user() || Gate::allows('list_leave_request') || Gate::allows('access_admin_leave')) {
            try {
                $filterParameters = $this->buildLeaveRequestFilters($request);

                if (!$request->filled('month')) {
                    if (AppHelper::ifDateInBsEnabled()) {
                        $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                        $filterParameters['month'] = $nepaliDate['month'];
                        $filterParameters['year'] = $request->filled('year') ? $request->year : $nepaliDate['year'];
                    } else {
                        $filterParameters['month'] = Carbon::now()->format('m');
                    }
                }

                $with = ['leaveType:id,name,leave_allocated', 'leaveRequestedBy:id,name,employee_code,username', 'leaveRequestUpdatedBy:id,name'];
                $select = ['leave_requests_master.*'];
                $leaveDetails = $this->leaveService->getAllEmployeeLeaveRequestsForExport($filterParameters, $select, $with);

                return Excel::download(new LeaveRequestListExport($leaveDetails), 'leave-request-list.xlsx');
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        }

        abort(403);
    }

    public function copyExport(Request $request): JsonResponse
    {
        if (auth('admin')->user() || Gate::allows('list_leave_request') || Gate::allows('access_admin_leave')) {
            try {
                $filterParameters = $this->buildLeaveRequestFilters($request);

                if (!$request->filled('month')) {
                    if (AppHelper::ifDateInBsEnabled()) {
                        $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                        $filterParameters['month'] = $nepaliDate['month'];
                        $filterParameters['year'] = $request->filled('year') ? $request->year : $nepaliDate['year'];
                    } else {
                        $filterParameters['month'] = Carbon::now()->format('m');
                    }
                }

                $with = ['leaveType:id,name,leave_allocated', 'leaveRequestedBy:id,name,employee_code,username', 'leaveRequestUpdatedBy:id,name'];
                $select = ['leave_requests_master.*'];
                $leaveDetails = $this->leaveService->getAllEmployeeLeaveRequestsForExport($filterParameters, $select, $with);
                $rows = $this->buildLeaveRequestCopyRows($leaveDetails);

                if (count($rows) <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => __('index.no_records_found'),
                    ], 404);
                }

                return response()->json([
                    'success' => true,
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

        abort(403);
    }

    private function buildLeaveRequestCopyRows($leaveDetails): array
    {
        $rows = [[
            'ថ្ងៃខែឆ្នាំ',
            'លេខសម្គាល់',
            'ឈ្មោះ',
            'ប្រភេទឈប់',
            'Paid/Unpaid',
            'រយៈពេល (ថ្ងៃ)',
            'ចាប់ពីថ្ងៃ',
            'ដលថ្ងៃ',
            'ផ្សេង',
            'ស្ថានភាព',
            'អនុញ្ញាតិដោយ',
            'ផ្សេងៗ',
            'ថ្ងៃខែស្នើរ',
            'ថ្ងៃខែអនុញ្ញាតិ',
            'ខែ-ឆ្នាំ',
            'សម្គាល់-ខែ-ឆ្នាំ',
        ]];

        foreach ($leaveDetails as $leave) {
            $requestedDate = $leave->leave_requested_date ? date('d-m-Y', strtotime($leave->leave_requested_date)) : '';
            $username = $leave->leaveRequestedBy?->username ?? '';
            $employeeName = $leave->leaveRequestedBy?->name ?? 'N/A';
            $leaveTypeName = $leave->leaveType?->name ?? '';
            $isPaid = !is_null($leave->leaveType?->leave_allocated);
            $paidStatus = $leave->status === 'rejected' ? 0 : ($isPaid ? 1 : 2);
            $approvedBy = $leave->leaveRequestUpdatedBy?->name ?? '';
            $requestedAt = $leave->leave_requested_date ? date('Y-m-d H:i:s', strtotime($leave->leave_requested_date)) : '';
            $approvedAt = $leave->updated_at ? date('Y-m-d H:i:s', strtotime($leave->updated_at)) : '';
            $monthYear = $leave->leave_requested_date ? date('m-Y', strtotime($leave->leave_requested_date)) : '';
            $monthYearNote = $requestedDate && $username ? $requestedDate . '-' . $username : '';

            $rows[] = [
                $requestedDate,
                $username,
                $employeeName,
                $leaveTypeName,
                $paidStatus,
                $leave->no_of_days,
                $leave->leave_from,
                $leave->leave_to,
                strip_tags($leave->reasons ?? ''),
                $leave->status,
                $approvedBy,
                strip_tags($leave->admin_remark ?? ''),
                $requestedAt,
                $approvedAt,
                $monthYear,
                $monthYearNote,
            ];
        }

        return $rows;
    }

    public function show($leaveId)
    {
        try {

            $leaveRequest = $this->leaveService->findLeaveRequestReasonById($leaveId);

            $leaveRequest->reasons = strip_tags($leaveRequest->reasons);
            return response()->json([
                'data' => $leaveRequest,
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateLeaveRequestStatus(Request $request, $leaveRequestId)
    {
        if (auth('admin')->user() || Gate::allows('update_leave_request') || Gate::allows('access_admin_leave')) {


            try {
                $validatedData = $request->validate([
                    'status' => ['required', 'string', Rule::in(LeaveRequestMaster::STATUS)],
                    'admin_remark' => ['nullable', 'required_if:status,rejected', 'string', 'min:10'],
                ]);
                DB::beginTransaction();
                $this->leaveService->updateLeaveRequestStatus($validatedData, $leaveRequestId);
                DB::commit();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('message.leave_status_updated'),
                    ]);
                }

                if ($request->boolean('redirect_back')) {
                    return redirect()
                        ->back()
                        ->with('success', __('message.leave_status_updated'));
                }

                return redirect()
                    ->route('admin.leave-request.index')
                    ->with('success', __('message.leave_status_updated'));
            } catch (Exception $exception) {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $exception->getMessage(),
                    ], 422);
                }

                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }

    }


    public function createLeaveRequest()
    {

        if (auth('admin')->user() || Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $leaveTypes = $this->leaveTypeRepo->getGenderLeaveTypeByBranch(auth()->user()->branch_id, auth()->user()->gender, ['id','name']);
                $bsEnabled = AppHelper::ifDateInBsEnabled();

                return view($this->view . 'create', compact('leaveTypes', 'bsEnabled'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }


    }

    public function storeLeaveRequest(LeaveRequestStoreFromWeb $request)
    {
        if (auth('admin')->user() || Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $validatedData = $request->validated();

                $validatedData['requested_by'] = auth()->user()->id;
                $validatedData['branch_id'] = auth()->user()?->branch_id;
                $validatedData['department_id'] = auth()->user()?->department_id;

                DB::beginTransaction();
                $leaveRequest = $this->leaveService->storeLeaveRequest($validatedData);
                DB::commit();

                if($leaveRequest){
                    $approver = \App\Helpers\AppHelper::getNextApprover($leaveRequest['id'], $validatedData['leave_type_id'], auth()->user()->id);

                    $title = __('message.leave_notification_title');
                    $description =  __('message.leave_notification_message', [
                        'name' => ucfirst(auth()->user()->name),
                        'days' => $leaveRequest['no_of_days'],
                        'from_date' => AppHelper::formatDateForView($leaveRequest['leave_from']),
                        'request_date' => AppHelper::convertLeaveDateFormat($leaveRequest['leave_requested_date']),
                        'reason' => $validatedData['reasons']
                    ]);
                    SMPushHelper::sendLeaveNotification($title, $description,$approver);
                }
                return redirect()
                    ->back()
                    ->with('success', __('message.leave_submitted'));
            } catch (Exception $exception) {
                DB::rollBack();
                return redirect()->back()
                    ->with('danger', $exception->getMessage())
                    ->withInput();
            }
        } else {
            abort(403); // Unauthorized
        }

    }

    public function addLeaveRequest(Request $request)
    {

        if (auth('admin')->user() || Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {

                $bsEnabled = AppHelper::ifDateInBsEnabled();
                $with = ['branches:id,name'];
                $select = ['id', 'name'];
                $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
                $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(
                    ['id', 'name', 'username', 'branch_id', 'department_id'],
                    ['branch:id,name', 'department:id,dept_name']
                );
                $preselectedEmployee = null;

                if ($request->filled('requested_by')) {
                    $preselectedEmployee = $this->userRepository->findUserDetailById(
                        $request->integer('requested_by'),
                        ['id', 'name', 'username', 'branch_id', 'department_id'],
                        ['branch:id,name', 'department:id,dept_name']
                    );
                }

                return view($this->view . 'add', compact('companyDetail', 'bsEnabled', 'preselectedEmployee', 'employees'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }

    }

    public function saveLeaveRequest(LeaveRequestAdd $request)
    {

        if (auth('admin')->user() || Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $validatedData = $request->validated();

                $validatedData['referred_by'] = auth()?->user()?->id;

                $employee = $this->userRepository->findUserDetailById($validatedData['requested_by'], ['name']);

                DB::beginTransaction();
                $leaveRequest = $this->leaveService->storeLeaveRequest($validatedData);
                DB::commit();

                if($leaveRequest){

                    // to leave requested user
                    $title = __('message.leave_notification_title');

                        $description =  __('message.leave_notification_message_on_behalf', [
                            'requester_name' => isset(auth()->user()->id) ? ucfirst(auth()?->user()?->name) : 'Admin',
                            'days' => $leaveRequest['no_of_days'],
                            'from_date' => AppHelper::formatDateForView($leaveRequest['leave_from']),
                            'request_date' => AppHelper::convertLeaveDateFormat($leaveRequest['leave_requested_date']),
                        ]);


                    SMPushHelper::sendLeaveNotification($title, $description,$leaveRequest['requested_by']);

                    // to approver
                    $approver = \App\Helpers\AppHelper::getNextApprover($leaveRequest['id'], $leaveRequest['leave_type_id'], $leaveRequest['requested_by']);

                    $title = __('message.leave_notification_title');
                    $description =  __('message.leave_notification_message', [
                        'name' => $employee->name,
                        'days' => $leaveRequest['no_of_days'],
                        'from_date' => AppHelper::formatDateForView($leaveRequest['leave_from']),
                        'request_date' => AppHelper::convertLeaveDateFormat($leaveRequest['leave_requested_date']),
                        'reason' => $leaveRequest['reasons']
                    ]);
                    SMPushHelper::sendLeaveNotification($title, $description,$approver);
                }

                return redirect()
                    ->route('admin.leave-request.index')
                    ->with('success', __('message.leave_submitted'));
            } catch (Exception $exception) {
                DB::rollBack();
                return redirect()->back()
                    ->with('danger', $exception->getMessage())
                    ->withInput();
            }
        } else {
            abort(403);
        }

    }

    public function getLeaveRequestApproval($leaveRequestId)
    {

        $with=['approvedBy'];
        $leaveData = $this->leaveService->findEmployeeLeaveRequestById($leaveRequestId,['admin_remark','status','request_updated_by']);
        $approvalDetails = $this->requestApprovalRepository->findByLeaveId($leaveRequestId,$with);
        $approvalData = $approvalDetails->map(function ($approval) {
            return [
                'approved_by_name' => $approval->approvedBy ? $approval->approvedBy->name : 'N/A',
                'status' => $approval->status == 1 ? 'Approved' :'Rejected',
                'reason' => $approval->reason ?: 'N/A'
            ];
        });

        $message = isset($leaveData->request_updated_by) ? '' :  'This leave request was '. $leaveData->status. ' by Admin';
        $adminData = [
            'remark'=> $leaveData->admin_remark,
            'status'=> $leaveData->status,
            'message'=> $message,
        ];

        return response()->json(['success' => true, 'data' => ['admin_data'=>$adminData, 'approval_data'=>$approvalData]]);
    }

    private function buildLeaveRequestFilters(Request $request): array
    {
        $filterParameters = [
            'branch_id' => $request->filled('branch_id') ? $request->branch_id : null,
            'department_id' => $request->filled('department_id') ? $request->department_id : null,
            'leave_type' => $request->filled('leave_type') ? $request->leave_type : null,
            'requested_by' => $request->filled('requested_by') ? $request->requested_by : null,
            'search' => $request->filled('search') ? trim($request->search) : null,
            'per_page' => $request->filled('per_page') ? $request->per_page : '25',
            'month' => $request->filled('month') ? $request->month : null,
            'year' => $request->filled('year') ? $request->year : Carbon::now()->format('Y'),
            'status' => $request->filled('status') ? $request->status : null,
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterParameters['branch_id'] = auth()->user()->branch_id;
        }

        if (AppHelper::ifDateInBsEnabled()) {
            $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
            $filterParameters['year'] = $request->filled('year') ? $request->year : $nepaliDate['year'];
        }

        return $filterParameters;
    }

}
