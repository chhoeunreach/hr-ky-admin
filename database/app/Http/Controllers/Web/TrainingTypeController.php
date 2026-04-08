<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Services\TrainingManagement\TrainingService;
use App\Services\TrainingManagement\TrainingTypeService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainingTypeController extends Controller
{

    private $view = 'admin.trainingManagement.types.';

    public function __construct(
        protected TrainingTypeService $trainingTypeService, protected TrainingService $trainingService, protected BranchRepository $branchRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function index()
    {
        $this->authorize('training_type_list');
        try{
            $select = ['*'];
            $with = ['trainings'];
            $companyId = AppHelper::getAuthUserCompanyId();
            $trainingTypes = $this->trainingTypeService->getAllTrainingTypes($select,$with);
            $selectBranch = ['id','name'];
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'index', compact('trainingTypes','branches'));
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
    public function store(Request $request)
    {
        $this->authorize('create_training_type');
        try{
            $validatedData = $request->all();
            DB::beginTransaction();
            $this->trainingTypeService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.training-types.index')->with('success', __('message.add_training_type'));
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
    public function show($id): View|Factory|Response|RedirectResponse|Application
    {
        $this->authorize('show_training_type');
        try{
            $select = ['*'];
            $with = ['trainings'];
            $trainingType = $this->trainingTypeService->findTrainingTypeById($id,$select,$with);

            return view($this->view.'show', compact('trainingType'));
        }catch(Exception $exception){
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
    public function update(Request $request, $id)
    {
        $this->authorize('update_training_type');
        try{
            $validatedData = $request->all();
            DB::beginTransaction();
            $this->trainingTypeService->updateTrainingType($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.training-types.index')
                ->with('success', __('message.update_training_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_training_type');
        try{

            $checkTrainingType = $this->trainingService->checkType($id);
            if ($checkTrainingType) {
                return redirect()->back()->with('danger',  __('message.training_type_delete_error'));
            }
            DB::beginTransaction();
            $this->trainingTypeService->deleteTrainingType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_training_type'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('update_training_type');
        try{

            $checkTrainingType = $this->trainingService->checkType($id);
             if ($checkTrainingType) {
                 return redirect()->back()->with('danger',  __('message.training_type_status_change_error'));
             }

            $this->trainingTypeService->toggleStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }


}
