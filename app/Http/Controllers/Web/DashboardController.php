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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{
    private DashboardRepository $dashboardRepo;
    private ClientService $clientService;
    private TaskService $taskService;
    private ProjectService $projectService;

    public function __construct(DashboardRepository $dashboardRepo,
                                ClientService       $clientService,
                                TaskService         $taskService,
                                ProjectService      $projectService
    )
    {
        $this->dashboardRepo = $dashboardRepo;
        $this->clientService = $clientService;
        $this->projectService = $projectService;
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {

        try {

            $appTimeSetting = AppHelper::check24HoursTimeAppSetting();


            $projectSelect = ['id','name','start_date','deadline','status','priority'];
            $withProject = [
                'projectLeaders.user:id,name,avatar',
                'tasks:id,project_id',
                'completedTask:id,project_id'
            ];
            $companyId = AppHelper::getAuthUserCompanyId();

            if (!$companyId) {
                throw new Exception(__('message.company_not_found'));
            }

            $date = AppHelper::yearDetailToFilterData();
            $dashboardDetail = $this->dashboardRepo->getCompanyDashboardDetail($companyId, $date);

            $topClients = $this->clientService->getTopClientsOfCompany();
            $taskPieChartData = $this->taskService->getTaskDataForPieChart();
            $projectCardDetail = $this->projectService->getProjectCardData();
            $recentProjects = $this->projectService->getRecentProjectListsForDashboard($projectSelect,$withProject);
            $multipleAttendance = AppHelper::getAttendanceLimit();

            return view('admin.dashboard', compact(
                'dashboardDetail',
                'topClients',
                'taskPieChartData',
                'projectCardDetail',
                'recentProjects',
                'appTimeSetting',
                'multipleAttendance'
                )
            );
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function showQR()
    {
        $url = $this->generateUrl();

        return view('admin.print_qr',compact('url'));
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


}
