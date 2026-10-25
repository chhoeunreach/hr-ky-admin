<?php

namespace App\Http\Controllers\Web;

use App\Enum\AssetAssignmentStatusEnum;
use App\Enum\AssetReturnConditionEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\AssetManagement\AssetDetailRequest;
use App\Services\AssetManagement\AssetAssignmentService;
use App\Services\AssetManagement\AssetService;
use App\Services\AssetManagement\AssetTypeService;
use App\Services\Notification\NotificationService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetAssignmentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.assetManagement.assetDetail.';

    public function __construct(
        protected AssetService     $assetService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository,
        protected AssetAssignmentService $assignmentService,
        protected NotificationService $notificationService,
        protected DepartmentRepository $departmentRepository,
    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index($assetId)
    {
        $this->authorize('asset_assign_list');
        try {

            $assetDetail = $this->assetService->findAssetById($assetId,['id','name']);

            $with = ['asset:id,name','user:id,name'];
            $select = ['*'];
            $assignmentList = $this->assignmentService->getAssignmentsPaginated($assetId, $select, $with);
            $activities = \App\Models\AssetActivity::where('asset_id', $assetDetail->id)->orderByDesc('id')->paginate(25)->withQueryString();
            return view($this->view . 'assignment_list', compact('assignmentList','assetDetail', 'activities'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }




    public function create($branchId)
    {
        try {
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId,[], ['id', 'dept_name']);
            return response()->json([
                'data' => $departments,
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    private function assignmentBranches(Asset $asset)
    {
        return $asset->branch
            ? collect([$asset->branch])
            : Branch::orderBy('name')->get(['id', 'name']);
    }

    public function options(Request $request, $assetId)
    {
        $this->authorize('assign_asset');
        $asset = Asset::with('branch:id,name')->findOrFail($assetId);
        $branches = $this->assignmentBranches($asset);
        $request->validate(['branch_id' => ['nullable', 'integer', Rule::in($branches->pluck('id')->all())]], [], ['branch_id' => __('index.branch')]);
        $branchId = $request->input('branch_id') ?: ($asset->branch?->id
            ?? ($branches->contains('id', auth()->user()?->branch_id) ? auth()->user()?->branch_id : null)
            ?? ($branches->count() === 1 ? $branches->first()->id : null));
        $filterBranchId = $request->input('branch_id') ?: $asset->branch?->id;
        $branchIds = $filterBranchId ? [$filterBranchId] : $branches->pluck('id')->all();
        $departments = Department::whereIn('branch_id', $branchIds)->where('is_active', 1)
            ->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']);
        $employees = User::whereIn('branch_id', $branchIds)->whereIn('department_id', $departments->pluck('id'))
            ->where('is_active', 1)->where('status', 'verified')->orderBy('name')
            ->get(['id', 'name', 'english_name', 'employee_code', 'username', 'department_id', 'branch_id']);

        return response()->json([
            'asset' => $asset->only(['id', 'name', 'asset_code', 'asset_serial_no', 'branch_id']) + ['branch_name' => $asset->branch?->name],
            'branches' => $branches->values(), 'selected_branch_id' => $branchId,
            'branch_locked' => (bool) $asset->branch,
            'current_assignment' => $asset->assignment()->whereNull('returned_date')->first(['id', 'user_id', 'assigned_date']),
            'departments' => $departments, 'employees' => $employees,
        ]);
    }

    public function receipt($assetId)
    {
        $this->authorize('print_assets');
        $asset = Asset::with(['branch', 'type', 'latestAssignment.user', 'latestAssignment.branch', 'latestAssignment.department'])->findOrFail($assetId);
        $assignment = $asset->latestAssignment;
        abort_unless($assignment && !$assignment->returned_date, 404);

        return view('admin.assetManagement.assetDetail.receipt', compact('asset', 'assignment'));
    }

    public function updateEmployee(Request $request, $id)
    {
        return $this->store($request, $id, true);
    }

    public function store(Request $request, $id, bool $transfer = false)
    {
        $this->authorize('assign_asset');
        $assetDetail = Asset::with('branch:id,name')->findOrFail($id);
        $branches = $this->assignmentBranches($assetDetail);
        $branchId = $assetDetail->branch?->id ?? $request->input('branch_id');
        try {
            $validatedData = $request->validate([
                'asset_id' => ['required', 'integer', Rule::in([$assetDetail->id])],
                'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('branch_id', $branchId)->where('department_id', $request->input('department_id'))
                    ->where('is_active', 1)->where('status', 'verified')->whereNull('deleted_at'))],
                'branch_id' => ['required', 'integer', Rule::in($branches->pluck('id')->all())],
                'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where(fn ($query) => $query
                    ->where('branch_id', $branchId)->where('is_active', 1))],
                'assigned_date' => 'required|date|before_or_equal:today',
            ], [], [
                'asset_id' => __('index.asset_name'), 'user_id' => __('index.employee'),
                'branch_id' => __('index.branch'), 'department_id' => __('index.department'),
                'assigned_date' => __('index.assigned_date'),
            ]);


            $validatedData['branch_id'] = $branchId;
            DB::beginTransaction();
            $assetDetail = Asset::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($assetDetail->branch_id && (int) $assetDetail->branch_id !== (int) $branchId) {
                throw ValidationException::withMessages(['branch_id' => __('index.asset_return_before_change')]);
            }
            $current = $assetDetail->assignment()->whereNull('returned_date')->lockForUpdate()->first();
            if ($assetDetail->is_working !== 'yes' || (!$transfer && (!$assetDetail->is_available || $current))) {
                throw ValidationException::withMessages(['asset_id' => __('index.asset_assign_condition_help')]);
            }
            if ($transfer) {
                if (!$current || (int) $current->user_id === (int) $validatedData['user_id']) {
                    throw ValidationException::withMessages(['user_id' => __('index.asset_select_new_employee')]);
                }
                if ($validatedData['assigned_date'] < $current->assigned_date) {
                    throw ValidationException::withMessages(['assigned_date' => __('index.asset_transfer_date_invalid')]);
                }
                $current->update([
                    'returned_date' => $validatedData['assigned_date'],
                    'status' => AssetAssignmentStatusEnum::returned->value,
                    'return_condition' => AssetReturnConditionEnum::working->value,
                    'notes' => __('index.asset_employee_changed'),
                ]);
            }
            if (!$assetDetail->branch_id) $assetDetail->update(['branch_id' => $branchId]);
            $validatedData['status'] = AssetAssignmentStatusEnum::assigned->value;
             $assetAssignmentDetail = $this->assignmentService->saveDetail($validatedData);

             if($assetAssignmentDetail){
                 $this->assetService->updateAssetDetail($id,[
                     'is_available'=>0,
                     ...(!$assetDetail->branch ? ['branch_id' => $branchId] : []),
                 ]);
             }
            DB::commit();

            $notificationTitle = __('index.asset_assign_notification');
            $message =  __('message.asset_notification_message', [
                'asset' => $assetDetail->name
            ]);

            try {
                SMPushHelper::sendLeaveNotification($notificationTitle, $message, $validatedData['user_id']);
            } catch (\Throwable $notificationError) {
                \Illuminate\Support\Facades\Log::warning('Asset assignment saved; push notification failed.', [
                    'asset_id' => $assetDetail->id, 'error' => $notificationError->getMessage(),
                ]);
            }

            return response()->json([
                'message' => __('message.asset_assignment_saved'),
            ], 200);
        } catch (ValidationException $exception) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            return response()->json(['message' => $exception->getMessage(), 'errors' => $exception->errors()], 422);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * @throws AuthorizationException
     */
//    public function update(AssetDetailRequest $request, $id)
//    {
//        $this->authorize('edit_assets');
//        try {
//            $validatedData = $request->validated();
//            DB::beginTransaction();
//            $this->assetService->updateAssetDetail($id, $validatedData);
//            DB::commit();
//            return redirect()->route('admin.assets.index')
//                ->with('success', __('message.asset_update'));
//        } catch (Exception $exception) {
//            DB::rollBack();
//            return redirect()->back()->with('danger', $exception->getMessage())
//                ->withInput();
//        }
//    }

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


    public function returnlist()
    {
        $this->authorize('asset_assign_list');
        try {
            $select = ['*'];
            $with = ['asset.type:id,name','user:id,name'];
            $returnLists = $this->assignmentService->getReturnAssetsPaginated($select,$with);
            $maintenanceLists = $this->assignmentService->getMaintenanceAssetsPaginated($select,$with);

            return view($this->view . 'return_list', compact('returnLists','maintenanceLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function storeReturn(Request $request, $id)
    {
        $this->authorize('assign_asset');
        try {
            $validatedData = $request->validate([
                'is_working' => 'required|in:yes,no',
                'notes' => [
                    'nullable',
                    'string', 'max:5000'
                ],
            ]);
            $validatedData['return_condition'] = $validatedData['is_working'] == 'yes' ? AssetReturnConditionEnum::working->value :  AssetReturnConditionEnum::requireMaintenance->value ;
            $validatedData['returned_date'] = date('Y-m-d');
            $validatedData['status'] = AssetAssignmentStatusEnum::returned->value;

            DB::beginTransaction();
            $assetAssignmentDetail = $this->assignmentService->updateDetail($id, $validatedData);

            $assetUpdate = [
                'is_available' =>  $validatedData['is_working'] == 'yes' ? 1 : 0,
                'is_working' =>  $validatedData['is_working']
            ];
            $this->assetService->updateAssetDetail($assetAssignmentDetail->asset_id,$assetUpdate);
            DB::commit();


            if ($request->input('asset_modal') === '1') {
                session()->flash('success', __('message.asset_assignment_return'));
                return view($this->view . 'common.modal-saved', ['message' => __('message.asset_assignment_return')]);
            }
            return redirect()->route('admin.assets.index')->with('success', __('message.asset_assignment_return'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



    /**
     * @throws AuthorizationException
     */
    public function changeRepairStatus($id)
    {
        $this->authorize('edit_assets');
        try {
            DB::beginTransaction();
            $this->assignmentService->toggleRepairStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
