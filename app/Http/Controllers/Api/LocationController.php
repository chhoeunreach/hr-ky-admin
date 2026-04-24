<?php

namespace App\Http\Controllers\Api;

use App\Events\LocationUpdated;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\EmployeeLocation;
use App\Models\UserLocation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'battery_level' => ['nullable', 'integer', 'between:0,100'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('index.validation_failed'),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();
        $authUser = $request->user();

        if ((int) $validated['user_id'] !== (int) $authUser->id) {
            return AppHelper::sendErrorResponse('You can only update your own location.', 403);
        }

        try {
            DB::beginTransaction();

            $location = null;

            if (Schema::hasTable('user_locations')) {
                $location = UserLocation::updateOrCreate(
                    ['user_id' => $authUser->id],
                    [
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                        'accuracy' => $validated['accuracy'],
                        'battery_level' => $validated['battery_level'] ?? null,
                        'device_name' => $validated['device_name'] ?? null,
                    ]
                )->fresh(['user:id,name,email,phone,avatar,branch_id,department_id']);
            }

            EmployeeLocation::create([
                'employee_id' => $authUser->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            if ($location) {
                event(new LocationUpdated($location));
            }

            DB::commit();

            return AppHelper::sendSuccessResponse('Location updated successfully.', [
                'location' => [
                    'user_id' => $authUser->id,
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'accuracy' => $validated['accuracy'],
                    'battery_level' => $validated['battery_level'] ?? null,
                    'device_name' => $validated['device_name'] ?? null,
                    'updated_at' => now(),
                ],
            ]);
        } catch (Exception $exception) {
            DB::rollBack();

            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }
}
