<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequestMaster;

use App\Repositories\LeaveRequestApprovalRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveRequestAdd;
use App\Requests\Leave\LeaveRequestStoreFromWeb;

use App\Services\Leave\LeaveService;
use App\Services\Notification\NotificationService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    private $view = 'admin.leaveRequest.';

    public function __construct(protected LeaveService $leaveService, protected LeaveTypeRepository $leaveTypeRepo, protected NotificationService $notificationService,
                                protected UserRepository $userRepository, protected LeaveRequestApprovalRepository $requestApprovalRepository)
    {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        if (Gate::allows('list_leave_request') || Gate::allows('access_admin_leave')) {
            try {
                $filterParameters = [
                    'leave_type' => $request->leave_type ?? null,
                    'requested_by' => $request->requested_by ?? null,
                    'month' => $request->month ?? null,
                    'year' => $request->year ?? Carbon::now()->format('Y'),
                    'status' => $request->status ?? null
                ];
                if(AppHelper::ifDateInBsEnabled()){
                    $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                    $filterParameters['year'] = $request->year ?? $nepaliDate['year'];
                }
                $leaveTypes = $this->leaveTypeRepo->getAllLeaveTypes(['id','name']);
                $months = AppHelper::MONTHS;
                $with = ['leaveType:id,name', 'leaveRequestedBy:id,name','requestApproval'];
                $select = ['leave_requests_master.*'];
                $leaveDetails = $this->leaveService->getAllEmployeeLeaveRequests($filterParameters,$select, $with);
                return view($this->view . 'index',
                    compact('leaveDetails', 'filterParameters',  'leaveTypes','months') );
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }

        } else {
            abort(403); // Unauthorized
        }

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
        if (Gate::allows('update_leave_request') || Gate::allows('access_admin_leave')) {
            $validatedData = $request->validate([
                'status' => ['required', 'string', Rule::in(LeaveRequestMaster::STATUS)],
                'admin_remark' => ['nullable', 'required_if:status,rejected', 'string', 'min:10'],
            ]);

            try {

                $this->leaveService->updateLeaveRequestStatus($validatedData, $leaveRequestId);

                return redirect()
                    ->route('admin.leave-request.index')
                    ->with('success', __('message.leave_status_updated'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }

    }



    public function createLeaveRequest()
    {

        if (Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $leaveTypes = $this->leaveTypeRepo->getAllActiveLeaveTypes(['id', 'name']);
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
        if (Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $validatedData = $request->validated();

                $validatedData['requested_by'] = auth()->user()->id;
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

    public function addLeaveRequest()
    {

        if (Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $leaveTypes = $this->leaveTypeRepo->getAllActiveLeaveTypes(['id','name']);
                $bsEnabled = AppHelper::ifDateInBsEnabled();

                $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);

                return view($this->view . 'add', compact('employees','leaveTypes','bsEnabled'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }

    }

    public function saveLeaveRequest(LeaveRequestAdd $request)
    {

        if (Gate::allows('request_leave') || Gate::allows('access_admin_leave')) {
            try {
                $validatedData = $request->validated();

                $validatedData['referred_by'] = auth()->user()->id;

                $employee = $this->userRepository->findUserDetailById($validatedData['requested_by'], ['name']);

                DB::beginTransaction();
                $leaveRequest = $this->leaveService->storeLeaveRequest($validatedData);
                DB::commit();

                if($leaveRequest){

                    // to leave requested user
                    $title = __('message.leave_notification_title');
                    $description =  __('message.leave_notification_message_on_behalf', [
                        'requester_name' => ucfirst(auth()->user()->name),
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
        $leaveData = $this->leaveService->findEmployeeLeaveRequestById($leaveRequestId,['admin_remark','status']);
        $approvalDetails = $this->requestApprovalRepository->findByLeaveId($leaveRequestId,$with);
        $approvalData = $approvalDetails->map(function ($approval) {
            return [
                'approved_by_name' => $approval->approvedBy ? $approval->approvedBy->name : 'N/A',
                'status' => $approval->status == 1 ? 'Approved' :'Rejected',
                'reason' => $approval->reason ?: 'N/A'
            ];
        });

        $adminData = [
            'remark'=> $leaveData->admin_remark,
            'status'=> $leaveData->status,
            'message'=> 'This leave request was '. $leaveData->status. ' by Admin'
        ];

        return response()->json(['success' => true, 'data' => ['admin_data'=>$adminData, 'approval_data'=>$approvalData]]);
    }

}
