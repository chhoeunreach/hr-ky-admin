<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetType;
use App\Requests\AssetManagement\AssetTypeRequest;
use App\Services\AssetManagement\AssetTypeService;
use Illuminate\Support\Facades\DB;

class AssetTypeController extends Controller
{
    private $view = 'admin.assetManagement.types.';

    public function __construct(
        protected AssetTypeService $assetTypeService
    ){}

    public function index()
    {
        $this->authorize('list_type');
        try{
            $select = ['*'];
            $with = ['assets'];
            $assetTypeLists = $this->assetTypeService->getAllAssetTypes($select,$with);
            return view($this->view.'index', compact('assetTypeLists'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function create()
    {
        $this->authorize('create_type');
        try{
            return view($this->view.'create');
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function store(AssetTypeRequest $request)
    {
        $this->authorize('create_type');
        try{
            $validatedData = $request->validated();
            $this->assetTypeService->store($validatedData);
            return redirect()->route('admin.asset-types.index')->with('success',  __('message.asset_type_create'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize('show_type');
        try{
            $select = ['*'];
            $with = ['assets:id,type_id,name,purchased_date,is_working,is_available'];
            $assetTypeDetail = $this->assetTypeService->findAssetTypeById($id,$select,$with);
            return view($this->view.'show', compact('assetTypeDetail'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function edit($id)
    {
        $this->authorize('edit_type');
        try{
            $assetTypeDetail = $this->assetTypeService->findAssetTypeById($id);
            return view($this->view.'edit', compact('assetTypeDetail'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function update(AssetTypeRequest $request, $id)
    {
        $this->authorize('edit_type');
        try{
            $validatedData = $request->validated();
            $this->assetTypeService->updateAssetType($id,$validatedData);
            return redirect()->route('admin.asset-types.index')
                ->with('success',  __('message.asset_type_update'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_type');
        try{
            DB::beginTransaction();
                $this->assetTypeService->deleteAssetType($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.asset_type_delete'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleIsActiveStatus($id)
    {
        $this->authorize('edit_type');
        try{
            $this->assetTypeService->toggleIsActiveStatus($id);
            return redirect()->back()->with('success', __('message.status_changed'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
}
