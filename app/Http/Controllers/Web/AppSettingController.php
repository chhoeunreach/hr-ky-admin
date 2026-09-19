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

            $payload = [
                'target_version' => trim($validated['target_version']),
                'min_version' => trim($validated['min_version']),
                'force_update' => (bool) $request->input('force_update', false),
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
                'status' => $request->has('enabled') ? (int)$request->input('enabled') : 1,
            ]);

            return redirect()->back()->with('success', __('index.app_version_updated'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
