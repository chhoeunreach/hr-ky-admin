<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\Payroll\SalaryComponent\SalaryComponentRequest;
use App\Services\Payroll\SalaryComponentService;
use Exception;

class SalaryComponentController extends Controller
{
    private $view = 'admin.payrollSetting.salaryComponent.';

    public function __construct(public SalaryComponentService $salaryComponentService)
    {
    }

    public function index()
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentLists = $this->salaryComponentService->getAllSalaryComponentList($select);
            return view($this->view . 'index', compact('salaryComponentLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('salary_component');
        try {

            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(SalaryComponentRequest $request)
    {
        $this->authorize('salary_component');
        try {

            $validatedData = $request->validated();
            $this->salaryComponentService->store($validatedData);
            return redirect()
                ->route('admin.salary-components.index')
                ->with('success', __('message.salary_component_add'));
        } catch (Exception $e) {
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
            return view($this->view . 'edit', compact('salaryComponentDetail'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function update(SalaryComponentRequest $request, $id)
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
            $validatedData = $request->validated();

            $validatedData['apply_for_all'] = $validatedData['apply_for_all'] ?? false;
            $this->salaryComponentService->updateDetail($salaryComponentDetail, $validatedData);
            return redirect()
                ->route('admin.salary-components.index')
                ->with('success', __('message.salary_component_update'));
        } catch (Exception $e) {
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('salary_component');
        try {

            $select = ['*'];
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
            $this->salaryComponentService->deleteSalaryComponentDetail($salaryComponentDetail);
            return redirect()
                ->back()
                ->with('success', __('message.salary_component_delete'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleSalaryComponentStatus($id)
    {
        $this->authorize('salary_component');

        try {
            $select = ['*'];
            $salaryComponentDetail = $this->salaryComponentService->findSalaryComponentById($id, $select);
            $this->salaryComponentService->changeSalaryComponentStatus($salaryComponentDetail);
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
