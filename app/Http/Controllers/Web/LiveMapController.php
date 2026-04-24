<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLocation;
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
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterData['branch_id'] = auth()->user()->branch_id;
        }

        $companyDetail = $this->companyRepo->getCompanyDetail(['id', 'name'], ['branches:id,name']);

        return view($this->view . 'live-map', compact('companyDetail', 'filterData'));
    }

    public function locations(Request $request): JsonResponse
    {
        $this->authorize('list_employee');

        $filterData = [
            'branch_id' => $request->branch_id ?? null,
            'department_id' => $request->department_id ?? null,
            'employee_id' => $request->employee_id ?? null,
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterData['branch_id'] = auth()->user()->branch_id;
        }

        $staff = User::with(['branch:id,name', 'department:id,dept_name'])
            ->select(['id', 'name', 'email', 'phone', 'avatar', 'branch_id', 'department_id'])
            ->where('is_active', 1)
            ->where('status', 'verified')
            ->when(!empty($filterData['branch_id']), function ($query) use ($filterData) {
                $query->where('branch_id', $filterData['branch_id']);
            })
            ->when(!empty($filterData['department_id']), function ($query) use ($filterData) {
                $query->where('department_id', $filterData['department_id']);
            })
            ->when(!empty($filterData['employee_id']), function ($query) use ($filterData) {
                $query->where('id', $filterData['employee_id']);
            })
            ->orderBy('name')
            ->get()
            ->values();

        $latestLocationIds = EmployeeLocation::query()
            ->selectRaw('MAX(id)')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('employee_id', $staff->pluck('id'))
            ->groupBy('employee_id');

        $latestLocations = EmployeeLocation::query()
            ->whereIn('id', $latestLocationIds)
            ->get()
            ->keyBy('employee_id');

        $locations = $staff
            ->map(fn (User $employee) => $this->formatLocation($employee, $latestLocations->get($employee->id)))
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

    private function formatLocation(User $user, ?EmployeeLocation $location): array
    {
        $lastSeenAt = $location?->updated_at ?? $location?->created_at;

        return [
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
            'latitude' => $location ? (float) $location->latitude : null,
            'longitude' => $location ? (float) $location->longitude : null,
            'accuracy' => null,
            'battery_level' => null,
            'device_name' => null,
            'last_seen_at' => $lastSeenAt?->toIso8601String(),
            'last_seen_human' => $lastSeenAt?->diffForHumans() ?? 'Waiting for GPS',
            'last_updated_at' => $location?->updated_at?->toIso8601String(),
            'last_updated_human' => $location?->updated_at?->diffForHumans() ?? 'Waiting for GPS',
            'has_location' => (bool) $location,
            'map_url' => $location
                ? 'https://www.google.com/maps?q=' . $location->latitude . ',' . $location->longitude
                : null,
        ];
    }
}
