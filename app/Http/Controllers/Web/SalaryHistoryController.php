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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            if (request()->boolean('salary_history_modal')) {
                return view($this->view.'partials.history-content', compact('salaryReviseLists', 'employeeDetail', 'currency'));
            }
            return view($this->view.'salary_history',compact('salaryReviseLists','employeeDetail','currency'));
        }catch (Exception $exception){
            report($exception);
            if (request()->expectsJson()) return response()->json(['message' => __('index.salary_review_load_error')], 500);
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
    public function edit($employeeId, SalaryReviseHistory $history)
    {
        $employeeDetail = $this->authorizeHistory($employeeId, $history, 'employee.salary.history.edit');
        return view($this->view.'partials.history-form', ['history' => $history, 'employeeDetail' => $employeeDetail, 'deleting' => false]);
    }

    public function confirmDelete($employeeId, SalaryReviseHistory $history)
    {
        $employeeDetail = $this->authorizeHistory($employeeId, $history, 'employee.salary.history.delete');
        return view($this->view.'partials.history-form', ['history' => $history, 'employeeDetail' => $employeeDetail, 'deleting' => true]);
    }

    public function update(Request $request, $employeeId, SalaryReviseHistory $history)
    {
        $this->authorizeHistory($employeeId, $history, 'employee.salary.history.edit');
        $data = $request->validate([
            'remark' => ['nullable', 'string', 'max:2000'],
            'base_salary' => ['prohibited'], 'revised_salary' => ['prohibited'], 'increment_amount' => ['prohibited'],
            'increment_percent' => ['prohibited'], 'date_from' => ['prohibited'], 'salary_revised_on' => ['prohibited'],
            'employee_id' => ['prohibited'],
        ]);
        DB::transaction(function () use ($history, $employeeId, $data, $request) {
            \App\Models\User::whereKey($employeeId)->lockForUpdate()->firstOrFail();
            $record = SalaryReviseHistory::whereKey($history->id)->lockForUpdate()->firstOrFail();
            $old = $record->toArray();
            $record->update(['remark' => $data['remark'] ?? null]);
            $this->auditHistory($record, 'revision_update', $old, $record->toArray(), $request);
        });
        return response()->json(['message' => __('index.salary_history_updated')]);
    }

    public function destroy(Request $request, $employeeId, SalaryReviseHistory $history)
    {
        $this->authorizeHistory($employeeId, $history, 'employee.salary.history.delete');
        DB::transaction(function () use ($history, $employeeId, $request) {
            \App\Models\User::whereKey($employeeId)->lockForUpdate()->firstOrFail();
            $record = SalaryReviseHistory::whereKey($history->id)->lockForUpdate()->firstOrFail();
            $this->auditHistory($record, 'revision_delete', $record->toArray(), null, $request);
            $record->delete();
        });
        return response()->json(['message' => __('index.salary_history_deleted')]);
    }

    private function authorizeHistory(int $employeeId, SalaryReviseHistory $history, string $ability): \App\Models\User
    {
        $this->authorize('show_salary_history');
        $this->authorize($ability);
        $employee = $this->checkEmployeeBranch($employeeId);
        abort_unless((int) $history->employee_id === $employeeId, 404);
        return $employee;
    }

    private function auditHistory(SalaryReviseHistory $record, string $action, array $before, ?array $after, Request $request): void
    {
        \App\Models\EmployeeProfileAuditLog::create([
            'employee_id' => $record->employee_id, 'module' => 'salary', 'action' => $action, 'record_id' => $record->id,
            'old_values' => $before, 'new_values' => $after === null ? null : $after + ['performed_by_name' => (auth('admin')->user() ?? auth()->user())?->name],
            'performed_by' => auth('admin')->check() ? null : auth()->id(),
            'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 65535), 'created_at' => now(),
        ]);
    }

    private function checkEmployeeBranch(int $employeeId): \App\Models\User
    {
        $employee = \App\Models\User::findOrFail($employeeId);
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }
        return $employee;
    }

}
