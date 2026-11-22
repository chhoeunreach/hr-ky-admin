<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetType;
use App\Models\Branch;
use App\Models\User;
use App\Services\AssetManagement\AssetCodeGenerator;
use App\Services\AssetManagement\AssetService;
use App\Traits\ImageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminAssetApiController extends Controller
{
    use ImageService;

    public function __construct(
        protected AssetService $assetService
    ) {
    }

    /**
     * List all assets with search and filters for Admin.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filterParameters = [
                'q' => $request->input('search') ?? $request->input('q'),
                'type_id' => $request->input('type_id'),
                'branch_id' => $request->input('branch_id'),
                'is_available' => $request->input('is_available'),
                'is_working' => $request->input('is_working'),
                'per_page' => $request->input('per_page') ?? 20,
            ];

            if (!auth('admin')->check() && auth()->user()?->branch_id && !AppHelper::canAccessAllBranches()) {
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['type:id,name', 'branch:id,name', 'latestAssignment.user:id,name,avatar'];
            $paginated = $this->assetService->getAllAssetsPaginated($filterParameters, $select, $with);

            $data = $paginated->getCollection()->map(function ($asset) {
                return $this->formatAssetData($asset);
            });

            return response()->json([
                'status' => true,
                'message' => __('index.data_found'),
                'status_code' => 200,
                'data' => $data,
                'pagination' => [
                    'total' => $paginated->total(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Show single asset details.
     */
    public function show($id): JsonResponse
    {
        try {
            $with = [
                'type:id,name',
                'branch:id,name',
                'setBy:id,name',
                'latestAssignment.user:id,name,avatar',
                'assignment.user:id,name,avatar',
            ];
            $asset = $this->assetService->findAssetById($id, ['*'], $with);
            if (!$asset) {
                return AppHelper::sendErrorResponse(__('message.asset_type_not_found'), 404);
            }

            $formatted = $this->formatAssetData($asset, true);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $formatted);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Form helper data: types, branches, active employees.
     */
    public function formData(Request $request): JsonResponse
    {
        try {
            $branchId = $request->input('branch_id');
            if (!auth('admin')->check() && auth()->user()?->branch_id && !AppHelper::canAccessAllBranches()) {
                $branchId = auth()->user()->branch_id;
            }

            $typesQuery = AssetType::query()->where('is_active', 1);
            if ($branchId) {
                $typesQuery->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }
            $types = $typesQuery->orderBy('name')->get(['id', 'name']);

            $branches = Branch::query()->orderBy('name')->get(['id', 'name']);

            $employeesQuery = User::query()
                ->where('is_active', 1)
                ->whereNull('deleted_at');
            if ($branchId) {
                $employeesQuery->where('branch_id', $branchId);
            }
            $employees = $employeesQuery->orderBy('name')->get(['id', 'name', 'avatar', 'employee_code', 'branch_id']);

            $formattedEmployees = $employees->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'employee_code' => $u->employee_code,
                    'branch_id' => $u->branch_id,
                    'avatar' => $u->avatar ? asset(User::AVATAR_UPLOAD_PATH . $u->avatar) : null,
                ];
            });

            return AppHelper::sendSuccessResponse(__('index.data_found'), [
                'types' => $types,
                'branches' => $branches,
                'employees' => $formattedEmployees,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created asset.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'type_id' => ['required', 'integer', 'exists:asset_types,id'],
                'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
                'asset_code' => ['nullable', 'string', 'max:100'],
                'asset_serial_no' => ['nullable', 'string', 'max:100'],
                'phone_numbers' => ['nullable', 'string', 'max:2000'],
                'icloud' => ['nullable', 'string', 'max:255'],
                'purchased_date' => ['required', 'date'],
                'is_available' => ['nullable', 'in:0,1,true,false'],
                'is_working' => ['nullable', 'in:yes,no'],
                'warranty_available' => ['nullable', 'in:0,1,true,false'],
                'warranty_end_date' => ['nullable', 'date'],
                'note' => ['nullable', 'string'],
                'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
                'assigned_date' => ['nullable', 'date'],
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            if (empty($validated['branch_id'])) {
                $validated['branch_id'] = auth()->user()?->branch_id ?? Branch::value('id');
            }

            $validated['is_working'] = $validated['is_working'] ?? 'yes';
            $validated['warranty_available'] = filter_var($validated['warranty_available'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $validated['is_available'] = filter_var($validated['is_available'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            if (!empty($validated['assigned_to'])) {
                $validated['is_available'] = 0;
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = Str::uuid() . '.' . $file->extension();
                File::ensureDirectoryExists(public_path(Asset::UPLOAD_PATH));
                $file->move(public_path(Asset::UPLOAD_PATH), $filename);
                $validated['image'] = $filename;
                $validated['images'] = [$filename];
            }

            $asset = DB::transaction(function () use ($validated) {
                if (empty($validated['asset_code'])) {
                    $validated['asset_code'] = app(AssetCodeGenerator::class)->next();
                }

                $assignedTo = $validated['assigned_to'] ?? null;
                $assignedDate = $validated['assigned_date'] ?? date('Y-m-d');
                unset($validated['assigned_to'], $validated['assigned_date']);

                $created = Asset::create($validated)->fresh(['type:id,name', 'branch:id,name']);

                if ($assignedTo) {
                    AssetAssignment::create([
                        'asset_id' => $created->id,
                        'user_id' => $assignedTo,
                        'assigned_date' => $assignedDate,
                        'branch_id' => $created->branch_id,
                        'status' => 'assigned',
                    ]);
                }

                return $created;
            });

            return AppHelper::sendSuccessResponse(__('message.asset_add'), $this->formatAssetData($asset->fresh(['type:id,name', 'branch:id,name', 'latestAssignment.user:id,name,avatar'])));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status' => false,
                'message' => $ve->validator->errors()->first(),
                'errors' => $ve->validator->errors(),
                'status_code' => 422,
            ], 422);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update an existing asset.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);

            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'type_id' => ['sometimes', 'required', 'integer', 'exists:asset_types,id'],
                'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
                'asset_code' => ['nullable', 'string', 'max:100'],
                'asset_serial_no' => ['nullable', 'string', 'max:100'],
                'phone_numbers' => ['nullable', 'string', 'max:2000'],
                'icloud' => ['nullable', 'string', 'max:255'],
                'purchased_date' => ['sometimes', 'required', 'date'],
                'is_available' => ['nullable'],
                'is_working' => ['nullable', 'in:yes,no'],
                'warranty_available' => ['nullable'],
                'warranty_end_date' => ['nullable', 'date'],
                'note' => ['nullable', 'string'],
                'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
                'assigned_date' => ['nullable', 'date'],
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            if (isset($validated['warranty_available'])) {
                $validated['warranty_available'] = filter_var($validated['warranty_available'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }
            if (isset($validated['is_available'])) {
                $validated['is_available'] = filter_var($validated['is_available'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = Str::uuid() . '.' . $file->extension();
                File::ensureDirectoryExists(public_path(Asset::UPLOAD_PATH));
                $file->move(public_path(Asset::UPLOAD_PATH), $filename);

                if ($asset->image && file_exists(public_path(Asset::UPLOAD_PATH . $asset->image))) {
                    @unlink(public_path(Asset::UPLOAD_PATH . $asset->image));
                }
                $validated['image'] = $filename;
                $validated['images'] = [$filename];
            }

            DB::transaction(function () use ($asset, $validated, $request) {
                $assignedTo = $validated['assigned_to'] ?? null;
                $assignedDate = $validated['assigned_date'] ?? date('Y-m-d');
                unset($validated['assigned_to'], $validated['assigned_date']);

                $asset->update($validated);

                if ($request->has('assigned_to')) {
                    $currentAssignment = $asset->assignment()->whereNull('returned_date')->latest('assigned_date')->first();

                    if ($assignedTo && (!$currentAssignment || (int) $currentAssignment->user_id !== (int) $assignedTo)) {
                        if ($currentAssignment) {
                            $currentAssignment->update([
                                'returned_date' => date('Y-m-d'),
                                'status' => 'returned',
                            ]);
                        }
                        AssetAssignment::create([
                            'asset_id' => $asset->id,
                            'user_id' => $assignedTo,
                            'assigned_date' => $assignedDate,
                            'branch_id' => $asset->branch_id,
                            'status' => 'assigned',
                        ]);
                        $asset->update(['is_available' => 0]);
                    } elseif (!$assignedTo && $currentAssignment) {
                        $currentAssignment->update([
                            'returned_date' => date('Y-m-d'),
                            'status' => 'returned',
                        ]);
                        $asset->update(['is_available' => 1]);
                    }
                }
            });

            $fresh = $asset->fresh(['type:id,name', 'branch:id,name', 'latestAssignment.user:id,name,avatar']);
            return AppHelper::sendSuccessResponse(__('message.asset_update'), $this->formatAssetData($fresh));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status' => false,
                'message' => $ve->validator->errors()->first(),
                'errors' => $ve->validator->errors(),
                'status_code' => 422,
            ], 422);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete an asset.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $asset = Asset::findOrFail($id);

            if ($asset->assignment()->whereNull('returned_date')->exists()) {
                return AppHelper::sendErrorResponse(__('index.asset_return_before_change'), 422);
            }

            DB::beginTransaction();
            $this->assetService->deleteAsset($id);
            DB::commit();

            return AppHelper::sendSuccessResponse(__('message.asset_delete'));
        } catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Format asset record for API response.
     */
    private function formatAssetData(Asset $asset, bool $fullDetail = false): array
    {
        $latest = $asset->latestAssignment;
        $activeAssignment = ($latest && is_null($latest->returned_date)) ? $latest : null;

        $imageUrl = null;
        if (!empty($asset->image)) {
            $imageUrl = asset(Asset::UPLOAD_PATH . $asset->image);
        } elseif (!empty($asset->images) && is_array($asset->images) && !empty($asset->images[0])) {
            $imageUrl = asset(Asset::UPLOAD_PATH . $asset->images[0]);
        }

        $allImages = [];
        if (!empty($asset->images) && is_array($asset->images)) {
            foreach ($asset->images as $img) {
                if ($img) $allImages[] = asset(Asset::UPLOAD_PATH . $img);
            }
        } elseif ($imageUrl) {
            $allImages[] = $imageUrl;
        }

        $status = 'Available';
        if ($asset->is_working === 'no') {
            $status = 'Maintenance';
        } elseif ($activeAssignment) {
            $status = 'Assigned';
        }

        $data = [
            'id' => $asset->id,
            'name' => $asset->name,
            'asset_code' => $asset->asset_code ?? '',
            'asset_serial_no' => $asset->asset_serial_no ?? '',
            'type_id' => $asset->type_id,
            'type_name' => $asset->type?->name ?? 'N/A',
            'branch_id' => $asset->branch_id,
            'branch_name' => $asset->branch?->name ?? 'N/A',
            'purchased_date' => $asset->purchased_date ? date('Y-m-d', strtotime($asset->purchased_date)) : '',
            'is_available' => (int) $asset->is_available,
            'is_working' => $asset->is_working ?? 'yes',
            'warranty_available' => (int) $asset->warranty_available,
            'warranty_end_date' => $asset->warranty_end_date ? date('Y-m-d', strtotime($asset->warranty_end_date)) : null,
            'note' => $asset->note ?? '',
            'phone_numbers' => $asset->phone_numbers ?? '',
            'icloud' => $asset->icloud ?? '',
            'image_url' => $imageUrl,
            'images' => $allImages,
            'status' => $status,
            'assigned_to' => $activeAssignment ? [
                'id' => $activeAssignment->user_id,
                'name' => $activeAssignment->user?->name ?? 'Unknown',
                'avatar' => $activeAssignment->user?->avatar ? asset(User::AVATAR_UPLOAD_PATH . $activeAssignment->user->avatar) : null,
                'assigned_date' => $activeAssignment->assigned_date ? date('Y-m-d', strtotime($activeAssignment->assigned_date)) : '',
            ] : null,
        ];

        if ($fullDetail) {
            $data['set_by'] = $asset->setBy ? [
                'id' => $asset->setBy->id,
                'name' => $asset->setBy->name,
            ] : null;

            $data['assignment_history'] = $asset->assignment->map(function ($a) {
                return [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'employee_name' => $a->user?->name ?? 'N/A',
                    'assigned_date' => $a->assigned_date ? date('Y-m-d', strtotime($a->assigned_date)) : '',
                    'returned_date' => $a->returned_date ? date('Y-m-d', strtotime($a->returned_date)) : null,
                    'status' => $a->status ?? ($a->returned_date ? 'returned' : 'assigned'),
                    'return_condition' => $a->return_condition ?? null,
                    'notes' => $a->notes ?? null,
                ];
            });
        }

        return $data;
    }
}
