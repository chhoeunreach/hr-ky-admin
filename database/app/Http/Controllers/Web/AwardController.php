<?php

namespace App\Http\Controllers\Web;

use App\Enum\AwardBaseEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Requests\AwardManagement\AwardRequest;
use App\Services\AwardManagement\AwardService;
use App\Services\AwardManagement\AwardTypeService;
use Exception;
use http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AwardController extends Controller
{
    private $view = 'admin.awardManagement.awardDetail.';

    public function __construct(
        protected AwardService $awardService, protected AwardTypeService $awardTypeService, protected UserRepository $userRepository
    ){}

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $this->authorize('award_list');
        try{
            $select = ['*'];
            $with = ['type:id,title','employee:id,name'];
            $awardLists = $this->awardService->getAllAwardPaginated($select,$with);

            return view($this->view.'index', compact('awardLists'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $this->authorize('create_award');

        try{
            $awardTypes = $this->awardTypeService->getAllActiveAwardTypes(['id','title']);
            $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);
            $awardBases = AwardBaseEnum::cases();
            $rewardCode = rand(1000, 9999);
            return view($this->view.'create', compact('employees','awardTypes','awardBases','rewardCode'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function store(AwardRequest $request)
    {
        $this->authorize('create_award');

        try{
            $validatedData = $request->validated();
            $this->awardService->saveAwardDetail($validatedData);
            return redirect()->route('admin.awards.index')->with('success',__('message.add_award') );
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id

     */
    public function show($id)
    {
        $this->authorize('show_award');

        try{
            $select = ['*'];
            $with = ['type:id,title','employee:id,name'];
            $awardDetail = $this->awardService->findAwardById($id,$select,$with);

            return view($this->view.'show', compact('awardDetail'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $this->authorize('update_award');
        try{
            $awardDetail = $this->awardService->findAwardById($id);
            $awardTypes = $this->awardTypeService->getAllActiveAwardTypes(['id','title']);
            $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);
            $awardBases = AwardBaseEnum::cases();

            return view($this->view.'edit', compact('awardDetail','employees','awardTypes','awardBases'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AwardRequest $request, $id)
    {
        $this->authorize('update_award');
        try{
            $validatedData = $request->validated();
            $this->awardService->updateAwardDetail($id,$validatedData);
            return redirect()->route('admin.awards.index')
                ->with('success', __('message.update_award'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_award');
        try{
            DB::beginTransaction();
            $this->awardService->deleteAward($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_award'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
}
