<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\KioskAttendanceEvent;
use App\Models\KioskDevice;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FaceKioskController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('list_employee');
        $companyId = $this->companyId();
        $branchId = Auth::user()?->branch_id;
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);

        $branches = Branch::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->whereKey($branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $devices = KioskDevice::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->with('branch:id,name')
            ->withCount('attendanceEvents')
            ->latest()
            ->get();

        $employees = User::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('is_active', true)
            ->where('status', 'verified')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with([
                'branch:id,name',
                'department:id,dept_name',
                'faceProfile:id,user_id,model_version,quality_score,is_active,enrolled_at',
            ])
            ->orderBy('name')
            ->paginate(50, ['*'], 'employees_page')
            ->withQueryString();

        $stats = [
            'employees' => User::query()->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where('is_active', true)->where('status', 'verified')->count(),
            'enrolled' => User::query()->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->whereHas('faceProfile', fn ($query) => $query->where('is_active', true))
                ->count(),
            'devices' => $devices->where('is_active', true)->count(),
            'today_events' => KioskAttendanceEvent::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->whereDate('captured_at', today())->count(),
        ];

        return view('admin.face-kiosk.index', compact(
            'branches',
            'devices',
            'employees',
            'stats',
            'search',
        ));
    }

    public function storeDevice(Request $request): RedirectResponse
    {
        $this->authorize('list_employee');
        $companyId = $this->companyId();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'branch_id' => ['required', 'integer'],
            'admin_pin' => ['required', 'digits_between:6,12', 'confirmed'],
        ]);

        $branch = Branch::query()->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereKey($validated['branch_id'])
            ->firstOrFail();
        if (Auth::user()?->branch_id && Auth::user()->branch_id !== $branch->id) {
            abort(403);
        }

        $plainToken = 'kiosk_' . Str::random(72);
        KioskDevice::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branch->id,
            'name' => $validated['name'],
            'token_prefix' => substr($plainToken, 0, 12),
            'token_hash' => hash('sha256', $plainToken),
            'admin_pin_hash' => Hash::make($validated['admin_pin']),
            'is_active' => true,
        ]);

        return back()
            ->with('success', 'Face kiosk created. Copy its token now; it will not be shown again.')
            ->with('kiosk_plain_token', $plainToken)
            ->with('kiosk_provisioning_payload', $this->provisioningPayload($request, $plainToken));
    }

    public function rotateToken(Request $request, KioskDevice $device): RedirectResponse
    {
        $this->authorizeDevice($device);
        $plainToken = 'kiosk_' . Str::random(72);
        $device->update([
            'token_prefix' => substr($plainToken, 0, 12),
            'token_hash' => hash('sha256', $plainToken),
            'provisioned_at' => null,
        ]);

        return back()
            ->with('success', 'Kiosk token rotated. The old token is now invalid.')
            ->with('kiosk_plain_token', $plainToken)
            ->with('kiosk_provisioning_payload', $this->provisioningPayload($request, $plainToken));
    }

    public function updatePin(Request $request, KioskDevice $device): RedirectResponse
    {
        $this->authorizeDevice($device);
        $validated = $request->validate([
            'admin_pin' => ['required', 'digits_between:6,12', 'confirmed'],
        ]);
        $device->update(['admin_pin_hash' => Hash::make($validated['admin_pin'])]);

        return back()->with('success', 'Kiosk administrator PIN updated.');
    }

    public function toggle(KioskDevice $device): RedirectResponse
    {
        $this->authorizeDevice($device);
        $device->update(['is_active' => !$device->is_active]);

        return back()->with('success', $device->is_active ? 'Kiosk enabled.' : 'Kiosk disabled.');
    }

    private function authorizeDevice(KioskDevice $device): void
    {
        $this->authorize('list_employee');
        if ($device->company_id !== $this->companyId()) {
            abort(404);
        }
        if (Auth::user()?->branch_id && Auth::user()->branch_id !== $device->branch_id) {
            abort(403);
        }
    }

    private function companyId(): int
    {
        return (int) (Auth::user()?->company_id ?: Company::query()->value('id'));
    }

    private function provisioningPayload(Request $request, string $plainToken): string
    {
        return json_encode([
            'type' => 'digitalhrs_face_kiosk',
            'version' => 1,
            'server_url' => $request->getSchemeAndHttpHost(),
            'device_token' => $plainToken,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
