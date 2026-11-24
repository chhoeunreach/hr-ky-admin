<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetType;
use App\Models\Branch;
use App\Models\User;
use App\Repositories\AssetRepository;
use App\Services\AssetManagement\AssetCodeGenerator;
use App\Services\AssetManagement\AssetService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminAssetApiController extends Controller
{
    public function __construct(
        protected AssetService $assetService,
        protected AssetRepository $assetRepo,
    ) {
    }

    /**
     * Get paginated list of assets for admin mobile app
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filterParameters = [
                'q' => $request->input('search') ?: $request->input('q'),
                'type_id' => $request->input('type_id'),
                'branch_id' => $request->input('branch_id'),
                'is_available' => $request->input('is_available'),
                'is_working' => $request->input('is_working'),
                'per_page' => $request->input('per_page', 20),
            ];

            if ($request->filled('page')) {
                $filterParameters['page'] = (int) $request->input('page');
            }

            $select = ['*'];
            $with = ['type:id,name', 'branch:id,name', 'latestAssignment.user:id,name,avatar', 'setBy:id,name'];

            $paginated = $this->assetRepo->getAllAssetsPaginated($filterParameters, $select, $with);

            $assets = collect($paginated->items())->map(fn ($asset) => $this->formatAsset($asset));

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Assets retrieved successfully',
                'data' => $assets,
                'pagination' => [
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get dropdown data for asset creation and edit form
     */
    public function formData(Request $request): JsonResponse
    {
        try {
            $types = AssetType::where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name']);

            $branches = Branch::orderBy('name')->get(['id', 'name']);

            $branchId = $request->input('branch_id');
            $employees = User::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_code', 'branch_id', 'avatar'])
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'employee_code' => $u->employee_code,
                        'branch_id' => $u->branch_id,
                        'avatar' => $u->avatar ? asset(User::AVATAR_UPLOAD_PATH . $u->avatar) : null,
                    ];
                });

            $nextAssetCode = '';
            try {
                $nextAssetCode = app(AssetCodeGenerator::class)->preview();
            } catch (\Throwable $e) {
                $nextAssetCode = 'AST-' . str_pad((string) (Asset::max('id') + 1), 5, '0', STR_PAD_LEFT);
            }

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Form data retrieved successfully',
                'data' => [
                    'types' => $types,
                    'branches' => $branches,
                    'employees' => $employees,
                    'next_asset_code' => $nextAssetCode,
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get single asset details with assignment history
     */
    public function show($id): JsonResponse
    {
        try {
            $with = [
                'type:id,name',
                'branch:id,name',
                'latestAssignment.user:id,name,avatar',
                'assignment.user:id,name',
            ];
            $asset = Asset::with($with)->findOrFail($id);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Asset detail retrieved successfully',
                'data' => $this->formatAsset($asset, includeHistory: true),
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Create a new asset via mobile admin form
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type_id' => 'required|exists:asset_types,id',
                'branch_id' => 'required|exists:branches,id',
                'asset_serial_no' => 'nullable|string|max:255',
                'phone_numbers' => 'nullable|string',
                'icloud' => 'nullable|string',
                'purchased_date' => 'nullable|date',
                'warranty_available' => 'nullable',
                'warranty_end_date' => 'nullable|date',
                'is_available' => 'nullable',
                'is_working' => 'nullable|in:yes,no',
                'note' => 'nullable|string',
                'assigned_to' => 'nullable|exists:users,id',
                'assigned_date' => 'nullable|date',
                'image' => 'nullable|file|max:10240',
                'images' => 'nullable|array',
                'images.*' => 'file|max:10240',
            ]);

            $assetData = $request->all();
            if ($request->hasFile('image')) {
                $assetData['image'] = $request->file('image');
            }
            if ($request->hasFile('images')) {
                $assetData['images'] = $request->file('images');
            }

            $assetData['is_available'] = $request->filled('assigned_to') ? 0 : ($request->input('is_available', 1) ? 1 : 0);
            $assetData['is_working'] = $request->input('is_working', 'yes');
            $assetData['warranty_available'] = $request->input('warranty_available', 0) ? 1 : 0;

            DB::beginTransaction();
            $asset = $this->assetRepo->store($assetData);

            if ($request->filled('assigned_to')) {
                $userId = (int) $request->input('assigned_to');
                $assignedUser = User::find($userId);
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'user_id' => $userId,
                    'branch_id' => $asset->branch_id,
                    'department_id' => $assignedUser?->department_id,
                    'assigned_date' => $request->input('assigned_date') ?: date('Y-m-d'),
                    'status' => 'assigned',
                    'assigned_by' => auth()->id() ?? auth('api')->id(),
                ]);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Asset added successfully',
                'data' => $this->formatAsset($asset->fresh(['type', 'branch', 'latestAssignment.user'])),
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update existing asset via mobile admin form
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type_id' => 'sometimes|required|exists:asset_types,id',
                'branch_id' => 'sometimes|required|exists:branches,id',
                'asset_serial_no' => 'nullable|string|max:255',
                'phone_numbers' => 'nullable|string',
                'icloud' => 'nullable|string',
                'purchased_date' => 'nullable|date',
                'warranty_available' => 'nullable',
                'warranty_end_date' => 'nullable|date',
                'is_available' => 'nullable',
                'is_working' => 'nullable|in:yes,no',
                'note' => 'nullable|string',
                'assigned_to' => 'nullable',
                'assigned_date' => 'nullable|date',
                'image' => 'nullable|file|max:10240',
                'images' => 'nullable|array',
                'images.*' => 'file|max:10240',
            ]);

            $assetData = $request->all();
            if ($request->hasFile('image')) {
                $assetData['image'] = $request->file('image');
            }
            if ($request->hasFile('images')) {
                $assetData['images'] = $request->file('images');
            }

            if ($request->has('warranty_available')) {
                $assetData['warranty_available'] = $request->input('warranty_available') ? 1 : 0;
            }

            DB::beginTransaction();

            $this->assetRepo->update($asset, $assetData);

            if ($request->has('assigned_to')) {
                $newAssignee = $request->input('assigned_to');
                $active = $asset->assignment()->whereNull('returned_date')->first();

                if (!empty($newAssignee)) {
                    $newUserId = (int) $newAssignee;
                    if (!$active || (int) $active->user_id !== $newUserId) {
                        if ($active) {
                            $active->update([
                                'returned_date' => $request->input('assigned_date') ?: date('Y-m-d'),
                                'status' => 'returned',
                                'return_condition' => 'working',
                                'notes' => 'Transferred from mobile admin',
                            ]);
                        }
                        $assignedUser = User::find($newUserId);
                        AssetAssignment::create([
                            'asset_id' => $asset->id,
                            'user_id' => $newUserId,
                            'branch_id' => $asset->branch_id,
                            'department_id' => $assignedUser?->department_id,
                            'assigned_date' => $request->input('assigned_date') ?: date('Y-m-d'),
                            'status' => 'assigned',
                            'assigned_by' => auth()->id() ?? auth('api')->id(),
                        ]);
                        $asset->update(['is_available' => 0]);
                    }
                } elseif ($active) {
                    $active->update([
                        'returned_date' => date('Y-m-d'),
                        'status' => 'returned',
                        'return_condition' => 'working',
                        'notes' => 'Unassigned from mobile admin',
                    ]);
                    $asset->update(['is_available' => 1]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Asset updated successfully',
                'data' => $this->formatAsset($asset->fresh(['type', 'branch', 'latestAssignment.user'])),
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete an asset
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->assetService->deleteAsset($id);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Asset deleted successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => collect($e->errors())->flatten()->first() ?? 'Cannot delete asset',
            ], 422);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Helper to format an Asset model matching Flutter's AdminAsset.fromJson
     */
    private function formatAsset(Asset $asset, bool $includeHistory = false): array
    {
        $latest = $asset->latestAssignment;
        $currentAssignee = ($latest && !$latest->returned_date && $latest->user) ? [
            'id' => $latest->user->id,
            'name' => $latest->user->name,
            'avatar' => $latest->user->avatar ? asset(User::AVATAR_UPLOAD_PATH . $latest->user->avatar) : null,
            'assigned_date' => $latest->assigned_date ? Carbon::parse($latest->assigned_date)->format('Y-m-d') : '',
        ] : null;

        $history = [];
        if ($includeHistory && $asset->relationLoaded('assignment')) {
            $history = $asset->assignment->map(function ($h) {
                return [
                    'id' => $h->id,
                    'user_id' => $h->user_id,
                    'employee_name' => $h->user?->name ?? 'N/A',
                    'assigned_date' => $h->assigned_date ? Carbon::parse($h->assigned_date)->format('Y-m-d') : '',
                    'returned_date' => $h->returned_date ? Carbon::parse($h->returned_date)->format('Y-m-d') : null,
                    'status' => $h->status ?? '',
                    'return_condition' => $h->return_condition,
                    'notes' => $h->notes,
                ];
            })->values()->all();
        }

        $imageFiles = $asset->imageFiles();
        $images = array_map(fn ($img) => asset(Asset::UPLOAD_PATH . $img), $imageFiles);
        $imageUrl = !empty($images) ? $images[0] : null;

        $status = 'Available';
        if ($asset->is_working === 'no') {
            $status = 'Under Repair';
        } elseif ($currentAssignee) {
            $status = 'Assigned';
        } elseif (!$asset->is_available) {
            $status = 'Unavailable';
        }

        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'asset_code' => $asset->asset_code ?? '',
            'asset_serial_no' => $asset->asset_serial_no ?? '',
            'type_id' => $asset->type_id,
            'type_name' => $asset->type?->name ?? 'N/A',
            'branch_id' => $asset->branch_id,
            'branch_name' => $asset->branch?->name ?? 'N/A',
            'purchased_date' => $asset->purchased_date ? Carbon::parse($asset->purchased_date)->format('Y-m-d') : '',
            'is_available' => (int) $asset->is_available,
            'is_working' => $asset->is_working ?? 'yes',
            'warranty_available' => (int) ($asset->warranty_available ?? 0),
            'warranty_end_date' => $asset->warranty_end_date ? Carbon::parse($asset->warranty_end_date)->format('Y-m-d') : null,
            'note' => $asset->note ?? '',
            'phone_numbers' => $asset->phone_numbers ?? '',
            'icloud' => $asset->icloud ?? '',
            'image_url' => $imageUrl,
            'images' => $images,
            'status' => $status,
            'assigned_to' => $currentAssignee,
            'assignment_history' => $history,
        ];
    }
}
