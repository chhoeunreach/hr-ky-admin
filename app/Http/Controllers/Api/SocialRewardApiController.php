<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SocialReward;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class SocialRewardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $employeeId = $request->filled('employee_id') ? $request->integer('employee_id') : null;

            if (!$this->canManage($user)) {
                $employeeId = $user?->id;
            }

            $query = SocialReward::query()
                ->with('employee')
                ->when($employeeId, fn ($query) => $query->where('existing_employee_id', $employeeId))
                ->when($user?->company_id, function ($query, $companyId) {
                    $query->whereHas('employee', fn ($employee) => $employee->where('company_id', $companyId));
                })
                ->latest('log_date')
                ->latest('id');

            $records = $query->get()->map(fn (SocialReward $reward) => $reward->toApiArray())->values();

            return AppHelper::sendSuccessResponse('Social rewards loaded successfully.', $records);
        } catch (HttpExceptionInterface|ValidationException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function today(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $employeeId = $request->integer('employee_id') ?: $user?->id;
            if (!$this->canManage($user)) {
                $employeeId = $user?->id;
            }

            $record = SocialReward::query()
                ->with('employee')
                ->where('existing_employee_id', $employeeId)
                ->when($user?->company_id, function ($query, $companyId) {
                    $query->whereHas('employee', fn ($employee) => $employee->where('company_id', $companyId));
                })
                ->whereDate('log_date', Carbon::today())
                ->first();

            return AppHelper::sendSuccessResponse('Today social reward status loaded successfully.', [
                'record' => $record?->toApiArray(),
            ]);
        } catch (HttpExceptionInterface|ValidationException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $record = $this->createRecord($request, true);

            return AppHelper::sendSuccessResponse('Social reward created successfully.', $record->load('employee')->toApiArray());
        } catch (HttpExceptionInterface|ValidationException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function submit(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $employeeId = (int) ($request->input('employee_id') ?? $request->input('existing_employee_id') ?? $user?->id);
            if (!$this->canManage($user) && $employeeId !== (int) $user?->id) {
                abort(403, 'You can only submit your own Social Media Marketing links.');
            }
            $this->ensureEmployeeAccess($user, $employeeId);

            $todayRecord = SocialReward::query()
                ->where('existing_employee_id', $employeeId)
                ->whereDate('log_date', Carbon::today())
                ->first();

            if ($todayRecord) {
                return $this->updateRecord($request, $todayRecord);
            }

            $record = $this->createRecord($request, false);

            return AppHelper::sendSuccessResponse('Social reward submitted successfully.', $record->load('employee')->toApiArray());
        } catch (HttpExceptionInterface|ValidationException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $record = SocialReward::query()->with('employee')->findOrFail($id);
            $this->ensureRecordAccess($request->user(), $record);

            return $this->updateRecord($request, $record);
        } catch (HttpExceptionInterface|ValidationException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function override(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'target_record_id' => ['required', 'integer', 'exists:hrs_addon_social_rewards,id'],
                'fb_post_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
                'fb_story_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
                'tiktok_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
                'reason' => ['required', 'string', 'max:2000'],
            ]);

            $record = SocialReward::query()->with('employee')->findOrFail($validated['target_record_id']);
            $this->ensureRecordAccess($request->user(), $record);
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
        } catch (HttpExceptionInterface|ValidationException $exception) {
            throw $exception;
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
        $user = $request->user();
        $employeeId = (int) ($validated['employee_id'] ?? $validated['existing_employee_id'] ?? $user?->id);

        if (!$this->canManage($user) && $employeeId !== (int) $user?->id) {
            abort(403, 'You can only submit your own Social Media Marketing links.');
        }
        $this->ensureEmployeeAccess($user, $employeeId);

        $logDate = $validated['log_date'] ?? Carbon::today()->format('Y-m-d');
        $exists = SocialReward::query()
            ->where('existing_employee_id', $employeeId)
            ->whereDate('log_date', $logDate)
            ->exists();
        if ($exists) {
            abort(422, 'This employee already has a Social Media Marketing record for that date.');
        }

        return SocialReward::query()->create([
            'existing_employee_id' => $employeeId,
            'log_date' => $logDate,
            'fb_post_url' => $validated['fb_post_url'],
            'fb_story_url' => $validated['fb_story_url'],
            'tiktok_url' => $validated['tiktok_url'],
            'fb_post_photo' => $this->storePhoto($request, 'fb_post_photo'),
            'fb_story_photo' => $this->storePhoto($request, 'fb_story_photo'),
            'tiktok_photo' => $this->storePhoto($request, 'tiktok_photo'),
            'reward_points' => 1,
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
            'fb_post_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
            'fb_story_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
            'tiktok_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
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

    private function canManage(?object $user): bool
    {
        $menu = config('mobile_permissions.menus.social-rewards-admin');

        return $user !== null && $menu && Gate::forUser($user)->allows($menu);
    }

    private function ensureEmployeeAccess(?object $user, int $employeeId): void
    {
        if ($user === null) {
            abort(401);
        }

        if (!$this->canManage($user) && $employeeId !== (int) $user->id) {
            abort(403, 'You can only access your own Social Media Marketing submissions.');
        }

        if ($user->company_id) {
            $matches = User::query()
                ->whereKey($employeeId)
                ->where('company_id', $user->company_id)
                ->exists();
            abort_unless($matches, 404);
        }
    }

    private function ensureRecordAccess(?object $user, SocialReward $record): void
    {
        $this->ensureEmployeeAccess($user, (int) $record->existing_employee_id);
    }

    private function resolveAuditAdminId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        $query = Admin::query()->where('id', $user->id);
        if (!empty($user->email)) {
            $query->orWhere('email', $user->email);
        }
        if (!empty($user->username)) {
            $query->orWhere('username', $user->username);
        }

        return $query->value('id');
    }
}
