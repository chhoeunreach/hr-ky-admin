<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Requests\AssetManagement\AssetDetailRequest;
use App\Services\AssetManagement\AssetService;
use App\Services\AssetManagement\AssetTypeService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    private $view = 'admin.assetManagement.assetDetail.';

    public function __construct(
        protected AssetService     $assetService,
        protected AssetTypeService $assetTypeService,
        protected UserRepository   $userRepo
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_assets');
        try {
            $filterParameters = [
                    'name' => $request->name ?? null,
                    'purchased_from' => $request->purchased_from ?? null,
                    'purchased_to' => $request->purchased_to ?? null,
                    'is_working' => $request->is_working ?? null,
                    'is_available' => $request->is_available ?? null,
                    'type' => $request->type ?? null,
            ];
            $select = ['*'];
            $with = ['type:id,name','assignedTo:id,name'];
            $assetType = $this->assetTypeService->getAllAssetTypes(['id','name']);
            $assetLists = $this->assetService->getAllAssetsPaginated($filterParameters,$select,$with);
            return view($this->view . 'index', compact('assetLists','assetType','filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_assets');
        try {
            $employeeSelect = ['id','name'];
            $typeSelect = ['id','name'];
            $assetType = $this->assetTypeService->getAllActiveAssetTypes($typeSelect);
            $employees = $this->userRepo->getAllVerifiedEmployeeOfCompany($employeeSelect);
            return view($this->view . 'create',compact('assetType','employees'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(AssetDetailRequest $request)
    {
        $this->authorize('create_assets');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->assetService->saveAssetDetail($validatedData);
            DB::commit();
            return redirect()->route('admin.assets.index')->with('success', __('message.asset_saved'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_asset');
        try {
            $select = ['*'];
            $with = ['type:id,name','assignedTo:id,name'];
            $assetDetail = $this->assetService->findAssetById($id,$select,$with,);
            return view($this->view . 'show', compact('assetDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('edit_assets');
        try {
            $employeeSelect = ['id','name'];
            $typeSelect = ['id','name'];
            $assetType = $this->assetTypeService->getAllActiveAssetTypes($typeSelect);
            $employees = $this->userRepo->getAllVerifiedEmployeeOfCompany($employeeSelect);
            $assetDetail = $this->assetService->findAssetById($id);
            return view($this->view . 'edit', compact('assetDetail','assetType','employees'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(AssetDetailRequest $request, $id)
    {
        $this->authorize('edit_assets');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->assetService->updateAssetDetail($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.assets.index')
                ->with('success', __('message.asset_update'));
        } catch (Exception $exception) {
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
        $this->authorize('delete_assets');
        try {
            DB::beginTransaction();
                $this->assetService->deleteAsset($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.asset_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function changeAvailabilityStatus($id)
    {
        $this->authorize('edit_assets');
        try {
            DB::beginTransaction();
            $this->assetService->toggleAvailabilityStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
