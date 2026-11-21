<?php

namespace App\Modules\AppSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AppSecurity\Models\SecurityAuditLog;
use App\Modules\AppSecurity\Models\SecurityRelease;
use App\Modules\AppSecurity\Services\ApkInspectionService;
use App\Modules\AppSecurity\Services\AppSecurityException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppSecurityReleaseController extends Controller
{
    public function publicPage(): View
    {
        return view('app-security.download', ['release' => $this->latestRelease()]);
    }

    public function latest(): JsonResponse
    {
        $release = $this->latestRelease();
        if (!$release) return response()->json(['error' => 'no_active_release'], 404);
        return response()->json($this->publicRelease($release));
    }

    public function releaseVersion(string $version): JsonResponse
    {
        $release = $this->findPublicRelease($version);
        abort_unless($release, 404);
        return response()->json($this->publicRelease($release));
    }

    public function checksum(string $version)
    {
        $release = $this->findPublicRelease($version);
        abort_unless($release, 404);
        return response($release->apk_sha256 . "\n", 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function download()
    {
        return $this->downloadRelease($this->latestRelease());
    }

    public function downloadVersion(string $version)
    {
        return $this->downloadRelease($this->findPublicRelease($version));
    }

    private function downloadRelease(?SecurityRelease $release)
    {
        abort_unless($release && $release->download_path, 404);
        abort_unless(Storage::disk('local')->exists($release->download_path), 404);
        return Storage::disk('local')->download(
            $release->download_path,
            'kneayerng-android-' . $release->version_name . '.apk',
            ['Content-Type' => 'application/vnd.android.package-archive', 'Cache-Control' => 'public, max-age=300']
        );
    }

    private function findPublicRelease(string $version): ?SecurityRelease
    {
        return SecurityRelease::query()
            ->where('package_name', config('app_security.package_name'))
            ->whereIn('release_status', ['active', 'archived'])
            ->where(function ($query) use ($version): void {
                $query->where('version_name', $version);
                if (ctype_digit($version)) $query->orWhere('version_code', (int) $version);
            })
            ->first();
    }

    public function store(Request $request, ApkInspectionService $inspector)
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_releases');
        $data = $request->validate([
            'apk' => ['required', 'file', 'max:512000'],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'minimum_supported_version' => ['required', 'integer', 'min:0'],
            'is_mandatory' => ['sometimes', 'boolean'],
        ]);
        if (strtolower($request->file('apk')->getClientOriginalExtension()) !== 'apk') {
            if ($request->expectsJson()) return response()->json(['message' => 'Select an APK file.'], 422);
            return back()->with('danger', 'Select an APK file.');
        }
        try {
            $facts = $inspector->inspect($request->file('apk')->getRealPath());
        } catch (AppSecurityException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], $exception->status);
            }
            return back()->with('danger', $exception->getMessage());
        }
        if (SecurityRelease::query()->where('package_name', $facts['package_name'])->where('version_code', $facts['version_code'])->exists()) {
            if ($request->expectsJson()) return response()->json(['message' => 'That version code is already registered. APK releases are immutable.'], 409);
            return back()->with('danger', 'That version code is already registered. APK releases are immutable.');
        }
        $uuid = (string) Str::uuid();
        $path = $request->file('apk')->storeAs('app-security/releases', $uuid . '.apk', 'local');
        $actorId = Auth::guard('admin')->id();
        $release = SecurityRelease::query()->create($facts + [
            'uuid' => $uuid,
            'download_path' => $path,
            'release_notes' => $data['release_notes'] ?? null,
            'minimum_supported_version' => (int) $data['minimum_supported_version'],
            'release_status' => 'draft',
            'is_mandatory' => (bool) ($data['is_mandatory'] ?? false),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
        $this->audit('release.uploaded', $release, null, $release->only(['uuid', 'version_name', 'version_code', 'apk_sha256', 'signing_sha256', 'release_status']), $request);
        if ($request->expectsJson()) {
            return response()->json(['release' => $release->makeHidden(['download_path']), 'action' => 'uploaded'], 201);
        }
        return back()->with('success', 'APK verified and saved as a draft release.');
    }

    public function activate(Request $request, string $uuid)
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_releases');
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'release.activate');
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:512']]);
        $release = DB::transaction(function () use ($uuid, $data, $request) {
            $release = SecurityRelease::query()->where('uuid', $uuid)->lockForUpdate()->firstOrFail();
            abort_if(in_array($release->release_status, ['revoked', 'archived'], true), 409, 'This release cannot be activated.');
            $previous = SecurityRelease::query()->where('package_name', $release->package_name)->where('release_status', 'active')->lockForUpdate()->get();
            foreach ($previous as $old) $old->update(['release_status' => 'archived', 'updated_by' => Auth::guard('admin')->id()]);
            $oldState = ['status' => $release->release_status, 'active_releases_archived' => $previous->pluck('uuid')->all()];
            $release->update(['release_status' => 'active', 'released_at' => now(), 'updated_by' => Auth::guard('admin')->id()]);
            $this->audit('release.activated', $release, $oldState, ['status' => 'active', 'reason' => $data['reason']], $request);
            return $release->refresh();
        }, 3);
        app(\App\Modules\AppSecurity\Services\SecurityAlertService::class)->record(
            'release-activated:'.$release->uuid.':'.(string) $release->released_at,
            'release_published', 'medium', 'APK release published',
            "Release {$release->version_name} ({$release->version_code}) was activated.", null, null,
            ['version_name' => $release->version_name, 'version_code' => $release->version_code]
        );
        if ($request->expectsJson()) return response()->json(['release' => $release->makeHidden(['download_path']), 'action' => 'activated']);
        return back()->with('success', 'Release ' . $release->version_name . ' is now active.');
    }

    public function revoke(Request $request, string $uuid)
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_releases');
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'release.revoke');
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:512']]);
        $release = SecurityRelease::query()->where('uuid', $uuid)->firstOrFail();
        abort_if($release->release_status === 'revoked', 409, 'Release is already revoked.');
        $before = ['status' => $release->release_status];
        $release->update(['release_status' => 'revoked', 'updated_by' => Auth::guard('admin')->id()]);
        $this->audit('release.revoked', $release, $before, ['status' => 'revoked', 'reason' => $data['reason']], $request);
        app(\App\Modules\AppSecurity\Services\SecurityAlertService::class)->record(
            'release-revoked:'.$release->uuid.':'.(string) $release->updated_at,
            'release_revoked', 'critical', 'APK release revoked',
            "Release {$release->version_name} ({$release->version_code}) was revoked. Existing clients will fail release verification.", null, null,
            ['version_name' => $release->version_name, 'version_code' => $release->version_code]
        );
        if ($request->expectsJson()) return response()->json(['release' => $release->makeHidden(['download_path']), 'action' => 'revoked']);
        return back()->with('success', 'Release revoked. Existing clients will fail release verification.');
    }

    private function latestRelease(): ?SecurityRelease
    {
        return SecurityRelease::query()->where('package_name', config('app_security.package_name'))
            ->where('release_status', 'active')->orderByDesc('version_code')->first();
    }

    private function publicRelease(SecurityRelease $release): array
    {
        return [
            'name' => 'KNEAYERNG', 'package_name' => $release->package_name,
            'version_name' => $release->version_name, 'version_code' => $release->version_code,
            'apk_size' => $release->apk_size, 'apk_sha256' => $release->apk_sha256,
            'release_notes' => $release->release_notes, 'minimum_android_sdk' => $release->minimum_android_sdk,
            'target_android_sdk' => $release->target_android_sdk,
            'download_url' => route('app.release-download', ['version' => $release->version_name]),
            'privacy_policy_url' => url('/privacy'),
        ];
    }

    private function audit(string $action, SecurityRelease $release, ?array $previous, ?array $next, Request $request): void
    {
        SecurityAuditLog::query()->create([
            'admin_id' => Auth::guard('admin')->id(), 'actor_type' => 'admin', 'actor_id' => Auth::guard('admin')->id(),
            'action' => $action, 'target_type' => 'release', 'target_id' => (string) $release->id,
            'previous_state' => $previous, 'new_state' => $next,
            'reason' => $next['reason'] ?? null, 'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512), 'occurred_at' => now(),
        ]);
    }
}
