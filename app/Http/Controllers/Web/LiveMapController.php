<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveMapController extends Controller
{
    use CustomAuthorizesRequests;

    private string $view = 'admin.employees.';

    public function __construct(protected CompanyRepository $companyRepo)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('list_employee');

        $filterData = [
            'branch_id' => $request->branch_id ?? null,
            'department_id' => $request->department_id ?? null,
            'employee_id' => $request->employee_id ?? null,
            'date' => $request->date ?? (AppHelper::ifDateInBsEnabled() ? AppHelper::getCurrentDateInBS() : date('Y-m-d')),
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterData['branch_id'] = auth()->user()->branch_id;
        }

        $companyDetail = $this->companyRepo->getCompanyDetail(['id', 'name'], ['branches:id,name']);
        $bsEnabled = AppHelper::ifDateInBsEnabled();

        return view($this->view . 'live-map', compact('companyDetail', 'filterData', 'bsEnabled'));
    }

    public function locations(Request $request): JsonResponse
    {
        $this->authorize('list_employee');

        $filterData = [
            'branch_id' => $request->branch_id ?? null,
            'department_id' => $request->department_id ?? null,
            'employee_id' => $request->employee_id ?? null,
            'date' => $request->date ?? (AppHelper::ifDateInBsEnabled() ? AppHelper::getCurrentDateInBS() : date('Y-m-d')),
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterData['branch_id'] = auth()->user()->branch_id;
        }

        if (AppHelper::ifDateInBsEnabled()) {
            $filterData['date'] = AppHelper::dateInYmdFormatNepToEng($filterData['date']);
        }

        $locations = Attendance::with([
                'employee:id,name,email,phone,avatar,branch_id,department_id',
                'employee.branch:id,name',
                'employee.department:id,dept_name',
            ])
            ->whereNotNull('check_in_latitude')
            ->whereNotNull('check_in_longitude')
            ->whereDate('attendance_date', $filterData['date'])
            ->when(!empty($filterData['branch_id']), function ($query) use ($filterData) {
                $query->whereHas('employee', function ($employeeQuery) use ($filterData) {
                    $employeeQuery->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(!empty($filterData['department_id']), function ($query) use ($filterData) {
                $query->whereHas('employee', function ($employeeQuery) use ($filterData) {
                    $employeeQuery->where('department_id', $filterData['department_id']);
                });
            })
            ->when(!empty($filterData['employee_id']), function ($query) use ($filterData) {
                $query->where('employee_id', $filterData['employee_id']);
            })
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_at')
            ->get()
            ->map(fn (Attendance $attendance) => $this->formatLocation($attendance))
            ->values();

        return response()->json([
            'success' => true,
            'updated_at' => now()->toIso8601String(),
            'total' => $locations->count(),
            'locations' => $locations,
            'broadcast' => [
                'driver' => config('broadcasting.default'),
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => config('broadcasting.connections.pusher.options.host'),
                'port' => config('broadcasting.connections.pusher.options.port'),
                'scheme' => config('broadcasting.connections.pusher.options.scheme'),
            ],
        ]);
    }

    private function formatLocation(Attendance $attendance): array
    {
        $user = $attendance->employee;
        $attendanceDateTime = $attendance->attendance_date . ' ' . ($attendance->check_in_at ?? '00:00:00');

        return [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'employee_id' => $user->id,
            'name' => ucfirst($user?->name ?? 'User'),
            'email' => $user?->email,
            'phone' => $user?->phone,
            'avatar' => $user?->avatar
                ? asset(User::AVATAR_UPLOAD_PATH . $user->avatar)
                : asset('assets/images/img.png'),
            'branch' => $user?->branch?->name,
            'department' => $user?->department?->dept_name,
            'is_online' => (bool) (
                $user?->online_status == User::ONLINE ||
                (!empty($user?->uuid) && (int) $user?->logout_status === (int) User::LOGOUT_STATUS['approve'])
            ),
            'status_label' => (
                $user?->online_status == User::ONLINE ||
                (!empty($user?->uuid) && (int) $user?->logout_status === (int) User::LOGOUT_STATUS['approve'])
            ) ? 'Online' : 'Offline',
            'attendance_date' => $attendance->attendance_date,
            'check_in_at' => $attendance->check_in_at,
            'check_in_datetime' => $attendanceDateTime,
            'latitude' => (float) $attendance->check_in_latitude,
            'longitude' => (float) $attendance->check_in_longitude,
            'accuracy' => null,
            'battery_level' => null,
            'device_name' => null,
            'last_seen_at' => $attendance->updated_at?->toIso8601String(),
            'last_seen_human' => $attendance->check_in_at ? date('h:i A', strtotime($attendance->check_in_at)) : '-',
            'last_updated_at' => $attendance->updated_at?->toIso8601String(),
            'last_updated_human' => $attendance->updated_at?->diffForHumans() ?? '-',
            'has_location' => true,
            'map_url' => 'https://www.google.com/maps?q=' . $attendance->check_in_latitude . ',' . $attendance->check_in_longitude,
        ];
    }
}
