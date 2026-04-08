<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Requests\AwardManagement\AwardTypeRequest;
use App\Services\AwardManagement\AwardService;
use App\Services\AwardManagement\AwardTypeService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AwardTypeController extends Controller
{

    private $view = 'admin.awardManagement.types.';

    public function __construct(
        protected AwardTypeService $awardTypeService, protected AwardService $awardService, protected BranchRepository $branchRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function index()
    {
        $this->authorize('award_type_list');
        try{
            $select = ['*'];
            $with = ['awards'];
            $awardTypes = $this->awardTypeService->getAllAwardTypes($select,$with);

            return view($this->view.'index', compact('awardTypes'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function create()
    {
        $this->authorize('create_award_type');

        try{
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'create',compact('branch'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function store(AwardTypeRequest $request)
    {
        $this->authorize('create_award_type');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->awardTypeService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.award-types.index')->with('success', __('message.add_award_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function show($id)
    {

        try{
            $select = ['*'];
            $with = ['awards.employee'];
            $awardTypes = $this->awardTypeService->findAwardTypeById($id,$select,$with);

            return view($this->view.'show', compact('awardTypes'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function edit($id)
    {
        $this->authorize('update_award_type');
        try{
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            $awardTypeDetail = $this->awardTypeService->findAwardTypeById($id);
            return view($this->view.'edit', compact('awardTypeDetail','branch'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return RedirectResponse|Response
     */
    public function update(AwardTypeRequest $request, $id)
    {
        $this->authorize('update_award_type');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->awardTypeService->updateAwardType($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.award-types.index')
                ->with('success', __('message.update_award_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_award_type');
        try{
            $checkAwardType = $this->awardService->checkAwardType($id);
            if ($checkAwardType) {
                return redirect()->back()->with('danger',  __('message.award_type_status_change_error'));
            }
            DB::beginTransaction();
            $this->awardTypeService->deleteAwardType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_award_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('update_award_type');
        try{
            $checkAwardType = $this->awardService->checkAwardType($id);
            if ($checkAwardType) {
                return redirect()->back()->with('danger',  __('message.award_type_status_change_error'));
            }
            $this->awardTypeService->toggleStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
}
