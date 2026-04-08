<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Requests\Payroll\Bonus\BonusRequest;
use App\Services\Payroll\BonusService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class BonusController extends Controller
{
    private $view = 'admin.payrollSetting.bonus.';

    public function __construct(public BonusService $bonusService)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {

        $this->authorize('bonus');
        try {


            $bonusList = $this->bonusService->getAllBonusList();
            return view($this->view . 'index', compact('bonusList'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('bonus');
        try {

            $months = AppHelper::getMonthsList();
            return view($this->view . 'create', compact('months'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(BonusRequest $request)
    {
        $this->authorize('bonus');
        try {

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->bonusService->store($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.bonus.index')
                ->with('success', __('message.add_bonus'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('bonus');
        try {


            $months = AppHelper::getMonthsList();

            $bonusDetail = $this->bonusService->findBonusById($id);
            return view($this->view . 'edit', compact('bonusDetail','months'));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function update(BonusRequest $request, $id)
    {
        $this->authorize('bonus');
        try {

            $validatedData = $request->validated();
            $bonusDetail = $this->bonusService->findBonusById($id);
            DB::beginTransaction();
            $this->bonusService->updateDetail($bonusDetail, $validatedData);
            DB::commit();
            return redirect()
                ->route('admin.bonus.index')
                ->with('success', __('message.update_bonus'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('bonus');
        try {

            $bonusDetail = $this->bonusService->findBonusById($id);
            DB::beginTransaction();
            $this->bonusService->deleteBonusDetail($bonusDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.delete_bonus'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleBonusStatus($id)
    {
        $this->authorize('bonus');
        try {

            $bonusDetail = $this->bonusService->findBonusById($id);
            DB::beginTransaction();
            $this->bonusService->changeBonusStatus($bonusDetail);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
