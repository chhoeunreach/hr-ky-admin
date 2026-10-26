<?php

namespace App\Http\Controllers\Web;

use App\Enum\EmployeeBasicSalaryTypeEnum;
use App\Enum\PayslipStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\DateConverter;
use App\Helpers\NepaliDate;
use App\Helpers\PayrollHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Imports\EmployeeSalaryImport;
use App\Models\Company;
use App\Models\EmployeeAccount;
use App\Models\EmployeePayslipDetail;
use App\Models\UnderTimeSetting;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeePayslipAdditionalRepository;
use App\Repositories\EmployeePayslipDetailRepository;
use App\Repositories\EmployeePayslipRepository;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryGroupEmployeeRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Services\Notification\NotificationService;
use App\Services\Payroll\AdvanceSalaryService;
use App\Services\Payroll\BonusService;
use App\Services\Payroll\GeneratePayrollService;
use App\Services\Payroll\PaymentMethodService;
use App\Services\Payroll\SalaryComponentService;
use App\Services\Payroll\SalaryGroupService;
use App\Services\Payroll\SalaryTDSService;
use App\Services\Payroll\UnderTimeSettingService;
use App\Services\Tada\TadaService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use MilanTarami\NumberToWordsConverter\Services\NumberToWords;

class EmployeeSalaryController extends Controller
{
    use CustomAuthorizesRequests;
    public $view = 'admin.payroll.employeeSalary.';

    public function __construct(
        public UserRepository $userRepository,
        public UserAccountRepository $userAccountRepo,
        public GeneratePayrollService $generatePayrollService,
        public EmployeeSalaryRepository $employeeSalaryRepository,
        public SalaryGroupEmployeeRepository $salaryGroupEmployeeRepository,
        public SalaryGroupService $salaryGroupService,
        public EmployeePayslipRepository $payslipRepository,
        public EmployeePayslipDetailRepository $payslipDetailRepository,
        public DepartmentRepository $departmentRepository,
        public TadaService $tadaService,
        public PaymentMethodService $paymentMethodService,
        public AdvanceSalaryService $advanceSalaryService,
        public BranchRepository $branchRepo,
        public UnderTimeSettingService $utSettingService,
        public SalaryComponentService $salaryComponentService,
        public SalaryTDSService $salaryTDSService,
        public BonusService $bonusService,
        public NotificationService $notificationService,
        public CompanyRepository $companyRepository,
        protected EmployeePayslipAdditionalRepository $additionalRepository
    ){}

    public function index(Request $request): Factory|View|\Symfony\Component\HttpFoundation\Response|Application
    {
        $this->authorize('view_salary_list');
        if ($request->filled('export')) $this->authorize('export_salary');
        $request->validate([
            'employee_id' => ['nullable', 'integer', 'min:1'],
            'branch_id' => ['nullable', 'integer', 'min:1'], 'department_id' => ['nullable', 'integer', 'min:1'],
            'employee_name' => ['nullable', 'string', 'max:200'], 'q' => ['nullable', 'string', 'max:200'],
            'payment_type' => ['nullable', 'in:monthly,weekly'], 'salary_status' => ['nullable', 'in:configured,missing'],
            'per_page' => ['nullable', 'in:10,25,50,100,all'], 'export' => ['nullable', 'in:csv,excel,print,pdf,json'],
            'orientation' => ['nullable', 'in:portrait,landscape'], 'columns' => ['nullable', 'array', 'min:1'],
            'columns.*' => ['string', \Illuminate\Validation\Rule::in(array_keys($this->salaryTableColumns()))],
        ]);
        try{

            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'employee_id' => $request->filled('employee_id') ? $request->integer('employee_id') : null,
                'department_id' => $request->department_id ?? null,
                'branch_id' => $request->branch_id ?? null,
                'q' => trim((string) $request->input('q', '')),
                'payment_type' => $request->input('payment_type'), 'salary_status' => $request->input('salary_status'),
                'per_page' => $request->input('per_page') ?: '25', 'exporting' => $request->filled('export'),
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $employeeLists = $this->userRepository->getAllVerifiedActiveEmployeeWithSalaryGroup($filterParameters);


            $employeeSalaries = \App\Models\EmployeeSalary::whereIn('employee_id', $employeeLists->pluck('employee_id'))->get()->keyBy('employee_id');
            if ($request->filled('export')) return $this->exportSalaryTable($request, $employeeLists, $employeeSalaries);
            $salaryColumns = $this->salaryTableColumns();
            $defaultSalaryColumns = $this->defaultSalaryColumns();
            if ($request->header('X-Salary-Fragment') === '1') {
                return view($this->view.'partials.salary-list', compact('employeeLists', 'employeeSalaries', 'filterParameters', 'salaryColumns', 'defaultSalaryColumns'));
            }
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index',compact('employeeLists','employeeSalaries','filterParameters','companyDetail', 'salaryColumns', 'defaultSalaryColumns'));
        }catch(Exception $exception){
            if ($request->header('X-Salary-Fragment') === '1') {
                return response()->json(['message' => __('index.salary_refresh_error')], 500);
            }
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    private function salaryTableColumns(): array
    {
        $currency = ' ('.AppHelper::getCompanyPaymentCurrencySymbol().')';
        return ['number' => '#', 'employee' => __('index.employee_name'), 'salary' => __('index.salary_table_gross').$currency,
            'basic_salary' => __('index.basic_salary').$currency, 'allowance' => __('index.fixed_allowance').$currency,
            'payment_type' => __('index.salary_table_payment_cycle'), 'status' => __('index.status'), 'marital_status' => __('index.marital_status'),
            'salary_cycle' => __('index.salary_table_account_cycle'), 'salary_group' => __('index.salary_group')];
    }

    private function defaultSalaryColumns(): array
    {
        return ['employee', 'salary', 'basic_salary', 'allowance', 'payment_type', 'status'];
    }

    private function exportSalaryTable(Request $request, $employees, $salaries)
    {
        $columns = array_intersect_key($this->salaryTableColumns(), array_flip($request->input('columns') ?: $this->defaultSalaryColumns()));
        $headings = array_values($columns);
        $rows = $employees->values()->map(function ($employee, $index) use ($salaries, $columns) {
            $salary = $salaries->get($employee->employee_id);
            $data = ['number' => (string) ($index + 1), 'employee' => $employee->employee_name,
                'salary' => $salary ? number_format((float) $salary->annual_salary / ($salary->payment_type === 'weekly' ? 52 : 12), 2, '.', '') : '',
                'basic_salary' => $salary ? number_format((float) ($salary->payment_type === 'weekly' ? $salary->weekly_basic_salary : $salary->monthly_basic_salary), 2, '.', '') : '',
                'allowance' => $salary ? number_format((float) ($salary->payment_type === 'weekly' ? $salary->weekly_fixed_allowance : $salary->monthly_fixed_allowance), 2, '.', '') : '',
                'payment_type' => $salary ? __('index.'.$salary->payment_type) : '',
                'status' => __($salary ? 'index.salary_setup_ready' : 'index.salary_setup_missing'),
                'marital_status' => in_array($employee->marital_status, ['single', 'married'], true) ? __('index.marital_status_'.$employee->marital_status) : ucfirst($employee->marital_status ?? ''),
                'salary_cycle' => $employee->salary_cycle ? __('index.'.$employee->salary_cycle) : '',
                'salary_group' => $employee->salary_group_name ?? ''];
            return array_map(fn ($key) => (string) ($data[$key] ?? ''), array_keys($columns));
        })->all();
        $format = $request->input('export');
        if ($format === 'json') return response()->json(compact('headings', 'rows'));
        if (in_array($format, ['csv', 'excel'], true)) {
            if ($format === 'csv') $rows = array_map(fn ($row) => array_map(fn ($value) => preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value, $row), $rows);
            return Excel::download(new \App\Exports\EmployeeSalaryTableExport($headings, $rows),
                'employee-salaries-'.now()->format('Ymd').($format === 'csv' ? '.csv' : '.xlsx'));
        }
        return view($this->view.'exports.table', [
            'headings' => $headings, 'rows' => $rows, 'format' => $format,
            'orientation' => $request->input('orientation') ?: 'landscape',
        ]);
    }

    public function changeSalaryCycle($employeeId,$salaryCycle)
    {
        $this->authorize('change_salary_cycle');
        $this->checkSalaryEmployeeBranch($employeeId);
        try{

            if (!$this->userRepository->findUserDetailById($employeeId, ['id'])) {
                throw new Exception(__('message.account_not_found'), 404);
            }

            $employeeAccountDetail = $this->userAccountRepo->findAccountDetailByEmployeeId($employeeId);
            if(!$employeeAccountDetail){
                throw new Exception(__('message.account_not_found'),404);
            }
            if(!in_array($salaryCycle,EmployeeAccount::SALARY_CYCLE)){
                throw new Exception(__('message.invalid_cycle'),400);
            }
            $updateCycle = $this->userAccountRepo->updateEmployeeSalaryCycle($employeeAccountDetail,$salaryCycle);
            if (request()->expectsJson()) return response()->json(['message' => __('message.salary_cycle_update', ['cycle' => __('index.'.$updateCycle->salary_cycle)])]);
            return redirect()->back()->with('success',__('message.salary_cycle_update',['cycle'=>__('index.' . $updateCycle->salary_cycle)]));

        }catch(Exception $exception){
            if (request()->expectsJson()) return response()->json(['message' => $exception->getMessage()], 422);
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

//    public function updateGeneratePayrollStatus($employeeId)
//    {
//        try{
//            $employeeAccountDetail = $this->userAccountRepo->findAccountDetailByEmployeeId($employeeId);
//            if(!$employeeAccountDetail){
//                throw new Exception('Employee Account Detail Not Found',404);
//            }
//            $this->userAccountRepo->toggleAllowGeneratePayrollStatus($employeeAccountDetail);
//            return redirect()->back()->with('success', 'Generate Payroll Status Updated Successfully');
//        }catch(Exception $exception){
//            return redirect()
//                ->back()
//                ->with('danger', $exception->getMessage());
//        }
//    }

    public function payrollCreate(Request $request)
    {
        try{

            $payrollData = $this->generatePayrollService->getEmployeeSalariesToCreatePayslip();

            return view($this->view.'generate_payslip',compact('payrollData'));
        }catch(Exception $exception){
            return response()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function payroll(Request $request): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('view_payroll_list');


            $filterData = [];

            if($request->all()){
                $validator = Validator::make($request->all(), [
                    'salary_cycle' => ['required','in:monthly,weekly'],
                    'year' => ['required'],
                    'month' => ['nullable','required_if:salary_cycle,monthly'],
                    'week' => ['nullable','required_if:salary_cycle,weekly'],
                    'include_tds' => ['nullable'],
                    'include_ssf' => ['nullable'],
                    'include_pf' => ['nullable'],
                    'include_tada' => ['nullable'],
                    'include_advance_salary'=> ['nullable'],
                    'attendance'=>['required'],
                    'absent_paid'=>['nullable'],
                    'department_id'=>['nullable'],
                    'branch_id'=>['required'],
                    'approved_paid_leaves'=>['nullable'],

                ]);
                if ($validator->fails()) {
                    return redirect()->back() ->withErrors($validator);
                }

                $filterData = $validator->validated();

                $filterData['include_tada'] =  $filterData['include_tada'] ?? 0;
                $filterData['include_advance_salary'] =  $filterData['include_advance_salary'] ?? 0;
                $filterData['include_tds'] =  $filterData['include_tds'] ?? 0;
                $filterData['include_ssf'] =  $filterData['include_ssf'] ?? 0;
                $filterData['include_pf'] =  $filterData['include_pf'] ?? 0;


                $payrolls = $this->generatePayrollService->getEmployeeSalariesToCreatePayslip($filterData);
            }else {
                $payrolls = $this->generatePayrollService->getCurrentEmployeeSalaries();
            }

            $employees = $this->userRepository->getAllEmployeesForPayroll();
            $paymentMethods = $this->paymentMethodService->pluckAllActivePaymentMethod(['id','name']);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            $companyId = AppHelper::getAuthUserCompanyId();

            $payslipStatus = EmployeePayslipDetail::PAYSLIP_STATUS;
            $salaryCycles = EmployeeAccount::SALARY_CYCLE;
            $branches = $this->branchRepo->getLoggedInUserCompanyBranches($companyId,['id','name']);

            $isBSDate = AppHelper::ifDateInBsEnabled();
            $months = AppHelper::getMonthsList();
            $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();

            return view($this->view.'payroll', compact('payslipStatus', 'salaryCycles','payrolls', 'filterData','paymentMethods','currency','employees','branches','months','isBSDate','currentNepaliYearMonth'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function viewPayroll($employeePayslipId): Factory|View|RedirectResponse|Application
    {
        $this->authorize('show_payroll_detail');

        try{
            $imagePath = User::AVATAR_UPLOAD_PATH;
            $payrollData = $this->generatePayrollService->getEmployeeAccountDetailToCreatePayslip($employeePayslipId);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);

            return view($this->view.'payslip', compact('payrollData','imagePath','currency','underTimeSetting'));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function editPayroll($employeePayslipId): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('edit_payroll');
            $payrollData = $this->generatePayrollService->getEmployeeAccountDetailToCreatePayslip($employeePayslipId);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);

            $paymentMethods = $this->paymentMethodService->pluckAllActivePaymentMethod(['id','name']);
            $paidStatus = PayslipStatusEnum::paid->value;

            return view($this->view.'edit_payslip', compact('payrollData','currency','underTimeSetting','paidStatus','paymentMethods'));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function updatePayroll(Request $request, $employeePayslipId): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('edit_payroll');
            $validatedData = $request->all();


            $employeePayslipData = [
                "paid_on" => ($validatedData['status'] == PayslipStatusEnum::paid->value) ? $validatedData['paid_on']: null,
                "status" => $validatedData['status'],
                "monthly_basic_salary" => $validatedData['monthly_basic_salary'],
                "monthly_fixed_allowance" => $validatedData['monthly_fixed_allowance'],
                "tds" => $validatedData['tds'] ?? 0,
                "ssf_deduction" => $validatedData['ssf_deduction'] ?? 0,
                "bonus" => $validatedData['bonus'] ?? 0,
                "advance_salary" => $validatedData['advance_salary'] ?? 0,
                "tada" => $validatedData['tada'] ?? 0,
                "net_salary" => $validatedData['net_salary'],
                "absent_deduction" => $validatedData['absent_deduction'] ?? 0,
                "overtime" => $validatedData['overtime'] ?? 0,
                "undertime" => $validatedData['undertime'] ?? 0,
                "payment_method_id" => $validatedData['payment_method_id'] ?? null,
            ];


            $employeePaySlipDetail = $this->payslipRepository->find($employeePayslipId);

            if($validatedData['status'] == PayslipStatusEnum::paid->value){
                $payslipYearMonth = AppHelper::getYearMonth($employeePaySlipDetail->salary_from);
                $payslipYear = $payslipYearMonth['year'];
                $payslipMonth = $payslipYearMonth['month'];
                $isBsEnabled = AppHelper::ifDateInBsEnabled();
                if($isBsEnabled){
                    $currentYearMonth = AppHelper::getCurrentYearMonth();
                    $currentYear = $currentYearMonth['year'];
                    $currentMonth = $currentYearMonth['month'];
                }else{
                    $currentYear = date('Y');
                    $currentMonth = date('m');
                }
                if(($payslipYear > $currentYear || ($currentYear == $payslipYear && $payslipMonth >= $currentMonth))){
                    throw new Exception(__('message.payslip_payment_error'),400);
                }

            }


            DB::beginTransaction();

                if($validatedData['status'] == PayslipStatusEnum::paid->value)
                {
                    if($employeePaySlipDetail->tada > 0){
                        $updateData = [
                            'is_settled'=>1,
                            'remark'=>'included in salary.',
                            'verified_by'=>auth()->user()?->id,
                        ];
                        $this->tadaService->makeSettlement($updateData, $employeePaySlipDetail);
                    }

                    // make advance salary settlement
                    if($employeePaySlipDetail->advance_salary > 0){
                        $advanceData = [
                            'is_settled'=>true,
                            'remark' => 'settled in payroll'
                        ];
                        $this->advanceSalaryService->advanceSalarySettlement($employeePaySlipDetail, $advanceData);
                    }

                }
                $this->payslipRepository->update($employeePaySlipDetail, $employeePayslipData);


                /** payslip detail update for components */
                if(isset($validatedData['component_amount'])){
                    foreach ($validatedData['component_amount'] as $key => $value) {
                        $payslipDetail = $this->payslipDetailRepository->find($employeePayslipId, $key);

                        $this->payslipDetailRepository->update($payslipDetail, ['amount' => $value]);

                    }
                }
                /** payslip additional component update */
                if(isset($validatedData['additional_component_amount'])){
                    foreach ($validatedData['additional_component_amount'] as $key => $value) {
                        $payslipDetail = $this->additionalRepository->find($employeePayslipId, $key);

                        $this->payslipDetailRepository->update($payslipDetail, ['amount' => $value]);

                    }
                }

            DB::commit();
            return redirect()->route('admin.employee-salary.payroll-detail',$employeePayslipId)->with('success', __('message.payroll_update'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function deletePayroll(Request $request, $employeePayslipId): RedirectResponse
    {
        try{
            $this->authorize('delete_payroll');
            $employeePaySlipDetail = $this->payslipRepository->find($employeePayslipId);

            if($employeePaySlipDetail->status == PayslipStatusEnum::paid->value  || $employeePaySlipDetail->status == PayslipStatusEnum::locked->value){
                return redirect()->back()->with('danger',__('message.payroll_delete_error'));
            }

            DB::beginTransaction();
                $this->payslipDetailRepository->deleteByPayslipId($employeePayslipId);
                $this->additionalRepository->deleteByPayslipId($employeePayslipId);
                $this->payslipRepository->delete($employeePaySlipDetail);
            DB::commit();

            return redirect()->route('admin.employee-salary.payroll')->with('success', __('message.payroll_delete'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    private function filterComponentsByType($components, $type): array
    {
        return array_filter($components, function ($value) use ($type) {
            return $value['component_type'] === $type;
        });
    }

    /**
     * @return Factory|View|RedirectResponse|Application
     */
    public function createSalary($employeeId): Factory|View|RedirectResponse|Application
    {
        $this->authorize('add_salary');
        $this->checkSalaryEmployeeBranch($employeeId);
        try{
            $salaryComponents = [];
            $employee = $this->userRepository->findUserDetailById($employeeId,['id','name']);
            $percentType = EmployeeBasicSalaryTypeEnum::percent->value;
            $fixedType = EmployeeBasicSalaryTypeEnum::fixed->value;

            $employeeSalaryGroup = $this->salaryGroupEmployeeRepository->getSalaryGroupFromEmployeeId($employeeId);

            if($employeeSalaryGroup)
            {
                $salaryGroup = $this->salaryGroupService->findOrFailSalaryGroupDetailById($employeeSalaryGroup->salary_group_id, ['*'], ['salaryComponents']);

                if($salaryGroup)
                {
                    $salaryComponents = $salaryGroup->salaryComponents->toArray();
                }
            }
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();


            return view($this->view.'add_salary', compact('employee','percentType','fixedType','employeeSalaryGroup','salaryComponents','currency'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $employeeId
     * @return RedirectResponse
     */
    public function saveSalary(Request $request, $employeeId): RedirectResponse
    {
        $this->authorize('add_salary');
        $this->checkSalaryEmployeeBranch($employeeId);
        $validatedData = $this->validatedSalaryData($request, $employeeId);
        try{
            if (!$this->userRepository->findUserDetailById($employeeId, ['id'])) {
                throw new Exception(__('message.account_not_found'), 404);
            }
            if ($this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId)) {
                throw ValidationException::withMessages(['employee_id' => 'This employee already has a salary record.']);
            }

            DB::transaction(function () use ($employeeId, $validatedData) {
                \App\Models\User::whereKey($employeeId)->lockForUpdate()->firstOrFail();
                if ($this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId)) {
                    throw ValidationException::withMessages(['employee_id' => __('index.salary_manage_already_exists')]);
                }
                $salary = $this->employeeSalaryRepository->store($validatedData);
                app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->syncProfile($salary);
            });

            return $this->salarySavedRedirect($request, $employeeId)->with('success',__('message.salary_add'));

        }catch(ValidationException $exception){
            throw $exception;
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }


    /**
     * @return Factory|View|RedirectResponse|Application
     */
    public function editSalary($employeeId): Factory|View|RedirectResponse|Application
    {
        $this->authorize('edit_salary');
        $this->checkSalaryEmployeeBranch($employeeId);
        try{
            $salaryComponents = [];
            $employee = $this->userRepository->findUserDetailById($employeeId,['id','name']);
            $percentType = EmployeeBasicSalaryTypeEnum::percent->value;
            $fixedType = EmployeeBasicSalaryTypeEnum::fixed->value;
            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);


            $employeeSalaryGroup = $this->salaryGroupEmployeeRepository->getSalaryGroupFromEmployeeId($employeeId);

            if($employeeSalaryGroup)
            {
                $salaryGroup = $this->salaryGroupService->findOrFailSalaryGroupDetailById($employeeSalaryGroup->salary_group_id, ['*'], ['salaryComponents']);

                if($salaryGroup)
                {
                    $salaryComponents = $salaryGroup->salaryComponents->toArray();
                }
            }
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            return view($this->view.'edit_salary', compact('employee','employeeSalary','percentType','fixedType','salaryComponents','currency'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $employeeId
     * @return RedirectResponse
     */
    public function updateSalary(Request $request, $employeeId): RedirectResponse
    {
        $this->authorize('edit_salary');
        $this->checkSalaryEmployeeBranch($employeeId);
        $validatedData = $this->validatedSalaryData($request, $employeeId);
        try{
            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);
            if (!$employeeSalary) {
                return redirect()->route('admin.employee-salaries.index')->with('danger', __('message.account_not_found'));
            }
            DB::transaction(function () use ($employeeId, $validatedData) {
                \App\Models\User::whereKey($employeeId)->lockForUpdate()->firstOrFail();
                $salary = \App\Models\EmployeeSalary::where('employee_id', $employeeId)->lockForUpdate()->firstOrFail();
                $before = $salary->toArray();
                $this->employeeSalaryRepository->update($salary, $validatedData);
                app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->syncProfile($salary->fresh(), $before);
            });

            return $this->salarySavedRedirect($request, $employeeId)->with('success',__('message.salary_update'));


        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    private function salarySavedRedirect(Request $request, $employeeId): RedirectResponse
    {
        if ($request->input('employee_modal') === '1') {
            return redirect()->route('admin.employees.profile.show', [
                'employee' => $employeeId, 'tab' => 'salary', 'employee_modal' => '1',
            ]);
        }

        return redirect()->route('admin.employee-salaries.index');
    }

    private function checkSalaryEmployeeBranch(int $employeeId): void
    {
        $employee = \App\Models\User::findOrFail($employeeId);
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }
    }

    private function validatedSalaryData(Request $request, $employeeId): array
    {
        $data = $request->validate([
            'payroll_type' => ['required', 'in:annual,hourly'],
            'payment_type' => ['required', 'in:monthly,weekly'],
            'annual_salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'basic_salary_type' => ['required', 'in:percent,fixed'],
            'basic_salary_value' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'hour_rate' => ['nullable', 'numeric', 'min:0'],
            'weekly_hours' => ['nullable', 'numeric', 'min:0'],
            'monthly_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $group = $this->salaryGroupEmployeeRepository->getSalaryGroupFromEmployeeId($employeeId);
        return app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->setupAmounts(
            $data + ['employee_id' => $employeeId, 'salary_group_id' => $group?->salary_group_id]
        );
    }

    public function printPayslip($employeePayslipId): Factory|View|RedirectResponse|Application
    {
        try{

            $this->authorize('print_payroll');
            $companyLogoPath = Company::UPLOAD_PATH;
            $payrollData = $this->generatePayrollService->getEmployeeAccountDetailToCreatePayslip($employeePayslipId);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);
            $numberToWords = new NumberToWords();

            return view($this->view.'print_payslip', compact( 'payrollData','companyLogoPath','currency','underTimeSetting','numberToWords'));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function makePayment($payslipId, Request $request)
    {
        try{

            $this->authorize('payroll_payment');
            $validator = Validator::make($request->all(), [
                'paid_on' => ['required'],
                'payment_method_id' => ['required'],
            ],
            [
                'paid_on.required' => __('message.payment_date_error'),
                'payment_method_id.required' => __('message.payment_method_error'),
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }



            $validatedData = $validator->validated();
            $employeePayslipData = [
                "paid_on" => $validatedData['paid_on'],
                "status" => PayslipStatusEnum::paid->value,
                "payment_method_id" => $validatedData['payment_method_id'],
            ];

            $employeePaySlipDetail = $this->payslipRepository->find($payslipId);

            $payslipYearMonth = AppHelper::getYearMonth($employeePaySlipDetail->salary_from);
            $payslipYear = $payslipYearMonth['year'];
            $payslipMonth = $payslipYearMonth['month'];
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            if($isBsEnabled){
                $currentYearMonth = AppHelper::getCurrentYearMonth();
                $currentYear = $currentYearMonth['year'];
                $currentMonth = $currentYearMonth['month'];
            }else{
                $currentYear = date('Y');
                $currentMonth = date('m');
            }


            if($payslipYear > $currentYear || ($currentYear == $payslipYear && $payslipMonth >= $currentMonth)){
                throw new Exception(__('message.payslip_payment_error'),400);
            }
            DB::beginTransaction();

            if($employeePaySlipDetail->tada > 0){
                $updateData = [
                    'is_settled'=>1,
                    'remark'=>'included in salary.',
                    'verified_by'=>auth()->user()?->id,
                ];
                $this->tadaService->makeSettlement($updateData, $employeePaySlipDetail);
            }

            // make advance salary settlement
            if($employeePaySlipDetail->advance_salary > 0){
                $advanceData = [
                    'is_settled'=>true,
                    'remark' => 'settled in payroll'
                ];
                $this->advanceSalaryService->advanceSalarySettlement($employeePaySlipDetail, $advanceData);
            }


            $this->payslipRepository->update($employeePaySlipDetail, $employeePayslipData);

            DB::commit();

            $message = "Your salary has been paid on " . $validatedData['paid_on'] . ". Any pending advances have been settled.";
            SMPushHelper::sendSalaryNotification(__('message.salary_notification'), $message,$employeePaySlipDetail->employee_id);
            return response()->json(['success' => true]);
        }catch(Exception $exception){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param $employeeId
     * @return RedirectResponse
     */
    public function deleteSalary($employeeId): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('delete_salary');
        $this->checkSalaryEmployeeBranch($employeeId);
        try{


            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);
            if (!$employeeSalary) {
                if (request()->expectsJson()) return response()->json(['message' => __('message.account_not_found')], 404);
                return redirect()->route('admin.employee-salaries.index')->with('danger', __('message.account_not_found'));
            }

            app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->remove($employeeSalary);
            if (request()->expectsJson()) return response()->json(['message' => __('message.salary_delete')]);

            return redirect()->route('admin.employee-salaries.index')->with('success',__('message.salary_delete'));


        }catch(Exception $exception){
            DB::rollBack();
            if (request()->expectsJson()) return response()->json(['message' => $exception->getMessage()], 422);
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function getWeeks($year)
    {
        try{
            $weeks = AppHelper::getweeksList($year);

            return response()->json(['success' => true,'data'=>$weeks]);

        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function calculateTax(Request $request){
        try{
            $maritalStatus = $request->query('marital_status');
            $annualIncome = $request->query('salary');

            $data = PayrollHelper::salaryTDSCalculator($maritalStatus, $annualIncome);

            return response()->json(['success' => true,'data'=>$data]);

        }catch(Exception $exception){
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function getEmployeeSalaryData($type)
    {
        try {

            $users = $this->employeeSalaryRepository->getPayrollTypeEmployee($type);

            return response()->json([
                'users' => $users,
            ],200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }
    public function getEmployeeByDepartmentPayrollType(Request $request)
    {
        try {
            $departmentIds = $request->input('department_ids');
            $payrollType = $request->input('payroll_type');
            $users = $this->employeeSalaryRepository->getDPayrollTypeDepartmentEmployee($departmentIds,$payrollType);

            return response()->json([
                'users' => $users,
            ],200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }

    public function salaryImport(): Factory|View|Application
    {
        $this->authorize('create_salary');

        return view($this->view.'importSalaries');
    }

    public function importSalaries(Request $request): RedirectResponse
    {
        $this->authorize('create_salary');

        try {
            $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,csv,txt'],
            ]);

            DB::transaction(fn () => Excel::import(new EmployeeSalaryImport(), $request->file('file')));

            return redirect()
                ->route('admin.employee-salaries.index')
                ->with('success', __('message.salaries_imported'));
        } catch (Exception $exception) {
            Log::error('Employee salary import failed', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

}
