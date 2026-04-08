<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayslip;
use App\Repositories\UserRepository;
use App\Requests\Payroll\SalaryGroup\SalaryGroupRequest;
use App\Services\Payroll\SalaryComponentService;
use App\Services\Payroll\SalaryGroupService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SalaryGroupController extends Controller
{

    private $view = 'admin.payrollSetting.salaryGroup.';

    public function __construct(
        public SalaryGroupService     $salaryGroupService,
        public SalaryComponentService $salaryComponentService,
        public UserRepository $userRepo
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(): Factory|View|RedirectResponse|Application
    {
        $this->authorize('salary_group');
        try {
            $select = ['*'];
            $with = ['salaryComponents:name'];
            $salaryGroupLists = $this->salaryGroupService->getAllSalaryGroupDetailList($select, $with);
            return view($this->view . 'index', compact('salaryGroupLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        $this->authorize('salary_group');
        try {

            $salaryComponents = $this->salaryComponentService->pluckAllActiveSalaryComponent();
            $employees = $this->userRepo->pluckIdAndNameOfAllVerifiedEmployee();
            return view($this->view . 'create', compact('salaryComponents','employees'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(SalaryGroupRequest $request): RedirectResponse
    {
        $this->authorize('salary_group');
        try {

            $validatedData = $request->validated();
            $this->salaryGroupService->store($validatedData);
            return redirect()
                ->route('admin.salary-groups.index')
                ->with('success', __('message.salary_group_add'));
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id): Factory|View|RedirectResponse|Application
    {
        $this->authorize('salary_group');
        try {

            $groupSelect = ['*'];
            $with = ['salaryComponents:id,name','groupEmployees:salary_group_id,employee_id'];
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id,$groupSelect,$with);
            $salaryComponents = $this->salaryComponentService->pluckAllActiveSalaryComponent();
            $employees = $this->userRepo->pluckIdAndNameOfAllVerifiedEmployee();

            $salaryGroupComponentId = $salaryGroupDetail?->salaryComponents?->pluck('id')->toArray() ?? [];
            $salaryGroupEmployeeId = $salaryGroupDetail?->groupEmployees?->pluck('employee_id')->toArray() ?? [];

            return view($this->view . 'edit', compact('salaryGroupDetail',
                'salaryComponents','salaryGroupComponentId','salaryGroupEmployeeId','employees'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function update(SalaryGroupRequest $request, $id): RedirectResponse
    {
        $this->authorize('salary_group');
        try {

            $select = ['*'];
            $validatedData = $request->validated();
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id, $select);
            $this->salaryGroupService->updateDetail($salaryGroupDetail,$validatedData);
            return redirect()
                ->route('admin.salary-groups.index')
                ->with('success', __('message.salary_group_update'));
        } catch (Exception $e) {
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function deleteSalaryGroup($id): RedirectResponse
    {
        $this->authorize('salary_group');
        try {

            $select = ['*'];
            $payrollCount = EmployeePayslip::where('salary_group_id', $id)->count();
            if($payrollCount > 0){
                return redirect()
                    ->back()
                    ->with('danger', __('message.salary_group_delete_error'));
            }
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id, $select);
            $this->salaryGroupService->deleteSalaryGroupDetail($salaryGroupDetail);
            return redirect()
                ->back()
                ->with('success', __('message.salary_group_delete'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleSalaryGroupStatus($id): RedirectResponse
    {
        $this->authorize('salary_group');
        try {

            $select = ['*'];
            $salaryGroupDetail = $this->salaryGroupService->findOrFailSalaryGroupDetailById($id, $select);
            $this->salaryGroupService->changeSalaryGroupStatus($salaryGroupDetail);
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

}
