<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\Payroll\SSF\SSfRequest;
use App\Services\Payroll\SSFService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SSFController extends Controller
{
    private $view = 'admin.payrollSetting.ssf.';


    public function __construct(protected SSFService $ssfService)
    {}

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('ssf');
        try {
            $ssfDetail = $this->ssfService->findSSFDetail();
            return view($this->view . 'index', compact('ssfDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(SSfRequest $request)
    {
        $this->authorize('ssf');
        try {
            $validatedData = $request->all();
            DB::beginTransaction();
            $this->ssfService->storeSSF($validatedData);
            DB::commit();
            return redirect()->route('admin.ssf.index')->with('success', __('message.ssf_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.ssf.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(SSfRequest $request, $id)
    {

        $this->authorize('ssf');
        try {

            $validatedData = $request->all();
            DB::beginTransaction();
            $this->ssfService->updateSSF($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.ssf.index')
                ->with('success', __('message.ssf_update'));

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.ssf.index')
                ->with('danger', $e->getMessage())
                ->withInput();

        }
    }
}
