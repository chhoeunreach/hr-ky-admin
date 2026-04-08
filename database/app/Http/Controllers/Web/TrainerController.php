<?php

namespace App\Http\Controllers\Web;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\UserRepository;
use App\Requests\TrainingManagement\TrainerRequest;
use App\Services\TrainingManagement\TrainerService;
use App\Services\TrainingManagement\TrainingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainerController extends Controller
{
    private string $view = 'admin.trainingManagement.trainer.';

    public function __construct(
        protected TrainerService $trainerService, protected UserRepository $userRepository, protected BranchRepository $branchRepository, protected TrainingService $trainingService
    ){}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('list_trainer');
        try{
            $select = ['*'];
            $with = ['employee:id,name,email,phone'];
            $trainerLists = $this->trainerService->getAllTrainerPaginated($select,$with);

            return view($this->view.'index', compact('trainerLists'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_trainer');

        try{
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $trainerTypes = TrainerTypeEnum::cases();
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'create', compact('trainerTypes','branch'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @throws AuthorizationException
     */
    public function store(TrainerRequest $request)
    {
        $this->authorize('create_trainer');

        try{
            $validatedData = $request->validated();
            $this->trainerService->saveTrainerDetail($validatedData);
            return redirect()->route('admin.trainers.index')->with('success',__('message.add_trainer') );
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_trainer');

        try{
            $select = ['*'];
            $with = ['employee:id,name,email,phone,address','branch:id,name','department:id,dept_name'];
            $trainerDetail = $this->trainerService->findTrainerById($id,$select,$with);

            return view($this->view.'show', compact('trainerDetail'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('update_trainer');
        try{
            $trainerDetail = $this->trainerService->findTrainerById($id);
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $trainerTypes = TrainerTypeEnum::cases();
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'edit', compact('trainerDetail','trainerTypes','branch'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function update(TrainerRequest $request, $id)
    {
        $this->authorize('update_trainer');
        try{
            $validatedData = $request->validated();
            $this->trainerService->updateTrainerDetail($id,$validatedData);
            return redirect()->route('admin.trainers.index')
                ->with('success', __('message.update_trainer'));
        }catch(Exception $exception){
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
        $this->authorize('delete_trainer');
        try{

            $checkTrainer = $this->trainingService->checkTrainer($id);
            if ($checkTrainer) {
                return redirect()->back()->with('danger',  __('message.trainer_delete_error'));
            }
            DB::beginTransaction();
            $this->trainerService->deleteTrainer($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_trainer'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('update_trainer');
        try{

            $checkTrainer = $this->trainingService->checkTrainer($id);
            if ($checkTrainer) {
                return redirect()->back()->with('danger',  __('message.trainer_status_change_error'));
            }

            $this->trainerService->toggleStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function getAllTrainersByType($type): JsonResponse|RedirectResponse
    {
        try {

            $trainers = $this->trainerService->getTrainerByType($type);
            return response()->json([
                'data' => $trainers
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }
}
