<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;
use App\Repositories\LeaveTypeRepository;
use App\Requests\Leave\LeaveTypeRequest;
use Exception;
use Illuminate\Support\Facades\Gate;

class LeaveTypeController extends Controller
{
    private $view = 'admin.leaveType.';

    private LeaveTypeRepository $leaveTypeRepo;
    private LeaveRepository $leaveRepo;


    public function __construct(LeaveTypeRepository $leaveTypeRepo,
                                LeaveRepository     $leaveRepo
    )
    {
        $this->leaveTypeRepo = $leaveTypeRepo;
        $this->leaveRepo = $leaveRepo;
    }

    public function index()
    {
        if (Gate::allows('list_leave_type') || Gate::allows('access_admin_leave')) {
            try {
                $leaveTypes = $this->leaveTypeRepo->getAllLeaveTypes();
                return view($this->view . 'index', compact('leaveTypes'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function create()
    {
        if (Gate::allows('leave_type_create') || Gate::allows('access_admin_leave')) {
            try {
                return view($this->view . 'create');
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function store(LeaveTypeRequest $request)
    {
        if (Gate::allows('leave_type_create') || Gate::allows('access_admin_leave')) {
            try {
                $validatedData = $request->validated();
                $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
                $this->leaveTypeRepo->store($validatedData);
                return redirect()
                    ->route('admin.leaves.index')
                    ->with('success', __('message.leave_type_added'));
            } catch (Exception $e) {
                return redirect()->back()
                    ->with('danger', $e->getMessage())
                    ->withInput();
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function edit($id)
    {
        if (Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {

            try {
                $leaveDetail = $this->leaveTypeRepo->findLeaveTypeDetailById($id);
                return view($this->view . 'edit', compact('leaveDetail'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function update(LeaveTypeRequest $request, $id)
    {
        if (Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {

            try {
                $validatedData = $request->validated();
                $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
                $leaveDetail = $this->leaveTypeRepo->findLeaveTypeDetailById($id);
                if (!$leaveDetail) {
                    throw new Exception(__('message.leave_type_not_found'), 404);
                }
                $this->leaveTypeRepo->update($leaveDetail, $validatedData);
                return redirect()
                    ->route('admin.leaves.index')
                    ->with('success', __('message.leave_type_updated'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage())
                    ->withInput();
            }
        } else {
            abort(403); // Unauthorized
        }

    }

    public function toggleStatus($id)
    {
        if (Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {
            try {
                $this->leaveTypeRepo->toggleStatus($id);
                return redirect()->back()->with('success', __('message.status_changed'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function toggleEarlyExit($id)
    {
        if (Gate::allows('leave_type_edit') || Gate::allows('access_admin_leave')) {

            try {
                $this->leaveTypeRepo->toggleEarlyExitStatus($id);
                return redirect()->back()->with('success', __('message.leave_type_early_exit_status_changed'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function delete($id)
    {
        if (Gate::allows('leave_type_delete') || Gate::allows('access_admin_leave')) {
            try {
                $leaveType = $this->leaveTypeRepo->findLeaveTypeDetailById($id);
                if (!$leaveType) {
                    throw new Exception(__('message.leave_type_not_found'), 404);
                }
                $checkLeaveTypeIfUsed = $this->leaveRepo->findLeaveRequestCountByLeaveTypeId($leaveType->id);
                if ($checkLeaveTypeIfUsed > 0) {
                    throw new Exception(__('message.leave_type_cannot_delete_in_use', ['name' => ucfirst($leaveType->name)]), 402);
                }
                $this->leaveTypeRepo->delete($leaveType);
                return redirect()->back()->with('success', __('message.leave_type_deleted'));
            } catch (Exception $exception) {
                return redirect()->back()->with('danger', $exception->getMessage());
            }
        } else {
            abort(403); // Unauthorized
        }
    }

    public function getLeaveTypesBasedOnEarlyExitStatus($status)
    {
        try {
            $leaveType = $this->leaveTypeRepo->getAllLeaveTypesBasedOnEarlyExitStatus($status);
            return AppHelper::sendSuccessResponse(__('message.data_found'), $leaveType);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
}
