<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FlashView;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Role;
use App\Models\SocialPost;
use App\Models\SocialComment;
use App\Models\SocialPostReport;
use App\Models\User;
use App\Helpers\SMPush\SMPushNotification;
use App\Services\Community\CommunityMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CommunityController extends Controller
{
    public function flashes(Request $request)
    {
        $this->authorize('flash_view.manage');

        $now = now();
        $statusCounts = [
            'all' => FlashView::count(),
            'live' => FlashView::active()->count(),
            'scheduled' => FlashView::where('is_active', true)->where('starts_at', '>', $now)->count(),
            'draft' => FlashView::where('is_active', false)->count(),
            'expired' => FlashView::where('is_active', true)->whereNotNull('ends_at')->where('ends_at', '<=', $now)->count(),
        ];
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $status = in_array($request->query('status'), ['live', 'scheduled', 'draft', 'expired'], true)
            ? $request->query('status') : 'all';

        $mediaType = in_array($request->query('media_type'), CommunityMedia::TYPES, true) ? $request->query('media_type') : '';
        $displayPolicy = in_array($request->query('display_policy'), ['once', 'session'], true) ? $request->query('display_policy') : '';
        $perPage = in_array((string) $request->query('per_page'), ['25', '50', '100', '200', 'all'], true) ? (string) $request->query('per_page') : '25';

        $query = FlashView::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('body', 'like', '%'.$search.'%')))
            ->when($status === 'live', fn ($query) => $query
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now)))
            ->when($status === 'scheduled', fn ($query) => $query->where('is_active', true)->where('starts_at', '>', $now))
            ->when($status === 'draft', fn ($query) => $query->where('is_active', false))
            ->when($status === 'expired', fn ($query) => $query->where('is_active', true)->whereNotNull('ends_at')->where('ends_at', '<=', $now));

        $query->when($mediaType !== '', fn ($query) => $query->where('media_type', $mediaType))
            ->when($displayPolicy !== '', fn ($query) => $query->where('display_policy', $displayPolicy));
        $items = $query->orderByDesc('priority')->orderByDesc('id')
            ->paginate($perPage === 'all' ? max(1, (clone $query)->count()) : (int) $perPage)->withQueryString();
        $roleIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->role_ids)->unique()->values();
        $branchIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->branch_ids)->unique()->values();
        $departmentIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->department_ids)->unique()->values();

        return view('admin.community.flashes', [
            'items' => $items,
            'statusCounts' => $statusCounts,
            'search' => $search,
            'status' => $status,
            'mediaType' => $mediaType,
            'displayPolicy' => $displayPolicy,
            'perPage' => $perPage,
            'roleNames' => Role::whereIn('id', $roleIds)->pluck('name', 'id')->all(),
            'branchNames' => Branch::withoutGlobalScopes()->whereIn('id', $branchIds)->pluck('name', 'id')->all(),
            'departmentNames' => Department::withoutGlobalScopes()->whereIn('id', $departmentIds)->pluck('dept_name', 'id')->all(),
        ]);
    }

    public function editFlash(?FlashView $flashView = null)
    {
        $this->authorize('flash_view.manage');
        return view('admin.community.flash-form', ['item' => $flashView ?? new FlashView,
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::withoutGlobalScopes()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::withoutGlobalScopes()->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']),
            'types' => CommunityMedia::TYPES]);
    }

    public function saveFlash(Request $request, CommunityMedia $media, ?FlashView $flashView = null)
    {
        $this->authorize('flash_view.manage');
        $request->merge(['role_ids' => $request->input('role_ids', [])]);
        $settings = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'link_url' => ['nullable', 'url:https', 'max:4096'], 'link_label' => ['nullable', 'string', 'max:80'],
            'display_policy' => ['required', Rule::in(['once', 'session'])],
            'role_ids' => ['nullable', 'array'], 'role_ids.*' => ['integer', 'exists:roles,id'],
            'branch_ids' => ['nullable', 'array'], 'branch_ids.*' => ['integer', 'exists:branches,id'],
            'department_ids' => ['nullable', 'array'], 'department_ids.*' => ['integer', 'exists:departments,id'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', Rule::when($request->filled('starts_at'), 'after:starts_at')],
            'priority' => ['required', 'integer', 'between:-1000,1000'],
        ]);
        $settings['role_ids'] = array_map('intval', $settings['role_ids'] ?? []);
        $settings['branch_ids'] = array_map('intval', $settings['branch_ids'] ?? []);
        $settings['department_ids'] = array_map('intval', $settings['department_ids'] ?? []);
        $settings['is_active'] = $request->boolean('is_active');
        $settings['starts_at'] = $request->filled('starts_at') ? $request->starts_at : null;
        $settings['ends_at'] = $request->filled('ends_at') ? $request->ends_at : null;
        $content = $media->content($request);
        $sendAlertNow = $request->boolean('alert_now');
        if ($flashView) {
            $flashView->fill($settings + $content);
            if ($request->boolean('show_again')) $flashView->revision++;
            $flashView->save();
        } else {
            $flashView = FlashView::create($settings + $content);
        }
        Log::info('Flash View saved', ['flash_view_id' => $flashView->id, 'actor_id' => auth()->id()]);

        if ($sendAlertNow) {
            if (!FlashView::active()->whereKey($flashView->id)->exists()) {
                return redirect()->route('admin.community.flashes')->with('warning', 'Flash View saved, but no alert was sent because it is not live yet. Enable it and make its schedule current to alert employees.');
            }

            $delivery = $this->deliverFlashAlert($flashView);
            if ($delivery['eligible'] === 0) {
            return redirect()->route('admin.community.flashes')->with('warning', 'Flash View saved, but no alert was sent. '.$this->noFlashRecipientsMessage($delivery));
            }
            if ($delivery['sent'] === 0) {
                return redirect()->route('admin.community.flashes')->with('danger', 'Flash View saved, but Firebase did not accept the push alert. '.$delivery['error']);
            }
            if ($delivery['failed'] > 0) {
                return redirect()->route('admin.community.flashes')->with('warning', 'Flash View saved. Firebase accepted the alert for '.$delivery['sent'].' employee(s); '.$delivery['failed'].' failed. Check the server log.');
            }

            return redirect()->route('admin.community.flashes')->with('success', 'Flash View saved and Firebase accepted the alert for '.$delivery['sent'].' employee(s).');
        }

        return redirect()->route('admin.community.flashes')->with('success', 'Flash View saved.');
    }

    public function deleteFlash(FlashView $flashView)
    {
        $this->authorize('flash_view.manage');
        Log::info('Flash View deleted', ['flash_view_id' => $flashView->id, 'actor_id' => auth()->id()]);
        $flashView->delete();
        return back()->with('success', 'Flash View deleted.');
    }

    public function toggleFlash(FlashView $flashView)
    {
        $this->authorize('flash_view.manage');
        $flashView->update(['is_active' => !$flashView->is_active]);
        Log::info('Flash View status changed', [
            'flash_view_id' => $flashView->id,
            'is_active' => $flashView->is_active,
            'actor_id' => auth()->id(),
        ]);
        return back()->with('success', $flashView->is_active ? 'Flash View enabled.' : 'Flash View turned off.');
    }

    public function sendFlashAlert(FlashView $flashView)
    {
        $this->authorize('flash_view.manage');

        if (!FlashView::active()->whereKey($flashView->id)->exists()) {
            return back()->with('warning', 'Enable this Flash View and make sure its schedule is current before sending an alert.');
        }

        $delivery = $this->deliverFlashAlert($flashView);
        if ($delivery['eligible'] === 0) {
            return back()->with('warning', $this->noFlashRecipientsMessage($delivery));
        }
        if ($delivery['sent'] === 0) {
            return back()->with('danger', 'Firebase did not accept the push alert. '.$delivery['error']);
        }
        if ($delivery['failed'] > 0) {
            return back()->with('warning', 'Firebase accepted the alert for '.$delivery['sent'].' employee(s); '.$delivery['failed'].' failed. Check the server log.');
        }

        return back()->with('success', 'Firebase accepted the alert for '.$delivery['sent'].' employee(s).');
    }

    private function deliverFlashAlert(FlashView $flashView): array
    {
        $roleIds = array_values(array_filter(array_map('intval', (array) $flashView->role_ids)));
        $branchIds = array_values(array_filter(array_map('intval', (array) $flashView->branch_ids)));
        $departmentIds = array_values(array_filter(array_map('intval', (array) $flashView->department_ids)));

        $audience = User::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->when($roleIds, fn ($query) => $query->whereIn('role_id', $roleIds))
            ->when($branchIds, fn ($query) => $query->whereIn('branch_id', $branchIds))
            ->when($departmentIds, fn ($query) => $query->whereIn('department_id', $departmentIds));

        // SPAuthGateMW grants full API access to these privileged roles even
        // when their permissions are not individually assigned in the pivot.
        // Keep alert eligibility consistent with that API authorization rule.
        $hasFlashViewAccess = fn ($role) => $role
            ->whereIn('slug', ['admin', 'super-admin', 'supper-admin'])
            ->orWhere(fn ($role) => $role
                ->whereHas('permission', fn ($permission) => $permission->where('permission_key', 'mobile.menu.flash-view'))
                ->whereHas('permission', fn ($permission) => $permission->where('permission_key', 'flash_view.view')));
        $hasToken = fn ($query) => $query->whereNotNull('fcm_token')
            ->whereRaw("TRIM(COALESCE(fcm_token, '')) <> ''");
        $accessibleAudience = (clone $audience)->whereHas('role', $hasFlashViewAccess);
        $withToken = (clone $audience)->where($hasToken);
        $eligibleUsers = (clone $accessibleAudience)->where($hasToken);

        $audienceCount = (clone $audience)->count();
        $tokenCount = (clone $withToken)->count();
        $accessCount = (clone $accessibleAudience)->count();
        $users = $eligibleUsers->get(['id', 'fcm_token']);

        $recipients = $users->pluck('fcm_token', 'id')->filter()->toArray();
        if (!$recipients) return [
            'eligible' => 0, 'sent' => 0, 'failed' => 0, 'error' => null,
            'audience_count' => $audienceCount, 'token_count' => $tokenCount, 'access_count' => $accessCount,
        ];

        $delivery = SMPushNotification::smSend(
            title: $flashView->title,
            message: (string) ($flashView->body ?: 'You have a new Flash View announcement.'),
            data: [
                'type' => 'flash_view',
                'flash_view_id' => (string) $flashView->id,
                'revision' => (string) $flashView->revision,
            ],
            recipients: $recipients,
            highPriority: true,
        );

        $delivery['eligible'] = count($recipients);
        $delivery['audience_count'] = $audienceCount;
        $delivery['token_count'] = $tokenCount;
        $delivery['access_count'] = $accessCount;
        if ($delivery['sent'] === 0) return $delivery;

        $flashView->forceFill([
            'last_alert_sent_at' => now(),
            'last_alert_recipient_count' => $delivery['sent'],
        ])->save();
        Log::info('Flash View alert sent', [
            'flash_view_id' => $flashView->id,
            'eligible_count' => $delivery['eligible'],
            'accepted_count' => $delivery['sent'],
            'failed_count' => $delivery['failed'],
            'actor_id' => auth()->id(),
        ]);
        return $delivery;
    }

    private function noFlashRecipientsMessage(array $delivery): string
    {
        $audience = (int) ($delivery['audience_count'] ?? 0);
        $tokens = (int) ($delivery['token_count'] ?? 0);
        $access = (int) ($delivery['access_count'] ?? 0);

        if ($audience === 0) {
            return 'No active employees match the selected roles, branches, and departments.';
        }
        if ($tokens === 0 && $access === 0) {
            return $audience.' active employee(s) match the audience, but none have both a push token and Flash View access ('.$tokens.' with tokens; '.$access.' with access). Register mobile push tokens and grant the Flash View menu and view permissions to the audience roles.';
        }
        if ($tokens === 0) {
            return $audience.' active employee(s) match the audience, but none have a registered push token. Ask employees to sign in to the mobile app and allow notifications.';
        }
        if ($access === 0) {
            return $tokens.' matching employee(s) have push tokens, but their roles do not have both the Flash View menu and view permissions.';
        }

        return $audience.' active employee(s) match the audience; '.$tokens.' have push tokens and '.$access.' have Flash View access, but none have both. Check the audience and role permissions.';
    }

    public function feed(Request $request)
    {
        $this->authorize('social_feed.moderate');

        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $filter = in_array($request->query('filter'), ['promoted', 'pinned'], true) ? $request->query('filter') : 'all';
        $base = SocialPost::query();
        $counts = [
            'all' => (clone $base)->count(),
            'promoted' => (clone $base)->where('is_announcement', true)->count(),
            'pinned' => (clone $base)->whereNotNull('pinned_at')->count(),
        ];
        $posts = $base->with(['author', 'original.author', 'media', 'poll'])
            ->withCount(['comments', 'reactions', 'shares'])
            ->when($filter === 'promoted', fn ($query) => $query->where('is_announcement', true))
            ->when($filter === 'pinned', fn ($query) => $query->whereNotNull('pinned_at'))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('body', 'like', '%'.$search.'%')
                ->orWhereHas('author', fn ($author) => $author->where('name', 'like', '%'.$search.'%'))))
            ->orderByDesc('pinned_at')->orderByDesc('is_announcement')->latest('id')->paginate(20)->withQueryString();

        return view('admin.community.feed', compact('posts', 'search', 'filter', 'counts'));
    }

    public function editPost(SocialPost $post)
    {
        $this->authorize('social_feed.moderate');
        $departments = $post->company_id
            ? Department::withoutGlobalScopes()->where('company_id', $post->company_id)->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id'])
            : collect();
        $employees = $post->company_id
            ? User::withoutGlobalScopes()->where('company_id', $post->company_id)->where('is_active', 1)->whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'department_id'])
            : collect();
        return view('admin.community.post-form', compact('post', 'departments', 'employees'));
    }

    public function createPost()
    {
        $this->authorize('social_feed.moderate');
        $companyId = auth()->user()?->company_id;
        abort_unless($companyId, 422, 'Your administrator account must be assigned to a company before creating feed posts.');
        $departments = Department::withoutGlobalScopes()->where('company_id', $companyId)->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']);
        $employees = User::withoutGlobalScopes()->where('company_id', $companyId)->where('is_active', 1)->whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'department_id']);
        $types = CommunityMedia::TYPES;
        return view('admin.community.post-create', compact('departments', 'employees', 'types'));
    }

    public function storePost(Request $request, CommunityMedia $media)
    {
        $this->authorize('social_feed.moderate');
        $companyId = auth()->user()?->company_id;
        abort_unless($companyId, 422, 'Your administrator account must be assigned to a company before creating feed posts.');

        $request->merge(['media_type' => $request->input('media_type', 'text')]);
        $audience = $this->moderatorAudience($request, (int) $companyId);
        $content = $media->content($request);
        $post = SocialPost::create($content + $audience + [
            'user_id' => auth()->id(),
            'company_id' => $companyId,
            'is_announcement' => true,
            'pinned_at' => $request->boolean('pin_now') ? now() : null,
            'pinned_by' => $request->boolean('pin_now') ? auth()->id() : null,
        ]);
        Log::info('Social announcement created by moderator', ['post_id' => $post->id, 'company_id' => $companyId, 'actor_id' => auth()->id()]);
        return redirect()->route('admin.community.feed')->with('success', 'Announcement published to the Social Feed.');
    }

    private function moderatorAudience(Request $request, int $companyId): array
    {
        $data = $request->validate([
            'visibility' => ['required', Rule::in(['company', 'department', 'employees', 'management', 'hr'])],
            'audience_department_ids' => ['nullable', 'array', 'max:50'],
            'audience_department_ids.*' => ['integer', 'distinct', 'exists:departments,id'],
            'audience_user_ids' => ['nullable', 'array', 'max:200'],
            'audience_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        $departmentIds = $data['visibility'] === 'department'
            ? array_values(array_map('intval', $data['audience_department_ids'] ?? [])) : [];
        $employeeIds = $data['visibility'] === 'employees'
            ? array_values(array_map('intval', $data['audience_user_ids'] ?? [])) : [];
        if ($data['visibility'] === 'department' && !$departmentIds) {
            throw \Illuminate\Validation\ValidationException::withMessages(['audience_department_ids' => 'Choose at least one department for this audience.']);
        }
        if ($data['visibility'] === 'employees' && !$employeeIds) {
            throw \Illuminate\Validation\ValidationException::withMessages(['audience_user_ids' => 'Choose at least one employee for this audience.']);
        }
        if ($departmentIds && Department::withoutGlobalScopes()->where('company_id', $companyId)->whereIn('id', $departmentIds)->count() !== count($departmentIds)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['audience_department_ids' => 'Choose departments from this company only.']);
        }
        if ($employeeIds && User::withoutGlobalScopes()->where('company_id', $companyId)->where('is_active', 1)->whereNull('deleted_at')->whereIn('id', $employeeIds)->count() !== count($employeeIds)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['audience_user_ids' => 'Choose employees from this company only.']);
        }
        return [
            'visibility' => $data['visibility'],
            'audience_department_ids' => $departmentIds ?: null,
            'audience_user_ids' => $employeeIds ?: null,
        ];
    }

    public function updatePost(Request $request, SocialPost $post)
    {
        $this->authorize('social_feed.moderate');
        $data = $request->validate(['body' => ['nullable', 'string', 'max:10000']]);
        $audience = $this->moderatorAudience($request, (int) $post->company_id);
        $post->forceFill([
            'body' => $data['body'] ?? null,
        ] + $audience)->save();
        Log::info('Social post edited by moderator', [
            'post_id' => $post->id,
            'visibility' => $post->visibility,
            'actor_id' => auth()->id(),
        ]);
        return redirect()->route('admin.community.feed')->with('success', 'Post updated.');
    }

    public function togglePostPromotion(SocialPost $post)
    {
        $this->authorize('social_feed.moderate');
        $post->forceFill(['is_announcement' => !$post->is_announcement])->save();
        Log::info('Social post promotion changed', ['post_id' => $post->id, 'is_announcement' => $post->is_announcement, 'actor_id' => auth()->id()]);
        return back()->with('success', $post->is_announcement ? 'Post promoted as an announcement.' : 'Post removed from announcements.');
    }

    public function togglePostPin(SocialPost $post)
    {
        $this->authorize('social_feed.moderate');
        $post->forceFill($post->pinned_at
            ? ['pinned_at' => null, 'pinned_by' => null]
            : ['pinned_at' => now(), 'pinned_by' => auth()->id()])->save();
        Log::info('Social post pin changed by moderator', ['post_id' => $post->id, 'is_pinned' => (bool) $post->pinned_at, 'actor_id' => auth()->id()]);
        return back()->with('success', $post->pinned_at ? 'Post pinned to the top of the feed.' : 'Post unpinned.');
    }

    public function comments(Request $request, SocialPost $post)
    {
        $this->authorize('social_feed.moderate');
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $comments = $post->comments()->with(['author', 'parent.author'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('body', 'like', '%'.$search.'%')
                ->orWhereHas('author', fn ($author) => $author->where('name', 'like', '%'.$search.'%'))))
            ->latest('id')->paginate(30)->withQueryString();
        return view('admin.community.comments', ['post' => $post,
            'comments' => $comments,
            'search' => $search,
        ]);
    }

    public function reports(Request $request)
    {
        $this->authorize('social_feed.moderate');
        $status = in_array($request->query('status'), ['open', 'reviewed', 'dismissed'], true) ? $request->query('status') : 'all';
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $base = SocialPostReport::query();
        $counts = [
            'all' => (clone $base)->count(),
            'open' => (clone $base)->where('status', 'open')->count(),
            'reviewed' => (clone $base)->where('status', 'reviewed')->count(),
            'dismissed' => (clone $base)->where('status', 'dismissed')->count(),
        ];
        $reports = $base->with(['post.author', 'reporter'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('reason', 'like', '%'.$search.'%')
                ->orWhereHas('post', fn ($post) => $post->where('body', 'like', '%'.$search.'%'))
                ->orWhereHas('reporter', fn ($reporter) => $reporter->where('name', 'like', '%'.$search.'%'))))
            ->latest()->paginate(30)->withQueryString();
        return view('admin.community.reports', [
            'reports' => $reports,
            'counts' => $counts,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function resolveReport(Request $request, SocialPostReport $report)
    {
        $this->authorize('social_feed.moderate');
        $status = $request->validate(['status' => ['required', Rule::in(['reviewed', 'dismissed'])]])['status'];
        $report->forceFill(['status' => $status, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()])->save();
        Log::info('Social post report resolved', ['report_id' => $report->id, 'status' => $status, 'actor_id' => auth()->id()]);
        return back()->with('success', 'Report marked as '.$status.'.');
    }

    public function deletePost(SocialPost $post)
    {
        $this->authorize('social_feed.moderate');
        Log::info('Social post moderated', ['post_id' => $post->id, 'actor_id' => auth()->id()]);
        $post->delete();
        return back()->with('success', 'Post removed from the feed.');
    }

    public function deleteComment(SocialComment $comment)
    {
        $this->authorize('social_feed.moderate');
        $commentIds = [$comment->id];
        $frontier = $commentIds;
        while ($frontier) {
            $frontier = SocialComment::whereIn('parent_id', $frontier)->whereNotIn('id', $commentIds)->pluck('id')->all();
            $commentIds = array_merge($commentIds, $frontier);
        }

        DB::transaction(fn () => SocialComment::whereIn('id', $commentIds)->delete());
        Log::info('Social comment thread moderated', ['comment_id' => $comment->id, 'removed_count' => count($commentIds), 'actor_id' => auth()->id()]);
        return back()->with('success', count($commentIds) > 1
            ? 'Comment and '.(count($commentIds) - 1).' replies removed.'
            : 'Comment removed.');
    }
}
