<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\SalaryReviseHistory;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Requests\Payroll\ReviseSalary\ReviseSalaryRequest;
use App\Services\FiscalYear\FiscalYearService;
use App\Services\Payroll\SalaryReviseHistoryService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Validation\ValidationException;

class SalaryHistoryController extends Controller
{
    use CustomAuthorizesRequests;
    public $view = 'admin.payroll.employeeSalary.';

    public function __construct(
        protected SalaryReviseHistoryService $salaryHistoryService,
        protected UserRepository $userRepo,
        protected EmployeeSalaryRepository $employeeSalaryRepository,
        protected FiscalYearService $fiscalYearService,
    ){}

    public function getEmployeeAllSalaryHistory($employeeId)
    {
        $this->authorize('show_salary_history');
        $this->checkEmployeeBranch($employeeId);
        try{
            $select = ['*'];

            $salaryReviseLists = $this->salaryHistoryService->getEmployeeAllSalaryHistory($employeeId,$select,['createdBy']);
            $employeeDetail = $this->userRepo->findUserDetailById($employeeId,['id','name']);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            return view($this->view.'salary_history',compact('salaryReviseLists','employeeDetail','currency'));
        }catch (Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function create($employeeId)
    {
        $this->authorize('salary_increment');
        $this->checkEmployeeBranch($employeeId);
        try{
            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);

            abort_unless($employeeSalary, 404);
            $employeeDetail = $this->userRepo->findUserDetailById($employeeId,['id','name']);
            return view($this->view.'increment_salary',compact('employeeDetail','employeeSalary'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function store(ReviseSalaryRequest $request)
    {
        $this->authorize('salary_increment');
        $this->checkEmployeeBranch($request->integer('employee_id'));
        try{
            $validatedData = $request->validated();

            $this->salaryHistoryService->store($validatedData);
            if ($request->input('salary_modal') === '1') {
                return redirect()->route('admin.employee-salaries.index', ['employee_modal' => '1', 'salary_modal' => '1'])
                    ->with('success', __('message.salary_history_update'));
            }
            if ($request->input('employee_modal') === '1') {
                return redirect()->route('admin.employees.profile.show', [
                    'employee' => $request->integer('employee_id'), 'tab' => 'salary', 'employee_modal' => '1',
                ])->with('success', __('message.salary_history_update'));
            }
            return redirect()
                ->route('admin.employee-salaries.index')
                ->with('success',__('message.salary_history_update'));
        }catch(ValidationException $exception){
            throw $exception;
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger',$exception->getMessage())
                ->withInput();
        }
    }
    private function checkEmployeeBranch(int $employeeId): void
    {
        $employee = \App\Models\User::findOrFail($employeeId);
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }
    }

}
