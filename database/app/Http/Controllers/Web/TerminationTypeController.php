<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Services\Termination\TerminationTypeService;
use App\Services\TrainingManagement\TrainingTypeService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TerminationTypeController extends Controller
{

    private $view = 'admin.terminationManagement.types.';

    public function __construct(
       protected TerminationTypeService $terminationTypeService, protected BranchRepository $branchRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('termination_type_list');
        try{
            $select = ['*'];
            $with = ['terminations'];
            $terminationTypes = $this->terminationTypeService->getAllTerminationTypes($select,$with);
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'index', compact('terminationTypes','branches'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create_termination_type');
        try{
            $validatedData = $request->all();
            DB::beginTransaction();
            $this->terminationTypeService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.termination-types.index')->with('success', __('message.add_termination_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Application|Factory|View|RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function show($id): View|Factory|Response|RedirectResponse|Application
    {
        $this->authorize('show_termination_type');
        try{
            $select = ['*'];
            $with = ['terminations'];
            $terminationType = $this->terminationTypeService->findTerminationTypeById($id,$select,$with);

            return view($this->view.'show', compact('terminationType'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update_termination_type');
        try{
            $validatedData = $request->all();
            DB::beginTransaction();
            $this->terminationTypeService->updateTerminationType($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.termination-types.index')
                ->with('success', __('message.update_termination_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_termination_type');
        try{
            DB::beginTransaction();
            $this->terminationTypeService->deleteTerminationType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_termination_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('update_termination_type');
        try{
            $this->terminationTypeService->toggleStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
}
