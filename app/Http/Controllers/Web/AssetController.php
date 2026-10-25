<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetType;
use App\Exports\AssetTableExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\AssetManagement\AssetDetailRequest;
use App\Services\AssetManagement\AssetService;
use App\Services\AssetManagement\AssetTypeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.assetManagement.assetDetail.';

    public function __construct(
        protected AssetService     $assetService,
        protected AssetTypeService $assetTypeService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_assets');
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'sort' => ['nullable', \Illuminate\Validation\Rule::in(Asset::SORTABLE_COLUMNS)],
            'direction' => ['nullable', \Illuminate\Validation\Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', \Illuminate\Validation\Rule::in(['10', '25', '50', '100', 'all'])],
            'export' => ['nullable', \Illuminate\Validation\Rule::in(['csv', 'excel', 'word', 'print', 'pdf', 'json'])],
            'columns' => ['nullable', 'array', 'min:1'],
            'columns.*' => ['string', \Illuminate\Validation\Rule::in(array_keys($this->assetTableColumns()))],
            'orientation' => ['nullable', \Illuminate\Validation\Rule::in(['portrait', 'landscape'])],
        ]);
        try {
            $filterParameters = [
                'q' => $request->input('q'),
                'sort' => $request->input('sort'),
                'direction' => $request->input('direction') ?: 'asc',
                'per_page' => $request->input('per_page') ?: '25',
                'exporting' => $request->filled('export'),
                'branch_id' => $request->branch_id ?? null,
                'name' => $request->name ?? null,
                'purchased_from' => $request->purchased_from ?? null,
                'purchased_to' => $request->purchased_to ?? null,
                'is_working' => $request->is_working ?? null,
                'is_available' => $request->is_available ?? null,
                'type_id' => $request->type_id ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['type:id,name','latestAssignment.user:id,name,avatar', 'setBy:id,name'];
            $assetLists = $this->assetService->getAllAssetsPaginated($filterParameters,$select,$with);
            if ($request->filled('export')) {
                return $this->exportAssetTable($request, $assetLists);
            }

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('assetLists','companyDetail','filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function activity(Request $request)
    {
        $this->authorize('asset_assign_list');
        $request->validate(['asset_id' => ['nullable', 'integer'], 'event' => ['nullable', 'in:asset_created,asset_updated,asset_deleted,assignment_created,assignment_updated,assignment_deleted']]);
        $activities = \App\Models\AssetActivity::query()
            ->when(!auth('admin')->check() && auth()->user()?->branch_id, fn ($query) => $query->where('branch_id', auth()->user()->branch_id))
            ->when($request->filled('asset_id'), fn ($query) => $query->where('asset_id', $request->integer('asset_id')))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->input('event')))
            ->orderByDesc('id')->paginate(25)->withQueryString();
        return view($this->view.'activity', compact('activities'));
    }

    private function assetTableColumns(): array
    {
        $columns = [
            'employee' => __('index.employee'), 'name' => __('index.name'), 'phone_numbers' => __('index.phone_number'),
            'icloud' => __('index.asset_icloud'), 'asset_password' => __('index.asset_device_password'),
            'screen_time_passcode' => __('index.asset_screen_time_passcode'), 'set_by' => __('index.asset_set_by'), 'asset_code' => __('index.asset_code'),
            'asset_serial_no' => __('index.asset_serial_number'), 'description' => __('index.description'),
            'type' => __('index.type'), 'is_working' => __('index.is_working'), 'is_available' => __('index.is_available'),
        ];
        if (auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('assign_asset')) {
            $columns['assigned_to'] = __('index.assign_to');
        }
        return $columns;
    }

    private function exportAssetTable(Request $request, $assets)
    {
        $columns = $this->assetTableColumns();
        $selected = $request->input('columns') ?: array_keys($columns);
        $columns = array_intersect_key($columns, array_flip($selected));
        $headings = array_values($columns);
        $rows = $assets->values()->map(function ($asset, $index) use ($columns) {
            $data = [
                'employee' => $asset->latestAssignment && !$asset->latestAssignment->returned_date ? $asset->latestAssignment->user?->name : null,
                'name' => $asset->name,
                'phone_numbers' => $asset->phone_numbers,
                'icloud' => $asset->icloud, 'asset_password' => $asset->getRawOriginal('asset_password') ? '••••••••' : '',
                'screen_time_passcode' => $asset->getRawOriginal('screen_time_passcode') ? '••••' : '', 'set_by' => $asset->setBy?->name,
                'asset_code' => $asset->asset_code, 'asset_serial_no' => $asset->asset_serial_no,
                'description' => trim(html_entity_decode(strip_tags(preg_replace('/<\/(p|div)>|<br\s*\/?>/i', "\n", $asset->note ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                'type' => $asset->type?->name, 'is_working' => __('index.' . $asset->is_working),
                'is_available' => $asset->is_available ? __('index.yes') : __('index.no'),
                'assigned_to' => $asset->latestAssignment && !$asset->latestAssignment->returned_date ? $asset->latestAssignment->user?->name : null,
            ];
            return array_values(array_map(fn ($key) => (string) ($data[$key] ?? ''), array_keys($columns)));
        })->all();
        $format = $request->input('export');
        if ($format === 'json') return response()->json(compact('headings', 'rows'));
        $filename = 'assets-' . now()->format('Ymd');
        if (in_array($format, ['csv', 'excel'], true)) {
            if ($format === 'csv') {
                $rows = array_map(fn ($row) => array_map(fn ($value) => preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value, $row), $rows);
            }
            return Excel::download(new AssetTableExport($headings, $rows), $filename . ($format === 'csv' ? '.csv' : '.xlsx'));
        }
        $view = view('admin.assetManagement.assetDetail.exports.table', [
            'headings' => $headings, 'rows' => $rows, 'format' => $format,
            'orientation' => $request->input('orientation') ?: 'landscape',
        ]);
        if ($format === 'word') {
            return response($view->render())->header('Content-Type', 'application/msword; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.doc"');
        }
        return $view;
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_assets');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $setByOptions = Asset::setupPeople();
            return view($this->view . 'create',compact('companyDetail', 'setByOptions'));
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
            if ($request->input('asset_modal') === '1') {
                session()->flash('success', __('message.asset_saved'));
                return view($this->view . 'common.modal-saved', ['message' => __('message.asset_saved')]);
            }
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
            $with = ['type:id,name'];
            $assetDetail = $this->assetService->findAssetById($id,$select,$with,);


            $assetDetail->asset_password_status = $assetDetail->getRawOriginal('asset_password') ? '••••••••' : '—';
            $assetDetail->screen_time_status = $assetDetail->getRawOriginal('screen_time_passcode') ? '••••' : '—';
            $assetDetail->set_by_name = $assetDetail->setBy?->name ?: '—';
            $assetDetail->image_urls = array_map(fn ($file) => asset(Asset::UPLOAD_PATH.$file), $assetDetail->imageFiles());
            $assetDetail->name = ucfirst($assetDetail->name);
            $assetDetail->asset_code = $assetDetail->asset_code ?? '';
            $assetDetail->asset_serial_no = $assetDetail->asset_serial_no ?? '';
            $assetDetail->is_working = ucfirst($assetDetail->is_working);
            $assetDetail->assetType = $assetDetail->type?->name ?: __('index.not_available');
            $assetDetail->image = $assetDetail->image ? asset(Asset::UPLOAD_PATH.$assetDetail->image):'';
            $assetDetail->purchased_date = isset($assetDetail->purchased_date) ? AppHelper::formatDateForView($assetDetail->purchased_date):'';
            $assetDetail->warranty_available = $assetDetail->warranty_available ?? '';
            $assetDetail->is_available = $assetDetail->is_available == 1 ? __('index.yes') : __('index.no');
            $assetDetail->note = removeHtmlTags($assetDetail->note);
            $assetDetail->used_for = AppHelper::getAssetUsedDays($id);

            return response()->json([
                'data' => $assetDetail,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('edit_assets');
        try {
            $assetDetail = $this->assetService->findAssetById($id, ['*'], ['type:id,name', 'latestAssignment.user:id,name,avatar']);
            $branchId = old('branch_id', $assetDetail->branch_id ?: auth()->user()?->branch_id);
            $assetTypes = $this->assetFormTypes($branchId, $assetDetail);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $setByOptions = Asset::setupPeople($assetDetail);
            return view($this->view . 'edit', compact('assetDetail','companyDetail','assetTypes', 'setByOptions'));
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
            if ($request->input('asset_modal') === '1') {
                session()->flash('success', __('message.asset_update'));
                return view($this->view . 'common.modal-saved', ['message' => __('message.asset_update')]);
            }
            return redirect()->route('admin.assets.index')
                ->with('success', __('message.asset_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput($request->except(['asset_password', 'screen_time_passcode']));
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function changeAvailabilityStatus($id)
    {
        $this->authorize('edit_assets');
        try {
            DB::transaction(function () use ($id) {
                $asset = Asset::whereKey($id)->lockForUpdate()->firstOrFail();
                $this->assetService->updateAssetDetail($id, ['is_available' => !$asset->is_available]);
            });
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

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






    private function assetFormTypes($branchId, ?Asset $asset = null)
    {
        $types = $branchId ? $this->assetTypeService->getBranchAssetTypes($branchId, ['id', 'name']) : collect();
        if ($types->isEmpty()) {
            $types = $this->assetTypeService->getAllActiveAssetTypes(['id', 'name']);
        }
        // An existing asset may use a legacy type from before its branch changed.
        if ($asset && (string) $asset->branch_id === (string) $branchId && $asset->type_id && !$types->contains('id', $asset->type_id)) {
            $currentType = AssetType::withoutGlobalScope('branch')->find($asset->type_id, ['id', 'name']);
            if ($currentType) {
                $types->push($currentType);
            }
        }
        return $types->sortBy('name')->values();
    }

    public function getBranchAssetData(Request $request, $branchId)
    {
        try {

            $asset = $request->filled('asset_id') ? Asset::findOrFail($request->input('asset_id')) : null;
            $types = $this->assetFormTypes($branchId, $asset);

            return response()->json([
                'types' => $types,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }





}
