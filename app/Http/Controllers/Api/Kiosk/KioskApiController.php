<?php

namespace App\Http\Controllers\Api\Kiosk;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FaceProfile;
use App\Models\KioskAttendanceEvent;
use App\Models\KioskDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class KioskApiController extends Controller
{
    public const MODEL_VERSION = 'mobile_facenet_112_192_v1';
    public const EMBEDDING_DIMENSION = 192;
    public const MIN_MATCH_SCORE = 0.62;

    /**
     * Exchange the QR credential plus PIN for a long-lived runtime token.
     *
     * @throws ValidationException
     */
    public function provision(Request $request): JsonResponse
    {
        $device = $this->device($request);
        if ($device->provisioned_at) {
            return $this->error('This provisioning QR has already been used.', 409);
        }

        $this->verifyAdminPinValue($request, $device);
        $runtimeToken = 'kiosk_' . Str::random(72);
        $device->update([
            'token_prefix' => substr($runtimeToken, 0, 12),
            'token_hash' => hash('sha256', $runtimeToken),
            'provisioned_at' => now(),
            'last_seen_at' => now(),
        ]);

        return $this->success('Kiosk provisioned successfully.', [
            'device_token' => $runtimeToken,
            'device_id' => $device->id,
            'device_name' => $device->name,
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $device = $this->device($request);

        $employees = User::query()
            ->withoutGlobalScopes()
            ->where('company_id', $device->company_id)
            ->where('branch_id', $device->branch_id)
            ->where('is_active', true)
            ->where('status', 'verified')
            ->whereHas('faceProfile', fn ($query) => $query
                ->where('is_active', true)
                ->where('model_version', self::MODEL_VERSION)
                ->where('embedding_dimension', self::EMBEDDING_DIMENSION))
            ->with([
                'department:id,dept_name',
                'post:id,post_name',
                'faceProfile' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('model_version', self::MODEL_VERSION)
                    ->where('embedding_dimension', self::EMBEDDING_DIMENSION),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (User $employee) {
                $profile = $employee->faceProfile;

                return [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'name' => $employee->name,
                    'department' => $employee->department?->dept_name,
                    'position' => $employee->post?->post_name,
                    'avatar_url' => $employee->avatar
                        ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar)
                        : null,
                    'embedding' => $profile->embedding,
                    'embedding_dimension' => $profile->embedding_dimension,
                    'model_version' => $profile->model_version,
                    'updated_at' => $profile->updated_at?->toIso8601String(),
                ];
            })
            ->values();

        return $this->success('Kiosk data synchronized.', [
            'server_time' => now()->toIso8601String(),
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'company_id' => $device->company_id,
                'company_name' => $device->company?->name,
                'branch_id' => $device->branch_id,
                'branch_name' => $device->branch?->name,
            ],
            'recognition' => [
                'model_version' => self::MODEL_VERSION,
                'embedding_dimension' => self::EMBEDDING_DIMENSION,
                'minimum_match_score' => self::MIN_MATCH_SCORE,
            ],
            'employees' => $employees,
        ]);
    }

    public function employees(Request $request): JsonResponse
    {
        $device = $this->device($request);
        $search = trim((string) $request->query('search', ''));

        $employees = User::query()
            ->withoutGlobalScopes()
            ->where('company_id', $device->company_id)
            ->where('branch_id', $device->branch_id)
            ->where('is_active', true)
            ->where('status', 'verified')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->with([
                'department:id,dept_name',
                'post:id,post_name',
                'faceProfile' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('model_version', self::MODEL_VERSION)
                    ->where('embedding_dimension', self::EMBEDDING_DIMENSION)
                    ->select([
                        'id',
                        'user_id',
                        'model_version',
                        'quality_score',
                        'enrolled_at',
                        'is_active',
                    ]),
            ])
            ->orderBy('name')
            ->limit(200)
            ->get()
            ->map(fn (User $employee) => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->name,
                'department' => $employee->department?->dept_name,
                'position' => $employee->post?->post_name,
                'has_face_profile' => (bool) $employee->faceProfile?->is_active,
                'face_enrolled_at' => $employee->faceProfile?->enrolled_at?->toIso8601String(),
                'face_quality_score' => $employee->faceProfile?->quality_score,
            ])
            ->values();

        return $this->success('Employees loaded.', ['employees' => $employees]);
    }

    /**
     * @throws ValidationException
     */
    public function verifyAdminPin(Request $request): JsonResponse
    {
        $this->verifyAdminPinValue($request, $this->device($request));

        return $this->success('Kiosk administrator PIN verified.');
    }

    /**
     * @throws ValidationException
     */
    public function storeFaceProfile(Request $request, int $user): JsonResponse
    {
        $device = $this->device($request);
        $this->verifyAdminPinValue($request, $device);

        $validator = Validator::make($request->all(), [
            'embedding' => ['required', 'array', 'size:' . self::EMBEDDING_DIMENSION],
            'embedding.*' => ['required', 'numeric', 'between:-1,1'],
            'model_version' => ['required', 'in:' . self::MODEL_VERSION],
            'quality_score' => ['nullable', 'numeric', 'between:0,1'],
        ]);
        $validated = $validator->validate();

        $employee = $this->findEmployee($device, $user);
        $embedding = array_map('floatval', $validated['embedding']);
        $magnitude = sqrt(array_sum(array_map(fn (float $value) => $value * $value, $embedding)));

        if ($magnitude < 0.5 || $magnitude > 1.5) {
            throw ValidationException::withMessages([
                'embedding' => ['The embedding must be L2-normalized.'],
            ]);
        }

        $embedding = array_map(fn (float $value) => $value / $magnitude, $embedding);

        $profile = FaceProfile::query()->updateOrCreate(
            ['user_id' => $employee->id],
            [
                'company_id' => $device->company_id,
                'branch_id' => $device->branch_id,
                'embedding' => $embedding,
                'embedding_dimension' => self::EMBEDDING_DIMENSION,
                'model_version' => self::MODEL_VERSION,
                'quality_score' => $validated['quality_score'] ?? null,
                'is_active' => true,
                'enrolled_by_device_id' => $device->id,
                'enrolled_at' => now(),
            ],
        );

        return $this->success('Face profile saved.', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'enrolled_at' => $profile->enrolled_at->toIso8601String(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function destroyFaceProfile(Request $request, int $user): JsonResponse
    {
        $device = $this->device($request);
        $this->verifyAdminPinValue($request, $device);
        $employee = $this->findEmployee($device, $user);

        FaceProfile::query()->where('user_id', $employee->id)->delete();

        return $this->success('Face profile removed.');
    }

    public function attendance(Request $request): JsonResponse
    {
        $device = $this->device($request);
        $event = null;
        $validator = Validator::make($request->all(), [
            'event_uuid' => ['required', 'uuid'],
            'employee_id' => ['required', 'integer'],
            'captured_at' => ['required', 'date'],
            'match_score' => ['required', 'numeric', 'between:' . self::MIN_MATCH_SCORE . ',1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid attendance event.', 422, $validator->errors()->toArray());
        }
        $validated = $validator->validated();

        $existing = KioskAttendanceEvent::query()
            ->where('event_uuid', $validated['event_uuid'])
            ->first();
        if ($existing) {
            if ($existing->kiosk_device_id !== $device->id) {
                return $this->error('Event UUID is already owned by another device.', 409);
            }

            if (in_array($existing->status, ['processed', 'rejected'], true) && $existing->response_payload) {
                return response()->json(
                    $existing->response_payload,
                    $existing->status === 'processed' ? 200 : 422,
                );
            }

            if ($existing->updated_at?->gt(now()->subMinutes(2))) {
                return $this->error('This attendance event is still processing.', 409);
            }

            // Recover a stale event left behind by a terminated PHP worker.
            // The UUID remains idempotent once processing succeeds or is rejected.
            $existing->delete();
        }

        try {
            $capturedAt = Carbon::parse($validated['captured_at']);
            if ($capturedAt->isFuture() && $capturedAt->diffInMinutes(now()) > 5) {
                return $this->error('Attendance time is in the future.', 422);
            }
            if (!$capturedAt->isSameDay(now())) {
                return $this->error('Offline attendance must be synchronized before the day ends.', 422);
            }

            $employee = $this->findEmployee($device, (int) $validated['employee_id']);
            $profileExists = FaceProfile::query()
                ->where('user_id', $employee->id)
                ->where('is_active', true)
                ->exists();
            if (!$profileExists) {
                return $this->error('Employee has no active face profile.', 422);
            }

            $latestAttendance = Attendance::query()
                ->where('user_id', $employee->id)
                ->whereDate('attendance_date', $capturedAt->toDateString())
                ->latest('created_at')
                ->first();
            $action = $latestAttendance && !$latestAttendance->check_out_at && !$latestAttendance->night_checkout
                ? 'check_out'
                : 'check_in';

            $event = KioskAttendanceEvent::query()->create([
                'event_uuid' => $validated['event_uuid'],
                'kiosk_device_id' => $device->id,
                'user_id' => $employee->id,
                'company_id' => $device->company_id,
                'branch_id' => $device->branch_id,
                'captured_at' => $capturedAt,
                'match_score' => $validated['match_score'],
                'action' => $action,
                'status' => 'pending',
            ]);

            $previousUser = Auth::user();
            $previousTestNow = Carbon::getTestNow();
            Auth::shouldUse('web');
            Auth::setUser($employee);
            Carbon::setTestNow($capturedAt);

            try {
                $internalRequest = Request::create('/api/employees/attendance', 'POST', [
                    'attendance_type' => EmployeeAttendanceTypeEnum::face->value,
                    'latitude' => $validated['latitude'] ?? $device->branch->branch_location_latitude ?? 0,
                    'longitude' => $validated['longitude'] ?? $device->branch->branch_location_longitude ?? 0,
                    'note' => 'Face kiosk: ' . $device->name,
                ]);
                $attendanceResponse = app(AttendanceApiController::class)
                    ->employeeAttendance($internalRequest);
                $payload = $attendanceResponse->getData(true);
            } finally {
                Carbon::setTestNow($previousTestNow);
                Auth::forgetUser();
                if ($previousUser) {
                    Auth::setUser($previousUser);
                }
            }

            $processed = (bool) ($payload['status'] ?? false);
            $attendance = Attendance::query()
                ->where('user_id', $employee->id)
                ->whereDate('attendance_date', $capturedAt->toDateString())
                ->latest('created_at')
                ->first();
            $responsePayload = [
                'status' => $processed,
                'message' => $payload['message'] ?? ($processed ? 'Attendance recorded.' : 'Attendance rejected.'),
                'status_code' => $processed ? 200 : 422,
                'data' => [
                    'event_uuid' => $event->event_uuid,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'action' => $action,
                    'captured_at' => $capturedAt->toIso8601String(),
                    'match_score' => (float) $event->match_score,
                    'attendance' => $payload['data'] ?? null,
                ],
            ];

            $event->update([
                'attendance_id' => $attendance?->id,
                'status' => $processed ? 'processed' : 'rejected',
                'message' => $responsePayload['message'],
                'response_payload' => $responsePayload,
            ]);

            return response()->json($responsePayload, $processed ? 200 : 422);
        } catch (ValidationException $exception) {
            return $this->error('Employee is not available to this kiosk.', 404, $exception->errors());
        } catch (Throwable $exception) {
            // Unexpected infrastructure failures must remain retryable with the
            // same client UUID. Business-rule rejections are persisted above.
            $event?->delete();
            report($exception);
            return $this->error('Unable to record attendance.', 500);
        }
    }

    public function recentAttendance(Request $request): JsonResponse
    {
        $device = $this->device($request);

        $events = KioskAttendanceEvent::query()
            ->where('kiosk_device_id', $device->id)
            ->with('employee:id,name,employee_code')
            ->latest('captured_at')
            ->limit(100)
            ->get()
            ->map(fn (KioskAttendanceEvent $event) => [
                'event_uuid' => $event->event_uuid,
                'employee_id' => $event->user_id,
                'employee_name' => $event->employee?->name,
                'employee_code' => $event->employee?->employee_code,
                'action' => $event->action,
                'status' => $event->status,
                'message' => $event->message,
                'match_score' => $event->match_score,
                'captured_at' => $event->captured_at?->toIso8601String(),
            ]);

        return $this->success('Recent kiosk attendance loaded.', ['events' => $events]);
    }

    private function device(Request $request): KioskDevice
    {
        return $request->attributes->get('kioskDevice');
    }

    /**
     * @throws ValidationException
     */
    private function verifyAdminPinValue(Request $request, KioskDevice $device): void
    {
        $pin = (string) ($request->header('X-Kiosk-Admin-Pin') ?: $request->input('admin_pin', ''));
        if (!Hash::check($pin, $device->admin_pin_hash)) {
            throw ValidationException::withMessages(['admin_pin' => ['Incorrect kiosk administrator PIN.']]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function findEmployee(KioskDevice $device, int $userId): User
    {
        $employee = User::query()
            ->withoutGlobalScopes()
            ->whereKey($userId)
            ->where('company_id', $device->company_id)
            ->where('branch_id', $device->branch_id)
            ->where('is_active', true)
            ->where('status', 'verified')
            ->first();

        if (!$employee) {
            throw ValidationException::withMessages([
                'employee_id' => ['Employee is not assigned to this kiosk branch.'],
            ]);
        }

        return $employee;
    }

    private function success(string $message, mixed $data = null): JsonResponse
    {
        $payload = ['status' => true, 'message' => $message, 'status_code' => 200];
        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload);
    }

    private function error(string $message, int $status, mixed $data = null): JsonResponse
    {
        $payload = ['status' => false, 'message' => $message, 'status_code' => $status];
        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }
}
