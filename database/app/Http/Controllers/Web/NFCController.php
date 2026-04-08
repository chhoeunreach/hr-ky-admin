<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Nfc\NfcService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class NFCController extends Controller
{

    private $view = 'admin.nfc.';

    public function __construct(Public NfcService $nfcService)
    {}

    /**
     * @return Application|Factory|View|RedirectResponse
     * @throws AuthorizationException
     */
    public function index(): View|Factory|RedirectResponse|Application
    {
        $this->authorize('list_nfc');

        try {
            $nfcData = $this->nfcService->getAllNfc();
            return view($this->view . 'index', compact('nfcData'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     */
    public function delete($id): RedirectResponse
    {
        try {
            $this->authorize('delete_nfc');

            DB::beginTransaction();
            $this->nfcService->deleteNfcDetail($id);
            DB::commit();
            return redirect()->route('admin.nfc.index')->with('success', __('nfc.nfc_deleted'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
