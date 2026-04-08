<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Tada;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Requests\Tada\TadaRequest;
use App\Requests\Tada\TadaUpdateStatusRequest;
use App\Services\Tada\TadaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TadaController extends Controller
{
    private $view = 'admin.tada.';

    public TadaService $tadaService;
    public UserRepository $userRepo;

    public function __construct(TadaService $tadaService,UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
        $this->tadaService = $tadaService;
    }

    public function index(Request $request)
    {
        $this->authorize('view_tada_list');
        try {
            $filterParameters = [
                'employee' => $request->employee ?? null,
                'status' => $request->status ?? null
            ];
            $select = ['*'];
            $with = ['employeeDetail:id,name'];
            $tadaLists = $this->tadaService->getAllTadaDetailPaginated($filterParameters,$select,$with);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            return view($this->view . 'index',compact('tadaLists','filterParameters','currency'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_tada');
        try {
            $select = ['name','id'];
            $employee = $this->userRepo->getAllVerifiedEmployeeOfCompany($select);
            $attachments = [];
            return view($this->view . 'create',compact('employee','attachments'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(TadaRequest $request)
    {
        $this->authorize('create_tada');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->tadaService->store($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.tadas.index')
                ->with('success', __('message.add_tada'));
        }catch(Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize('show_tada_detail');
        try {
            $select= ['*'];
            $with = ['employeeDetail:id,name','attachments','verifiedBy:id,name'];
            $tadaDetail = $this->tadaService->findTadaDetailById($id,$with,$select);
            $attachments = $tadaDetail->attachments;
            return view($this->view . 'show',compact('tadaDetail','attachments'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_tada');
        try {
            $select= ['*'];
            $with = ['employeeDetail:id,name','attachments'];
            $tadaDetail = $this->tadaService->findTadaDetailById($id,$with,$select);
            $employeeSelect = ['name','id'];
            $employee = $this->userRepo->getAllVerifiedEmployeeOfCompany($employeeSelect);
            $attachments = $tadaDetail->attachments;
            return view($this->view .'edit',compact('tadaDetail','employee','attachments'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(TadaRequest $request, $id)
    {
        $this->authorize('edit_tada');
        try {
            $validatedData = $request->validated();
            $with=['attachments'];
            $tadaDetail = $this->tadaService->findTadaDetailById($id,$with);
            DB::beginTransaction();
            $this->tadaService->update($tadaDetail,$validatedData);
            DB::commit();
            return redirect()->route('admin.tadas.index')
                ->with('success', __('message.update_tada'));
        }catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_tada');
        try {
            DB::beginTransaction();
                $this->tadaService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_tada'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleTadaIsActive($id)
    {
        $this->authorize('edit_tada');
        try {
            DB::beginTransaction();
            $this->tadaService->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.tada_settlement_change'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeTadaStatus(TadaUpdateStatusRequest $request,$id)
    {
        $this->authorize('edit_tada');
        try {
            $validatedData = $request->validated();
            $tadaDetail = $this->tadaService->findTadaDetailById($id);
            $this->tadaService->changeTadaStatus($tadaDetail,$validatedData);
            return redirect()->back()->with('success', __('message.tada_status_change'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
