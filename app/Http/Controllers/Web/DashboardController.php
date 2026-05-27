<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\DashboardRepository;
use App\Services\Client\ClientService;
use App\Services\Project\ProjectService;
use App\Services\Task\TaskService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private DashboardRepository $dashboardRepo;
    private ClientService $clientService;
    private TaskService $taskService;
    private ProjectService $projectService;

    public function __construct(
        DashboardRepository $dashboardRepo,
        ClientService $clientService,
        TaskService $taskService,
        ProjectService $projectService
    ) {
        $this->dashboardRepo = $dashboardRepo;
        $this->clientService = $clientService;
        $this->projectService = $projectService;
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        try {
            $appTimeSetting = AppHelper::check24HoursTimeAppSetting();

            $projectSelect = ['id', 'name', 'start_date', 'deadline', 'status', 'priority'];
            $withProject = [
                'projectLeaders.user:id,name,avatar',
                'tasks:id,project_id',
                'completedTask:id,project_id',
            ];

            $companyId = AppHelper::getAuthUserCompanyId();

            if (!$companyId) {
                throw new Exception(__('message.company_not_found'));
            }

            $date = AppHelper::yearDetailToFilterData();
            $dashboardDetail = $this->dashboardRepo->getCompanyDashboardDetail($companyId, $date);
            $branchDashboardSummaries = $this->dashboardRepo->getBranchDashboardSummaries($companyId);
            $departmentDashboardSummaries = $this->transformDepartmentDashboardSummaries(
                $this->dashboardRepo->getDepartmentDashboardSummaries($companyId)
            );

            $topClients = $this->clientService->getTopClientsOfCompany();
            $taskPieChartData = $this->taskService->getTaskDataForPieChart();
            $projectCardDetail = $this->projectService->getProjectCardData();
            $recentProjects = $this->projectService->getRecentProjectListsForDashboard($projectSelect, $withProject);
            $multipleAttendance = AppHelper::getAttendanceLimit();

            return view('admin.dashboard', compact(
                'dashboardDetail',
                'branchDashboardSummaries',
                'departmentDashboardSummaries',
                'topClients',
                'taskPieChartData',
                'projectCardDetail',
                'recentProjects',
                'appTimeSetting',
                'multipleAttendance'
            ));
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function summaryDetail(Request $request): JsonResponse
    {
        $companyId = AppHelper::getAuthUserCompanyId();

        if (!$companyId) {
            return response()->json(['message' => __('message.company_not_found')], 404);
        }

        $validated = $request->validate([
            'scope' => ['required', 'in:branch,department'],
            'metric' => ['required', 'in:total_all_employee,inactive_employee,active_employee,active_employee_checkin,active_employee_not_yet_checkin,active_employee_checkout,active_employee_not_yet_checkout,active_employee_dayoff,active_employee_leave,active_employee_pending_request'],
            'entity_ids' => ['required', 'array', 'min:1'],
            'entity_ids.*' => ['integer'],
            'entity_name' => ['nullable', 'string'],
        ]);

        $rows = $this->dashboardRepo->getSummaryDetailRows(
            $companyId,
            $validated['scope'],
            $validated['entity_ids'],
            $validated['metric']
        );

        $rows = $rows->map(function ($row) {
            $row['chat_url'] = route('admin.employee-chat', ['employee_id' => $row['id']]);
            $row['leave_url'] = route('admin.leave-request.add', ['requested_by' => $row['id']]);

            return $row;
        })->values();

        return response()->json([
            'title' => trim(($validated['entity_name'] ?? ucfirst($validated['scope'])) . ' - ' . $this->getSummaryMetricLabel($validated['metric'])),
            'rows' => $rows,
        ]);
    }

    public function showQR()
    {
        $url = $this->generateUrl();

        return view('admin.print_qr', compact('url'));
    }

    public function employeeChat()
    {
        $chatUrl = config('app.mobile_app_url');
        $staffList = User::query()
            ->select(['id', 'name', 'username', 'avatar', 'phone', 'department_id', 'branch_id', 'online_status'])
            ->with([
                'department:id,dept_name',
                'branch:id,name',
            ])
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('admin.employee-chat', compact('chatUrl', 'staffList'));
    }

    private function generateUrl(): string
    {
        $url = config('app.mobile_app_url') ?: config('app.url');
        $url = rtrim($url, '/') . '/';

        return base64_encode($url);
    }

    private function transformDepartmentDashboardSummaries($departmentDashboardSummaries)
    {
        return $departmentDashboardSummaries
            ->groupBy(function ($departmentSummary) {
                return $this->normalizeDepartmentSummaryName(trim((string) $departmentSummary->dept_name));
            })
            ->map(function ($groupedDepartmentSummaries, $departmentName) {
                return (object) [
                    'dept_name' => $departmentName,
                    'department_ids' => $groupedDepartmentSummaries->pluck('id')->filter()->unique()->values()->all(),
                    'total_all_employee' => $groupedDepartmentSummaries->sum('total_all_employee'),
                    'inactive_employee' => $groupedDepartmentSummaries->sum('inactive_employee'),
                    'active_employee' => $groupedDepartmentSummaries->sum('active_employee'),
                    'active_employee_checkin' => $groupedDepartmentSummaries->sum('active_employee_checkin'),
                    'active_employee_not_yet_checkin' => $groupedDepartmentSummaries->sum('active_employee_not_yet_checkin'),
                    'active_employee_checkout' => $groupedDepartmentSummaries->sum('active_employee_checkout'),
                    'active_employee_not_yet_checkout' => $groupedDepartmentSummaries->sum('active_employee_not_yet_checkout'),
                    'active_employee_dayoff' => $groupedDepartmentSummaries->sum('active_employee_dayoff'),
                    'active_employee_leave' => $groupedDepartmentSummaries->sum('active_employee_leave'),
                    'active_employee_pending_request' => $groupedDepartmentSummaries->sum('active_employee_pending_request'),
                ];
            })
            ->filter(function ($departmentSummary) {
                return $departmentSummary->total_all_employee > 0
                    || $departmentSummary->inactive_employee > 0
                    || $departmentSummary->active_employee > 0
                    || $departmentSummary->active_employee_checkin > 0
                    || $departmentSummary->active_employee_not_yet_checkin > 0
                    || $departmentSummary->active_employee_checkout > 0
                    || $departmentSummary->active_employee_not_yet_checkout > 0
                    || $departmentSummary->active_employee_dayoff > 0
                    || $departmentSummary->active_employee_leave > 0
                    || $departmentSummary->active_employee_pending_request > 0;
            })
            ->sortBy('dept_name')
            ->values();
    }

    private function normalizeDepartmentSummaryName(string $departmentName): string
    {
        if (strcasecmp($departmentName, 'Management') === 0) {
            return 'Management';
        }

        if (str_starts_with($departmentName, 'ការហ្វេ')) {
            return 'ការហ្វេរ';
        }

        if (
            str_starts_with($departmentName, 'គណនេយ្យករ')
            || $departmentName === 'គិតលុយ'
            || str_starts_with($departmentName, 'អ្នកគិតលុយ')
        ) {
            return 'គិតលុយ';
        }

        if (str_starts_with($departmentName, 'ចុងភៅ') || $departmentName === 'ផ្នែកមេផ្ទះ') {
            return 'ចុងភៅ';
        }

        if ($departmentName === 'ជំនួយការរដ្ឋបាល' || str_starts_with($departmentName, 'ធនធានមនុស្ស')) {
            return 'ជំនួយការរដ្ឋបាល';
        }

        if (str_starts_with($departmentName, 'អ្នកលក់អនឡាញ')) {
            return 'ផ្នែកអនឡាញ';
        }

        if (str_starts_with($departmentName, 'អ្នកលក់')) {
            return 'អ្នកលក់';
        }

        if (str_starts_with($departmentName, 'មេឌៀ')) {
            return 'មេឌៀ';
        }

        if (str_starts_with($departmentName, 'សន្តិសុខ')) {
            return 'សន្តិសុខ';
        }

        if (str_starts_with($departmentName, 'អាយធី')) {
            return 'អាយធី';
        }

        if (str_starts_with($departmentName, 'អ្នកអ៊ុត')) {
            return 'អ្នកអ៊ុត';
        }

        if (str_starts_with($departmentName, 'អ្នកដឹកជញ្ជូន') || str_starts_with($departmentName, 'អ្នកដឹកជញ្ជូល')) {
            return 'អ្នកដឹកជញ្ជូន';
        }

        if (str_starts_with($departmentName, 'រំលស់')) {
            return 'រំលស់';
        }

        if (str_starts_with($departmentName, 'ផ្នែកស្តុក')) {
            return 'ផ្នែកស្តុក';
        }

        if (str_starts_with($departmentName, 'អ្នកនិពន្ធ')) {
            return 'អ្នកនិពន្ធ';
        }

        return $departmentName;
    }

    private function getSummaryMetricLabel(string $metric): string
    {
        return match ($metric) {
            'total_all_employee' => 'All Staff',
            'inactive_employee' => 'Inactive Employee',
            'active_employee' => 'Active',
            'active_employee_checkin' => 'Checked In',
            'active_employee_not_yet_checkin' => 'No Check-In',
            'active_employee_checkout' => 'Checked Out',
            'active_employee_not_yet_checkout' => 'No Check-Out',
            'active_employee_dayoff' => 'Day Off',
            'active_employee_leave' => 'Leave',
            'active_employee_pending_request' => 'Pending',
            default => ucfirst(str_replace('_', ' ', $metric)),
        };
    }
}
