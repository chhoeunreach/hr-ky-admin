<?php

namespace App\Http\Controllers\Web;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\ComplaintEmployee;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Complaint\ComplaintRequest;
use App\Services\Complaint\ComplaintService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    private string $view = 'admin.complaint.';

    public function __construct(
        protected ComplaintService $complaintService,
        protected UserRepository $userRepository,protected BranchRepository $branchRepository,
        protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('list_complaint');
        try{
            $select = ['*'];
            $with = ['complaintEmployee.employee:id,name'];
            $complaintLists = $this->complaintService->getAllComplaintPaginated($select,$with);

            return view($this->view.'index', compact('complaintLists'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_complaint');

        try{
            $departmentIds = [];
            $employeeIds = [];
            $companyId = AppHelper::getAuthUserCompanyId();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);

            return view($this->view.'create', compact('branch','isBsEnabled','employeeIds','departmentIds','employees'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param ComplaintRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(ComplaintRequest $request)
    {
        $this->authorize('create_complaint');

        try{
            $validatedData = $request->validated();

            DB::beginTransaction();
            $complaintDetail = $this->complaintService->saveComplaintDetail($validatedData);
            DB::commit();
            if($complaintDetail && $validatedData['notification'] == 1){
                // notification to members
                $message = 'A formal complaint (#' . $complaintDetail['id'] . ') has been filed against you regarding ' . ucfirst($complaintDetail['subject']) .
                    '. Please review and respond as early as possible through your account dashboard.';
                $this->sendNotification( $message, $validatedData['employee_id']);

            }

            return redirect()->route('admin.complaint.index')->with('success',__('message.add_complaint') );
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */

    public function show($id)
    {
        $this->authorize('show_complaint');

        try{
            $select = ['*'];
            $with = ['complainFrom:id,name','branch:id,name','complaintDepartment.department:id,dept_name','createdBy:id,name', 'updatedBy:id,name','complaintEmployee.employee:id,name','complaintReply.employee:id,name'];
            $complaintDetail = $this->complaintService->findComplaintById($id,$select,$with);
            $trainerTypes = TrainerTypeEnum::cases();
            $departmentIds = [];
            $employeeIds = [];
            return view($this->view.'show', compact('complaintDetail','trainerTypes','departmentIds','employeeIds'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('update_complaint');
        try{
            $with = ['complaintEmployee','complaintDepartment'];
            $complaintDetail = $this->complaintService->findComplaintById($id,['*'],$with);
            $companyId = AppHelper::getAuthUserCompanyId();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id','name'];


            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);

            $selectUser = ['id', 'name'];
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);
            $employeeIds = [];
            foreach ($complaintDetail->complaintEmployee as $key => $value) {
                $employeeIds[] = $value->employee_id;
            }

            $departmentIds = [];
            foreach ($complaintDetail->complaintDepartment as $key => $value) {
                $departmentIds[] = $value->department_id;
            }
            // Fetch users by selected departments
            $filteredDepartment = isset($complaintDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($complaintDetail->branch_id,[], ['id','dept_name'])
                : [];

            $select = ['name', 'id'];
            $filteredUsers = !empty($departmentIds)
                ? $this->userRepository->getActiveEmployeesByDepartment($departmentIds, $select)
                : $users;

            $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);


            return view($this->view.'edit', compact('complaintDetail','isBsEnabled','branch','employeeIds','filteredUsers','departmentIds','filteredDepartment','employees'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ComplaintRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(ComplaintRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_complaint');
        try{

            $previousEmployee = [];

            $validatedData = $request->validated();

            if($validatedData['notification'] == 1){
                $previousEmployee = ComplaintEmployee::where('complaint_id',$id)->get('employee_id')->toArray();

            }

            DB::beginTransaction();
            $complaintDetail = $this->complaintService->updateComplaintDetail($id,$validatedData);
            DB::commit();

            if($complaintDetail && $validatedData['notification'] == 1){

                $previousEmployeeIds = array_column($previousEmployee, 'employee_id');
                $removedIds = array_diff($previousEmployeeIds, $validatedData['employee_id']);
                $addedEmployeeIds = array_diff($validatedData['employee_id'], $previousEmployeeIds);


                $today = date('Y-m-d');
                $start = $complaintDetail['complaint_date'];

                if(strtotime($today) <= strtotime($start)) {
                    // add notification
                    $message = 'A formal complaint (#' . $complaintDetail['id'] . ') has been filed against you regarding ' . ucfirst($complaintDetail['subject']) .
                        '. Please review and respond as early as possible through your account dashboard.';
                    $this->sendNotification( $message, $addedEmployeeIds);


                    //remove notification
                    $removeMassage = 'The formal complaint regarding ' . ucfirst($complaintDetail['subject']) . ' has been withdrawn.';
                    $this->sendNotification( $removeMassage, $removedIds);

                }
            }
            return redirect()->route('admin.complaint.index')
                ->with('success', __('message.update_complaint'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_complaint');
        try{
            DB::beginTransaction();
            $this->complaintService->deleteComplaint($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_complaint'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    private function sendNotification($message, $userIds)
    {
        SMPushHelper::sendComplaintNotification('Complaint Notification', $message, $userIds);
    }


}
