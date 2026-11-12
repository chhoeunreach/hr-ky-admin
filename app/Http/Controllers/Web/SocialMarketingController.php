<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SocialReward;
use App\Models\SocialRewardAudit;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SocialMarketingController extends Controller
{
    use CustomAuthorizesRequests;

    private array $photoFields = SocialReward::PHOTO_FIELDS;

    public function index(Request $request)
    {
        $this->authorize('social_reward.view');

        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);
        $summary = (clone $query)->selectRaw(
            'COUNT(*) as total, COALESCE(SUM(reward_points), 0) as points, '
            .'COUNT(DISTINCT existing_employee_id) as employees, '
            .'SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked'
        )->first();
        $todayCount = (clone $query)->whereDate('log_date', now()->toDateString())->count();
        $records = $query->paginate(25)->withQueryString();
        $employees = $this->employeeOptions();

        return view('admin.socialMarketing.index', compact(
            'records', 'filters', 'summary', 'todayCount', 'employees'
        ));
    }

    public function create()
    {
        $this->authorize('social_reward.manage');

        return view('admin.socialMarketing.create', [
            'employees' => $this->employeeOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('social_reward.manage');

        $data = $this->validateRecord($request, true);
        $exists = SocialReward::query()
            ->where('existing_employee_id', $data['employee_id'])
            ->whereDate('log_date', $data['log_date'])
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'log_date' => 'This employee already has a Social Media Marketing record for that date.',
            ]);
        }

        $record = DB::transaction(function () use ($request, $data) {
            return SocialReward::create([
                'existing_employee_id' => $data['employee_id'],
                'log_date' => $data['log_date'],
                'fb_post_url' => $data['fb_post_url'],
                'fb_story_url' => $data['fb_story_url'],
                'tiktok_url' => $data['tiktok_url'],
                'fb_post_photo' => $this->storePhoto($request, 'fb_post_photo'),
                'fb_story_photo' => $this->storePhoto($request, 'fb_story_photo'),
                'tiktok_photo' => $this->storePhoto($request, 'tiktok_photo'),
                'reward_points' => 1,
                'is_locked' => true,
            ]);
        });

        Log::info('Social marketing record created', [
            'record_id' => $record->id,
            'employee_id' => $record->existing_employee_id,
            'actor_id' => $this->auditAdminId(),
        ]);

        return redirect()
            ->route('admin.social-marketing.show', $record)
            ->with('success', 'Social Media Marketing record created successfully.');
    }

    public function show(SocialReward $socialReward)
    {
        $this->authorize('social_reward.view');

        $socialReward->load(['employee.branch', 'employee.department', 'audits.admin']);

        return view('admin.socialMarketing.show', ['record' => $socialReward]);
    }

    public function edit(SocialReward $socialReward)
    {
        $this->authorize('social_reward.manage');

        $socialReward->load(['employee', 'audits.admin']);

        return view('admin.socialMarketing.edit', [
            'record' => $socialReward,
            'employees' => $this->employeeOptions(),
        ]);
    }

    public function update(Request $request, SocialReward $socialReward)
    {
        $this->authorize('social_reward.manage');

        $data = $this->validateRecord($request, false);
        $unlock = $request->boolean('unlock');

        DB::transaction(function () use ($request, $socialReward, $data, $unlock) {
            $payload = [
                'fb_post_url' => $data['fb_post_url'],
                'fb_story_url' => $data['fb_story_url'],
                'tiktok_url' => $data['tiktok_url'],
            ];

            foreach ($this->photoFields as $field) {
                if ($request->hasFile($field)) {
                    $payload[$field] = $this->storePhoto($request, $field);
                }
            }

            if ($unlock) {
                $payload['is_locked'] = false;
            }

            $socialReward->update($payload);
            $this->recordAudit(
                $socialReward,
                $data['reason'],
                $unlock ? 'UNLOCK_OVERRIDE_UPDATE' : 'MANUAL_OVERRIDE_UPDATE'
            );
        });

        return redirect()
            ->route('admin.social-marketing.show', $socialReward)
            ->with('success', $unlock
                ? 'Record updated, unlocked, and the override was added to the audit trail.'
                : 'Record updated and the override was added to the audit trail.');
    }

    public function destroy(SocialReward $socialReward)
    {
        $this->authorize('social_reward.manage');

        Log::info('Social marketing record deleted', [
            'record_id' => $socialReward->id,
            'employee_id' => $socialReward->existing_employee_id,
            'actor_id' => $this->auditAdminId(),
        ]);
        $socialReward->delete();

        return redirect()
            ->route('admin.social-marketing.index')
            ->with('success', 'Social Media Marketing record deleted.');
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(['locked', 'unlocked'])],
        ]);

        return [
            'search' => trim((string) ($data['search'] ?? '')),
            'employee_id' => $data['employee_id'] ?? null,
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
            'status' => $data['status'] ?? '',
        ];
    }

    private function filteredQuery(array $filters): Builder
    {
        return SocialReward::query()
            ->with('employee')
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->whereHas('employee', function (Builder $employee) use ($term) {
                    $employee->where(function (Builder $match) use ($term) {
                        $match->where('name', 'like', $term)
                            ->orWhere('username', 'like', $term)
                            ->orWhere('employee_code', 'like', $term);
                    });
                });
            })
            ->when($filters['employee_id'], fn (Builder $query, $id) => $query->where('existing_employee_id', $id))
            ->when($filters['date_from'], fn (Builder $query, $date) => $query->whereDate('log_date', '>=', $date))
            ->when($filters['date_to'], fn (Builder $query, $date) => $query->whereDate('log_date', '<=', $date))
            ->when($filters['status'] === 'locked', fn (Builder $query) => $query->where('is_locked', true))
            ->when($filters['status'] === 'unlocked', fn (Builder $query) => $query->where('is_locked', false))
            ->latest('log_date')
            ->latest('id');
    }

    private function validateRecord(Request $request, bool $creating): array
    {
        $required = $creating ? 'required' : 'nullable';

        return $request->validate([
            'employee_id' => [$required, 'integer', 'exists:users,id'],
            'log_date' => [$required, 'date'],
            'fb_post_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
            'fb_story_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
            'tiktok_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/\S+$/i'],
            'fb_post_photo' => ['nullable', 'file', 'image', 'max:5120'],
            'fb_story_photo' => ['nullable', 'file', 'image', 'max:5120'],
            'tiktok_photo' => ['nullable', 'file', 'image', 'max:5120'],
            'reason' => [$creating ? 'nullable' : 'required', 'string', 'max:2000'],
        ]);
    }

    private function storePhoto(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store('social-rewards', 'public');
    }

    private function recordAudit(SocialReward $record, string $reason, string $action): void
    {
        $adminId = $this->auditAdminId();

        Log::info('Social marketing record overridden', [
            'record_id' => $record->id,
            'action' => $action,
            'actor_id' => $adminId,
            'reason' => $reason,
        ]);

        if ($adminId === null) {
            return;
        }

        SocialRewardAudit::create([
            'target_record_id' => $record->id,
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
    }

    private function auditAdminId(): ?int
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
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

    private function employeeOptions()
    {
        return User::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->limit(5000)
            ->get(['id', 'name', 'username', 'employee_code']);
    }
}
