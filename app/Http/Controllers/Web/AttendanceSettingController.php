<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\AppSettingRepository;
use App\Repositories\AttendanceSettingRepository;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\PaymentCurrencyRepository;
use App\Requests\GeneralSetting\GeneralSettingRequest;
use App\Services\FiscalYear\FiscalYearService;
use App\Traits\CustomAuthorizesRequests;
use App\Models\AttendanceSetting;
use Database\Seeders\EmployeeCodeSeeder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AttendanceSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.attendanceSetting.';
    private const MONTHLY_CONTROL_SETTINGS = [
        'monthly_attendance_bonus_amount' => [
            'name' => 'Monthly Attendance Bonus Amount',
            'value' => 20,
            'status' => 1,
        ],
        'monthly_attendance_require_check_in' => [
            'name' => 'Require Check In',
            'value' => null,
            'status' => 1,
        ],
        'monthly_attendance_require_check_out' => [
            'name' => 'Require Check Out',
            'value' => null,
            'status' => 1,
        ],
        'monthly_attendance_require_no_late_check_in' => [
            'name' => 'Control Late Check In',
            'value' => null,
            'status' => 1,
        ],
        'monthly_attendance_require_no_early_check_out' => [
            'name' => 'Control Check Out Before Time Out',
            'value' => null,
            'status' => 1,
        ],
        'monthly_attendance_require_no_early_check_in' => [
            'name' => 'Control Check In Before Time Start',
            'value' => null,
            'status' => 1,
        ],
    ];


    public function __construct(protected AttendanceSettingRepository $attendanceSettingRepository
    )
    {}

    public function index()
    {
        try {
            $attendanceSettings = AttendanceSetting::query()
                ->whereIn('slug', ['attendance_note', 'attendance_limit', 'attendance_method'])
                ->get();

            return view($this->view . 'index', compact('attendanceSettings'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(Request $request)
    {
        $this->authorize('attendance_setting');

        try {
            $rules = [
                'attendance_method' => 'sometimes|array',
                'attendance_method.*' => 'sometimes|string|in:default,biometric,nfc,qr',
                'attendance_limit' => 'sometimes|integer|min:1',
                'attendance_note' => 'sometimes|in:0,1',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $values = $request->input('attendance_method', []);
            if (in_array('default', $values) && in_array('biometric', $values)) {
                return response()->json([
                    'success' => false,
                    'errors'  => [
                        'attendance_method' => ['Cannot select both "default" and "biometric".']
                    ]
                ], 422);
            }

            DB::beginTransaction();

            $attendanceSettings = $this->attendanceSettingRepository->getAll();

            foreach ($attendanceSettings as $attendanceSetting) {
                $slug = $attendanceSetting->slug;
                $data = [];

                if ($slug === 'attendance_method' && $request->has('attendance_method')) {
                    $data['values'] = $values;
                } elseif ($slug === 'attendance_limit' && $request->has('attendance_limit')) {
                    $data['value'] = $request->input('attendance_limit');
                } elseif ($slug === 'attendance_note' && $request->has('attendance_note')) {
                    $data['status'] = $request->input('attendance_note');
                }

                if (!empty($data)) {
                    $this->attendanceSettingRepository->update($attendanceSetting, $data);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('message.attendance_setting_update')
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    public function toggleStatus(Request $request, $id)
    {
        $this->authorize('attendance_setting');

        try {
            DB::beginTransaction();
            $this->attendanceSettingRepository->toggleStatus($id);
            DB::commit();

            $message = __('message.status_changed');
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $exception) {
            DB::rollBack();

            if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    public function monthlyControls()
    {
        $this->authorize('attendance_setting');

        try {
            $settings = $this->monthlyControlSettings();

            return view($this->view . 'monthly-controls', compact('settings'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateMonthlyControls(Request $request)
    {
        $this->authorize('attendance_setting');

        $validated = $request->validate([
            'bonus_amount' => ['required', 'numeric', 'min:0', 'max:9999'],
            'require_check_in' => ['nullable', 'boolean'],
            'require_check_out' => ['nullable', 'boolean'],
            'require_no_late_check_in' => ['nullable', 'boolean'],
            'require_no_early_check_out' => ['nullable', 'boolean'],
            'require_no_early_check_in' => ['nullable', 'boolean'],
        ]);

        try {
            DB::beginTransaction();

            $this->monthlyControlSettings();

            AttendanceSetting::query()
                ->where('slug', 'monthly_attendance_bonus_amount')
                ->update(['value' => (int) round((float) $validated['bonus_amount'])]);

            $ruleSlugByInput = [
                'require_check_in' => 'monthly_attendance_require_check_in',
                'require_check_out' => 'monthly_attendance_require_check_out',
                'require_no_late_check_in' => 'monthly_attendance_require_no_late_check_in',
                'require_no_early_check_out' => 'monthly_attendance_require_no_early_check_out',
                'require_no_early_check_in' => 'monthly_attendance_require_no_early_check_in',
            ];

            foreach ($ruleSlugByInput as $input => $slug) {
                AttendanceSetting::query()
                    ->where('slug', $slug)
                    ->update(['status' => (bool) ($validated[$input] ?? false)]);
            }

            DB::commit();

            return redirect()
                ->route('admin.attendance-monthly.controls')
                ->with('success', __('message.attendance_setting_update'));
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('danger', $exception->getMessage());
        }
    }

    private function monthlyControlSettings()
    {
        foreach (self::MONTHLY_CONTROL_SETTINGS as $slug => $setting) {
            AttendanceSetting::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $setting['name'],
                    'value' => $setting['value'],
                    'values' => null,
                    'status' => $setting['status'],
                ]
            );
        }

        return AttendanceSetting::query()
            ->whereIn('slug', array_keys(self::MONTHLY_CONTROL_SETTINGS))
            ->get()
            ->keyBy('slug');
    }



}
