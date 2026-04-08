<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\UnderTimeSetting;
use App\Repositories\UserRepository;
use App\Requests\Payroll\UnderTime\UnderTimeRequest;
use App\Services\Payroll\UnderTimeSettingService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UnderTimeSettingController extends Controller
{
    private $view = 'admin.payrollSetting.under_time.';

    public function __construct(protected UnderTimeSettingService $utSettingService, protected UserRepository $userRepository)
    {}


    public function index(): Factory|View|RedirectResponse|Application
    {

        $this->authorize('undertime_setting');
        try {
            $underTimeData = $this->utSettingService->getAllUTList();
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            return view($this->view . 'index', compact('underTimeData','currency'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @return Application|Factory|View|RedirectResponse
     */
    public function create(): View|Factory|RedirectResponse|Application
    {
        $this->authorize('undertime_setting');
        try {

            $underTime = $this->utSettingService->getAllUTList(['*'],1);


            if(isset($underTime)){
                return redirect()->route('admin.under-time.edit',$underTime->id);
            }else{
                return view($this->view . 'create');
            }
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param UnderTimeRequest $request
     * @return View|Factory|Response|RedirectResponse|Application
     */
    public function store(UnderTimeRequest $request): View|Factory|Response|RedirectResponse|Application
    {
        $this->authorize('undertime_setting');
        try {

            $validatedData = $request->all();

            $underTime = $this->utSettingService->store($validatedData);

            return redirect()->route('admin.under-time.edit',$underTime->id)->with('success', __('message.ut_create'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        $this->authorize('undertime_setting');
        try {

            $with = ['utEmployees:under_time_setting_id,employee_id'];

            $underTime = $this->utSettingService->findUTById($id, $with);
//            $employees = $this->userRepository->pluckIdAndNameOfAllVerifiedEmployee();
//            $underTimeEmployeeId = $underTime?->utEmployees?->pluck('employee_id')->toArray() ?? [];

            return view($this->view . 'edit', compact('underTime'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param UnderTimeRequest $request
     * @param $utId
     * @return RedirectResponse
     */
    public function update(UnderTimeRequest $request, $utId): RedirectResponse
    {
        $this->authorize('undertime_setting');
        try {

            $validatedData = $request->all();

            $underTime = $this->utSettingService->updateUnderTime($utId, $validatedData);

            return redirect()->route('admin.under-time.edit',$underTime->id)->with('success',  __('message.ut_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id): RedirectResponse
    {
        $this->authorize('undertime_setting');
        try {
            DB::beginTransaction();
            $this->utSettingService->deleteUTSetting($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.ut_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleUTStatus($id): RedirectResponse
    {
        $this->authorize('undertime_setting');
        try {
            $this->utSettingService->changeUTStatus($id);
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
