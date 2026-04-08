<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Requests\Client\ClientRequest;
use App\Services\Client\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    private $view = 'admin.client.';

    private ClientService $clientService;
    private BranchRepository $branchRepository;

    public function __construct(ClientService $clientService, BranchRepository $branchRepository)
    {
        $this->clientService = $clientService;
        $this->branchRepository = $branchRepository;
    }

    public function index()
    {
        $this->authorize('view_client_list');
        try{
            $clientLists = $this->clientService->getAllClientsList();
            return view($this->view.'index', compact('clientLists'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_client');
        try{
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'create',compact('branch'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(ClientRequest $request)
    {
        $this->authorize('create_client');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->clientService->saveClientDetail($validatedData);
            DB::commit();
            return redirect()->route('admin.clients.index')->with('success', __('message.add_client'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function ajaxClientStore(ClientRequest $request): JsonResponse
    {
        try{
            $this->authorize('create_client');
            $validatedData = $request->validated();
            DB::beginTransaction();
            $client = $this->clientService->saveClientDetail($validatedData);
            DB::commit();
            return AppHelper::sendSuccessResponse( __('message.add_client'),$client);
        }catch(\Exception $exception){
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());

        }
    }

    public function show($clientId)
    {
        $this->authorize('show_client_detail');
        try{
            $select = ['*'];
            $with = [
                'projects:id,client_id,name,start_date,deadline,cost,status',
                'projects.tasks:id,project_id',
                'projects.completedTask:id,project_id'
            ];
            $clientDetail = $this->clientService->findClientDetailById($clientId,$select,$with);
            return view($this->view.'show', compact('clientDetail'));
        }catch(\Exception $e){

            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_client');
        try{
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            $clientDetail = $this->clientService->findClientDetailById($id);
            return view($this->view.'edit', compact('clientDetail','branch'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(ClientRequest $request, $id)
    {
        $this->authorize('edit_client');
        try{
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->clientService->updateClientDetail($validatedData,$id);
            DB::commit();
            return redirect()->route('admin.clients.index')
                ->with('success', __('message.update_client'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function toggleIsActiveStatus($id)
    {
        $this->authorize('edit_client');
        try{
            DB::beginTransaction();
            $this->clientService->toggleIsActiveStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){

            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }


    public function delete($id)
    {
        $this->authorize('delete_client');
        try{
            DB::beginTransaction();
            $this->clientService->deleteClientDetail($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_client'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

}
