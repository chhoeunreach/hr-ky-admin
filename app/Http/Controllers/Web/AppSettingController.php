<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Repositories\AppSettingRepository;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Helpers\SMPush\SMPushNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AppSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.appSetting.';

    private AppSettingRepository $appSettingRepo;

    public function __construct(AppSettingRepository $appSettingRepo)
    {
        $this->appSettingRepo = $appSettingRepo;
    }

    public function index()
    {
        $this->authorize('app_setting');
        try{
            $select=['id','name','slug','value','status'];
            $appSettings = $this->appSettingRepo->getAllAppSettings($select);
            $appVersionSetting = AppHelper::getAppVersionSettings();
            return view($this->view.'index', compact('appSettings', 'appVersionSetting'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('app_setting');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'),400);
            }
            DB::beginTransaction();
                $this->appSettingRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateAndroidApk(Request $request): RedirectResponse
    {
        $this->authorize('app_setting');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $request->validate([
                'android_apk' => ['required', 'file', 'max:512000'],
            ]);

            $file = $request->file('android_apk');
            if (strtolower($file->getClientOriginalExtension()) !== 'apk') {
                return redirect()->back()->with('danger', __('message.android_apk_invalid'));
            }

            $setting = AppSetting::firstOrCreate(
                ['slug' => 'android-apk'],
                [
                    'name' => 'Android APK',
                    'status' => 1,
                ]
            );

            $directory = public_path('downloads');
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $fileName = 'android-app-' . now()->format('YmdHis') . '-' . Str::random(8) . '.apk';
            $file->move($directory, $fileName);
            $path = 'downloads/' . $fileName;

            if ($setting->value && File::exists(public_path($setting->value))) {
                File::delete(public_path($setting->value));
            }

            $setting->update([
                'value' => $path,
                'status' => 1,
            ]);

            return redirect()->back()->with('success', __('message.android_apk_updated'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeTheme()
    {
        try{
            $slug = 'dark-theme';
           $themeDetail = $this->appSettingRepo->findAppSettingDetailBySlug($slug);
           if(!$themeDetail){
               throw new \Exception(__('message.theme_not_found'),404);
           }
           $status = $this->appSettingRepo->toggleTheme($themeDetail);
           if($status){
               Cache::forget('theme');
               $theme = $themeDetail->status ? 'dark' : 'light' ;
               Cache::forever('theme', $theme);
           }
           return AppHelper::sendSuccessResponse(__('message.theme_changed'));
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }


    public function updateAppVersion(Request $request): RedirectResponse
    {
        $this->authorize('app_setting');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $validated = $request->validate([
                'target_version' => ['required', 'string', 'max:50'],
                'min_version' => ['required', 'string', 'max:50'],
                'force_update' => ['nullable', 'boolean'],
                'enabled' => ['nullable', 'boolean'],
                'alert_title' => ['required', 'string', 'max:150'],
                'alert_message' => ['required', 'string', 'max:1000'],
                'android_url' => ['nullable', 'string', 'max:255'],
                'ios_url' => ['nullable', 'string', 'max:255'],
            ]);

            $isEnabled = $request->boolean('enabled');
            $forceUpdate = $request->boolean('force_update');

            $payload = [
                'enabled' => $isEnabled,
                'target_version' => trim($validated['target_version']),
                'min_version' => trim($validated['min_version']),
                'force_update' => $forceUpdate,
                'alert_title' => trim($validated['alert_title']),
                'alert_message' => trim($validated['alert_message']),
                'android_url' => trim((string)($validated['android_url'] ?? '')),
                'ios_url' => trim((string)($validated['ios_url'] ?? '')),
            ];

            $setting = AppSetting::firstOrCreate(
                ['slug' => 'app-version-update'],
                [
                    'name' => 'App Version Control',
                    'status' => 1,
                ]
            );

            $setting->update([
                'value' => json_encode($payload),
                'status' => $isEnabled ? 1 : 0,
            ]);

            return redirect()->back()->with('success', __('index.app_version_updated'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function sendAppUpdateAlert(Request $request): RedirectResponse
    {
        $this->authorize('app_setting');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $validated = $request->validate([
                'alert_title' => ['required', 'string', 'max:150'],
                'alert_message' => ['required', 'string', 'max:1000'],
                'target_audience' => ['required', 'in:all,outdated'],
            ]);

            $versionSettings = AppHelper::getAppVersionSettings();
            $targetVersion = $versionSettings['target_version'] ?? '13.00';
            $downloadUrl = $versionSettings['android_url'] ?: ($versionSettings['ios_url'] ?: config('app.url'));
            $forceUpdate = !empty($versionSettings['force_update']);

            $title = trim($validated['alert_title']);
            $message = str_replace(
                [':target_version', ':min_version'],
                [$targetVersion, $versionSettings['min_version'] ?? $targetVersion],
                trim($validated['alert_message'])
            );

            // Determine recipient user IDs
            $usersQuery = User::query()
                ->where('status', 'verified')
                ->where('is_active', 1)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '');

            if ($validated['target_audience'] === 'outdated') {
                $upToDateUserIds = DB::table('user_locations')
                    ->whereNotNull('app_version')
                    ->where('app_version', '!=', '')
                    ->get(['user_id', 'app_version'])
                    ->filter(function ($loc) use ($targetVersion) {
                        $norm = AppHelper::normalizeAppVersion($loc->app_version);
                        return $norm !== null && version_compare($norm, $targetVersion, '>=');
                    })
                    ->pluck('user_id')
                    ->unique()
                    ->toArray();

                if (!empty($upToDateUserIds)) {
                    $usersQuery->whereNotIn('id', $upToDateUserIds);
                }
            }

            $recipientUsers = $usersQuery->get(['id', 'fcm_token']);
            $recipientCount = $recipientUsers->count();

            if ($recipientCount === 0) {
                return redirect()->back()->with('warning', __('index.no_recipients_found'));
            }

            $recipientUserIds = $recipientUsers->pluck('id')->toArray();
            $fcmTokens = $recipientUsers->pluck('fcm_token', 'id')->filter()->toArray();

            // 1. Create in-app Notification record
            try {
                $notification = Notification::create([
                    'title' => $title,
                    'description' => $message,
                    'type' => 'general',
                    'company_id' => AppHelper::getAuthUserCompanyId(),
                    'is_active' => 1,
                ]);

                if ($notification) {
                    $userNotifications = [];
                    foreach ($recipientUserIds as $uId) {
                        $userNotifications[] = [
                            'user_id' => $uId,
                            'is_seen' => 0,
                        ];
                    }
                    $notification->notifiedUsers()->createMany($userNotifications);
                }
            } catch (\Throwable $ne) {
                Log::warning('Failed to store in-app notification for app update: ' . $ne->getMessage());
            }

            // 2. Send FCM push notification
            if (!empty($fcmTokens)) {
                try {
                    SMPushNotification::smSend(
                        title: $title,
                        message: $message,
                        data: [
                            'type' => 'app_update',
                            'target_version' => $targetVersion,
                            'download_url' => (string)$downloadUrl,
                            'force_update' => $forceUpdate ? '1' : '0',
                        ],
                        recipients: $fcmTokens
                    );
                } catch (\Throwable $fe) {
                    Log::warning('FCM send failed for app update alert: ' . $fe->getMessage());
                }
            }

            return redirect()->back()->with('success', __('index.alert_sent_successfully', ['count' => $recipientCount]));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
