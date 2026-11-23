<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\StorePhoneNumber;
use App\Repositories\StorePhoneNumberRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorePhoneNumberApiController extends Controller
{
    public function __construct(
        protected StorePhoneNumberRepository $phoneRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filterParameters = [
                'search' => $request->search ?? null,
                'branch_id' => $request->branch_id ?? null,
                'purpose' => $request->purpose ?? null,
                'operator' => $request->operator ?? null,
            ];

            $phoneNumbers = $this->phoneRepo->getActiveStorePhoneNumbers($filterParameters);

            $data = $phoneNumbers->map(function ($item) {
                return [
                    'id' => $item->id,
                    'phone_number' => $item->phone_number,
                    'operator' => $item->operator,
                    'operator_name' => StorePhoneNumber::OPERATORS[$item->operator] ?? ucfirst($item->operator),
                    'sim_slot' => $item->sim_slot,
                    'sim_slot_name' => StorePhoneNumber::SIM_SLOTS[$item->sim_slot] ?? strtoupper($item->sim_slot),
                    'purpose' => $item->purpose,
                    'purpose_name' => StorePhoneNumber::PURPOSES[$item->purpose] ?? ucfirst($item->purpose),
                    'plan_type' => $item->plan_type,
                    'serial_number' => $item->serial_number,
                    'status' => $item->status,
                    'branch' => $item->branch ? [
                        'id' => $item->branch->id,
                        'name' => $item->branch->name,
                    ] : null,
                    'device' => $item->asset ? [
                        'id' => $item->asset->id,
                        'name' => $item->asset->name,
                        'asset_code' => $item->asset->asset_code,
                    ] : null,
                    'assigned_employee' => $item->employee ? [
                        'id' => $item->employee->id,
                        'name' => $item->employee->name,
                        'email' => $item->employee->email,
                    ] : null,
                    'assigned_date' => $item->assigned_date?->format('Y-m-d'),
                    'notes' => $item->notes,
                ];
            });

            return AppHelper::sendSuccessResponse(__('index.data_found') ?? 'Store phone directory fetched successfully', $data);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    public function mySimCards(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $mySims = StorePhoneNumber::query()
                ->where('assigned_to', $user->id)
                ->where('status', 'active')
                ->with(['asset:id,name,asset_code', 'branch:id,name'])
                ->get();

            $data = $mySims->map(function ($item) {
                return [
                    'id' => $item->id,
                    'phone_number' => $item->phone_number,
                    'operator' => $item->operator,
                    'operator_name' => StorePhoneNumber::OPERATORS[$item->operator] ?? ucfirst($item->operator),
                    'sim_slot' => $item->sim_slot,
                    'sim_slot_name' => StorePhoneNumber::SIM_SLOTS[$item->sim_slot] ?? strtoupper($item->sim_slot),
                    'purpose' => $item->purpose,
                    'purpose_name' => StorePhoneNumber::PURPOSES[$item->purpose] ?? ucfirst($item->purpose),
                    'plan_type' => $item->plan_type,
                    'branch' => $item->branch?->name,
                    'device' => $item->asset ? [
                        'id' => $item->asset->id,
                        'name' => $item->asset->name,
                        'asset_code' => $item->asset->asset_code,
                    ] : null,
                    'assigned_date' => $item->assigned_date?->format('Y-m-d'),
                    'notes' => $item->notes,
                ];
            });

            return AppHelper::sendSuccessResponse(__('index.data_found') ?? 'My assigned phone numbers fetched successfully', $data);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }
}
