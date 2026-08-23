<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SocialReward;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialRewardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SocialReward::query()
                ->with('employee')
                ->latest('log_date')
                ->latest('id');

            if ($request->filled('employee_id')) {
                $query->where('existing_employee_id', $request->integer('employee_id'));
            }

            $records = $query->get()->map(fn (SocialReward $reward) => $reward->toApiArray())->values();

            return AppHelper::sendSuccessResponse('Social rewards loaded successfully.', $records);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function today(Request $request): JsonResponse
    {
        try {
            $employeeId = $request->integer('employee_id') ?: auth()->id();
            $record = SocialReward::query()
                ->with('employee')
                ->where('existing_employee_id', $employeeId)
                ->whereDate('log_date', Carbon::today())
                ->first();

            return AppHelper::sendSuccessResponse('Today social reward status loaded successfully.', [
                'record' => $record?->toApiArray(),
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $record = $this->createRecord($request, true);

            return AppHelper::sendSuccessResponse('Social reward created successfully.', $record->load('employee')->toApiArray());
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function submit(Request $request): JsonResponse
    {
        try {
            $employeeId = (int) ($request->input('employee_id') ?? $request->input('existing_employee_id') ?? auth()->id());
            $todayRecord = SocialReward::query()
                ->where('existing_employee_id', $employeeId)
                ->whereDate('log_date', Carbon::today())
                ->first();

            if ($todayRecord) {
                return $this->updateRecord($request, $todayRecord);
            }

            $record = $this->createRecord($request, false);

            return AppHelper::sendSuccessResponse('Social reward submitted successfully.', $record->load('employee')->toApiArray());
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $record = SocialReward::query()->findOrFail($id);
            return $this->updateRecord($request, $record);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function override(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'target_record_id' => ['required', 'integer', 'exists:hrs_addon_social_rewards,id'],
                'fb_post_url' => ['required', 'string'],
                'fb_story_url' => ['required', 'string'],
                'tiktok_url' => ['required', 'string'],
                'reason' => ['required', 'string'],
            ]);

            $record = SocialReward::query()->findOrFail($validated['target_record_id']);
            $adminId = $this->resolveAuditAdminId($request);

            DB::transaction(function () use ($record, $validated, $adminId) {
                $record->update([
                    'fb_post_url' => $validated['fb_post_url'],
                    'fb_story_url' => $validated['fb_story_url'],
                    'tiktok_url' => $validated['tiktok_url'],
                    'is_locked' => false,
                ]);

                if ($adminId !== null) {
                    DB::table('hrs_addon_reward_audit')->insert([
                        'target_record_id' => $record->id,
                        'admin_id' => $adminId,
                        'reason' => $validated['reason'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            return AppHelper::sendSuccessResponse('Social reward updated successfully.', $record->fresh('employee')->toApiArray());
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    private function updateRecord(Request $request, SocialReward $record): JsonResponse
    {
        $validated = $this->validatePayload($request, true);
        $payload = [
            'fb_post_url' => $validated['fb_post_url'],
            'fb_story_url' => $validated['fb_story_url'],
            'tiktok_url' => $validated['tiktok_url'],
        ];

        foreach (['fb_post_photo', 'fb_story_photo', 'tiktok_photo'] as $field) {
            if ($request->hasFile($field)) {
                $payload[$field] = $this->storePhoto($request, $field);
            }
        }

        $record->update($payload);

        return AppHelper::sendSuccessResponse('Social reward updated successfully.', $record->fresh('employee')->toApiArray());
    }

    private function createRecord(Request $request, bool $photosOptional): SocialReward
    {
        $validated = $this->validatePayload($request, $photosOptional);
        $employeeId = (int) ($validated['employee_id'] ?? $validated['existing_employee_id'] ?? auth()->id());
        $logDate = $validated['log_date'] ?? Carbon::today()->format('Y-m-d');

        return SocialReward::query()->create([
            'existing_employee_id' => $employeeId,
            'log_date' => $logDate,
            'fb_post_url' => $validated['fb_post_url'],
            'fb_story_url' => $validated['fb_story_url'],
            'tiktok_url' => $validated['tiktok_url'],
            'fb_post_photo' => $this->storePhoto($request, 'fb_post_photo'),
            'fb_story_photo' => $this->storePhoto($request, 'fb_story_photo'),
            'tiktok_photo' => $this->storePhoto($request, 'tiktok_photo'),
            'reward_points' => (int) ($validated['reward_points'] ?? 1),
            'is_locked' => true,
        ]);
    }

    private function validatePayload(Request $request, bool $photosOptional = false): array
    {
        $photoRule = $photosOptional ? 'nullable' : 'required';

        return $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'existing_employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'log_date' => ['nullable', 'date'],
            'fb_post_url' => ['required', 'string'],
            'fb_story_url' => ['required', 'string'],
            'tiktok_url' => ['required', 'string'],
            'fb_post_photo' => [$photoRule, 'file', 'image', 'max:5120'],
            'fb_story_photo' => [$photoRule, 'file', 'image', 'max:5120'],
            'tiktok_photo' => [$photoRule, 'file', 'image', 'max:5120'],
            'reward_points' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function storePhoto(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('social-rewards', 'public');
    }

    private function resolveAuditAdminId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        $admin = Admin::query()
            ->where('id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('username', $user->username)
            ->first();

        return $admin?->id;
    }
}
