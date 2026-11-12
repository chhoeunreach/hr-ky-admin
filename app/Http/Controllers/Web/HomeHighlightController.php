<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\AppHelper;
use App\Models\Branch;
use App\Models\Department;
use App\Models\HomeHighlight;
use App\Models\Role;
use App\Models\User;
use App\Services\Community\CommunityMedia;
use App\Services\Community\HomeHighlightAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HomeHighlightController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('home_highlight.manage');

        $companyId = AppHelper::getAuthUserCompanyId();
        $now = now();
        $statusCounts = [
            'all' => HomeHighlight::where('company_id', $companyId)->count(),
            'live' => HomeHighlight::where('company_id', $companyId)->active()->count(),
            'scheduled' => HomeHighlight::where('company_id', $companyId)->where('is_active', true)->where('starts_at', '>', $now)->count(),
            'draft' => HomeHighlight::where('company_id', $companyId)->where('is_active', false)->count(),
            'expired' => HomeHighlight::where('company_id', $companyId)->where('is_active', true)->whereNotNull('ends_at')->where('ends_at', '<=', $now)->count(),
        ];
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $status = in_array($request->query('status'), ['live', 'scheduled', 'draft', 'expired'], true)
            ? $request->query('status') : 'all';

        $query = HomeHighlight::query()
            ->where('company_id', $companyId)
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

        $items = $query->orderByDesc('priority')->orderByDesc('id')->paginate(20)->withQueryString();
        $roleIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->role_ids)->unique()->values();
        $branchIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->branch_ids)->unique()->values();
        $departmentIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->department_ids)->unique()->values();
        $userIds = $items->getCollection()->flatMap(fn ($item) => (array) $item->user_ids)->unique()->values();

        return view('admin.home_highlight.index', [
            'items' => $items,
            'statusCounts' => $statusCounts,
            'displaySettings' => \App\Models\HomeHighlightDisplaySetting::forCompany($companyId),
            'search' => $search,
            'status' => $status,
            'roleNames' => Role::whereIn('id', $roleIds)->pluck('name', 'id')->all(),
            'branchNames' => Branch::withoutGlobalScopes()->whereIn('id', $branchIds)->pluck('name', 'id')->all(),
            'departmentNames' => Department::withoutGlobalScopes()->whereIn('id', $departmentIds)->pluck('dept_name', 'id')->all(),
            'userNames' => User::withoutGlobalScopes()->whereIn('id', $userIds)->pluck('name', 'id')->all(),
        ]);
    }

    public function saveDisplaySettings(Request $request)
    {
        $this->authorize('home_highlight.manage');
        $data = $request->validate([
            'auto_switch_enabled' => ['required', 'boolean'],
            'late_bonus_seconds' => ['required', 'integer', 'between:5,300'],
            'highlights_seconds' => ['required', 'integer', 'between:5,300'],
        ]);
        \App\Models\HomeHighlightDisplaySetting::updateOrCreate(
            ['company_id' => AppHelper::getAuthUserCompanyId()], $data);
        return redirect()->route('admin.community.home-highlights.index')
            ->with('success', 'Home card switching settings saved. Open phones update within one minute.');
    }

    public function create()
    {
        $this->authorize('home_highlight.manage');

        return view('admin.home_highlight.form', $this->formData(new HomeHighlight));
    }

    public function edit(HomeHighlight $homeHighlight)
    {
        $this->authorize('home_highlight.manage');
        $this->ensureCompanyAccess($homeHighlight);

        return view('admin.home_highlight.form', $this->formData($homeHighlight));
    }

    public function store(Request $request)
    {
        $this->authorize('home_highlight.manage');
        $this->persist($request, new HomeHighlight);

        return redirect()->route('admin.community.home-highlights.index')->with('success', 'Home Highlight saved.');
    }

    public function update(Request $request, HomeHighlight $homeHighlight)
    {
        $this->authorize('home_highlight.manage');
        $this->ensureCompanyAccess($homeHighlight);
        $this->persist($request, $homeHighlight);

        return redirect()->route('admin.community.home-highlights.index')->with('success', 'Home Highlight updated.');
    }

    public function toggle(HomeHighlight $homeHighlight)
    {
        $this->authorize('home_highlight.manage');
        $this->ensureCompanyAccess($homeHighlight);
        $homeHighlight->update(['is_active' => !$homeHighlight->is_active, 'updated_by' => (auth('admin')->id() ?? auth()->id())]);

        return back()->with('success', $homeHighlight->is_active ? 'Home Highlight enabled.' : 'Home Highlight turned off.');
    }

    public function destroy(HomeHighlight $homeHighlight)
    {
        $this->authorize('home_highlight.manage');
        $this->ensureCompanyAccess($homeHighlight);
        $imageUrl = $homeHighlight->image_url;
        $homeHighlight->delete();
        if ($imageUrl) {
            CommunityMedia::removeUnused($imageUrl);
        }

        return back()->with('success', 'Home Highlight deleted.');
    }

    public function sendNow(HomeHighlight $homeHighlight, HomeHighlightAlert $alerts)
    {
        $this->authorize('home_highlight.manage');
        $this->ensureCompanyAccess($homeHighlight);
        if (!HomeHighlight::active()->whereKey($homeHighlight->id)->exists()) {
            return back()->with('warning', 'Publish this highlight and make its schedule current before sending.');
        }

        try {
            $delivery = $alerts->send($homeHighlight);
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('danger', 'The notification could not be sent. Check the server log before retrying.');
        }
        Log::info('Home Highlight notification requested', [
            'home_highlight_id' => $homeHighlight->id,
            'actor_id' => auth('admin')->id() ?? auth()->id(),
            'eligible_count' => $delivery['eligible'],
            'accepted_count' => $delivery['sent'],
            'failed_count' => $delivery['failed'],
        ]);

        if ($delivery['eligible'] === 0) {
            return back()->with('warning', 'No matching active employees have a registered push token and Home Highlights access.');
        }
        if ($delivery['sent'] === 0) {
            return back()->with('danger', 'Firebase did not accept the notification. '.($delivery['error'] ?? 'Check the server log.'));
        }
        if ($delivery['failed'] > 0) {
            return back()->with('warning', 'Firebase accepted the notification for '.$delivery['sent'].' employee(s); '.$delivery['failed'].' failed.');
        }

        return back()->with('success', 'Firebase accepted the notification for '.$delivery['sent'].' employee(s).');
    }

    private function ensureCompanyAccess(HomeHighlight $item): void
    {
        abort_unless((int) $item->company_id === (int) AppHelper::getAuthUserCompanyId(), 404);
    }

    private function formData(HomeHighlight $item): array
    {
        $companyId = AppHelper::getAuthUserCompanyId();
        $branchNameById = Branch::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('name', 'id');
        $departments = Department::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderBy('dept_name')
            ->get(['id', 'dept_name', 'branch_id']);

        return [
            'item' => $item,
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'departments' => $departments,
            'employees' => User::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'name', 'department_id']),
            'branchNameById' => $branchNameById,
        ];
    }

    private function persist(Request $request, HomeHighlight $item): HomeHighlight
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180', function ($attribute, $value, $fail) {
                if (trim($value) === '') $fail('Enter a highlight title.');
            }],
            'body' => ['nullable', 'string', 'max:20000'],
            'image_url' => ['nullable', 'url:http,https', 'max:4096'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:10240'],
            'link_url' => ['nullable', 'url:https', 'max:4096'],
            'link_label' => ['nullable', 'string', 'max:80'],
            'role_ids' => ['nullable', 'array'], 'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
            'branch_ids' => ['nullable', 'array'], 'branch_ids.*' => ['integer', 'distinct', 'exists:branches,id'],
            'department_ids' => ['nullable', 'array'], 'department_ids.*' => ['integer', 'distinct', 'exists:departments,id'],
            'user_ids' => ['nullable', 'array'], 'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
            'remove_image' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', Rule::when($request->filled('starts_at'), 'after:starts_at')],
            'priority' => ['required', 'integer', 'between:-1000,1000'],
        ]);

        $companyId = AppHelper::getAuthUserCompanyId();
        if (!empty($data['branch_ids'])
            && Branch::withoutGlobalScopes()->where('company_id', $companyId)->whereIn('id', $data['branch_ids'])->count() !== count($data['branch_ids'])) {
            throw ValidationException::withMessages(['branch_ids' => 'Select branches from your company.']);
        }
        if (!empty($data['department_ids'])
            && Department::withoutGlobalScopes()->where('company_id', $companyId)->whereIn('id', $data['department_ids'])->count() !== count($data['department_ids'])) {
            throw ValidationException::withMessages(['department_ids' => 'Select departments from your company.']);
        }
        if (!empty($data['user_ids'])
            && User::withoutGlobalScopes()->where('company_id', $companyId)->where('is_active', 1)->whereNull('deleted_at')->whereIn('id', $data['user_ids'])->count() !== count($data['user_ids'])) {
            throw ValidationException::withMessages(['user_ids' => 'Select employees from your company.']);
        }

        $payload = [
            'title' => trim($data['title']),
            'body' => $data['body'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'link_label' => $data['link_label'] ?? null,
            'role_ids' => array_map('intval', $data['role_ids'] ?? []),
            'branch_ids' => array_map('intval', $data['branch_ids'] ?? []),
            'department_ids' => array_map('intval', $data['department_ids'] ?? []),
            'user_ids' => array_map('intval', $data['user_ids'] ?? []),
            'starts_at' => $request->filled('starts_at') ? $data['starts_at'] : null,
            'ends_at' => $request->filled('ends_at') ? $data['ends_at'] : null,
            'priority' => $data['priority'],
            'is_active' => $request->boolean('is_active'),
            'updated_by' => (auth('admin')->id() ?? auth()->id()),
        ];

        $previousImage = null;
        if ($request->hasFile('image')) {
            $previousImage = $item->image_url;
            $path = $request->file('image')->store('community', 'public');
            $payload['image_url'] = url(Storage::disk('public')->url($path));
        } elseif ($request->boolean('remove_image')) {
            $previousImage = $item->image_url;
            $payload['image_url'] = null;
        } elseif ($request->filled('image_url')) {
            if ($data['image_url'] !== $item->image_url) {
                $previousImage = $item->image_url;
            }
            $payload['image_url'] = $data['image_url'];
        } elseif ($request->exists('image_url')) {
            $previousImage = $item->image_url;
            $payload['image_url'] = null;
        }

        if (!$item->exists) {
            $payload['company_id'] = $companyId;
            $payload['created_by'] = (auth('admin')->id() ?? auth()->id());
        }

        $item->fill($payload)->save();

        if ($previousImage) {
            CommunityMedia::removeUnused($previousImage);
        }

        return $item;
    }
}
