<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\AssignedMember;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectTeamLeader;
use App\Models\Task;
use App\Repositories\UserRepository;
use App\Requests\Project\AssignEmployeeRequest;
use App\Requests\Project\ProjectRequest;
use App\Services\Client\ClientService;
use App\Services\Notification\NotificationService;
use App\Services\Project\ProjectService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    private $view = 'admin.project.';

    private UserRepository $userRepo;
    private ProjectService $projectService;
    private ClientService $clientService;
    private NotificationService $notificationService;

    public function __construct(UserRepository      $userRepo,
                                ProjectService      $projectService,
                                ClientService           $clientService,
                                NotificationService $notificationService
    )
    {
        $this->userRepo = $userRepo;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $this->authorize('view_project_list');
        try {
            $filterParameters = [
                'project_name' => $request->project_name ?? null,
                'status' => $request->status ?? null,
                'priority' => $request->priority ?? null,
                'members' => $request->members ?? null
            ];
            $select = ['*'];
            $with = [
                'assignedMembers.user:id,name,avatar',
                'projectLeaders.user:id,name,avatar',
                'tasks:id,project_id',
                'completedTask:id,project_id',
                'client:id,name,avatar'
            ];
            $selectEmployeeColumn = ['name', 'id'];
            $employees = $this->userRepo->getAllVerifiedEmployeesExceptAdminOfCompany($selectEmployeeColumn);
            $projects = $this->projectService->getAllFilteredProjectsPaginated($filterParameters, $select, $with);
            $allProjects =  $this->projectService->getAllProjectLists(['id','name']);
            return view($this->view . 'index', compact('projects', 'filterParameters','employees','allProjects'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_project');
        try {
            $select = ['name', 'id'];
            $employees = $this->userRepo->getAllVerifiedEmployeesExceptAdminOfCompany($select);
            $clientLists = $this->clientService->getAllActiveClients($select);
            return view($this->view . 'create', compact('employees','clientLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->authorize('create_project');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
             $project = $this->projectService->saveProjectDetail($validatedData);
            DB::commit();

                if($project && $validatedData['notification'] == 1){

                    // project assign
                    $addedUserIds = array_unique(array_merge($validatedData['assigned_member'],$validatedData['project_leader']));
                    $addMessage = __('message.project_assign',['name'=>$validatedData['name'],'deadline'=>$validatedData['deadline']]);
                    $this->sendNotification($addedUserIds, $addMessage, $project->id);
                }
            return redirect()
                ->route('admin.projects.index')
                ->with('success', __('message.project_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * @param $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function show($id)
    {
        $this->authorize('show_project_detail');
        try {
            $select = ['*'];
            $with = [
                'assignedMembers.user:id,name,post_id,avatar',
                'assignedMembers.user.post:id,post_name',
                'client:id,name,email,contact_no,avatar,address,country',
                'projectLeaders.user:id,post_id,name,avatar',
                'projectLeaders.user.post:id,post_name',
                'tasks.assignedMembers.user:id,name,avatar',
                'completedTask:id,name,project_id',
                'projectAttachments'
            ];
            $images = [];
            $files = [];
            $projectDetail = $this->projectService->findProjectDetailById($id,$with,$select);
            $assignedMember = $projectDetail->assignedMembers;
            $projectLeader = $projectDetail->projectLeaders;
            $projectDocument =  $projectDetail->projectAttachments;
            foreach ($projectDocument as $key => $value){
                if(!in_array($value->attachment_extension,['pdf','doc','docx','ppt','txt','xls','zip'])){
                    $images[] = $value;
                }else{
                    $files[] = $value;
                }
            }
            return view($this->view . 'show',
                compact('projectDetail',
                    'assignedMember',
                    'projectLeader',
                    'images',
                    'files')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_project');
        try {
            $memberId = [];
            $images = [];
            $files = [];
            $leaderId[] = [];
            $selectProjectColumn = ['*'];
            $with = ['assignedMembers.user:id,name','projectLeaders.user:id,name','projectAttachments'];
            $selectUserColumn = ['name', 'id'];
            $clientLists = $this->clientService->getAllActiveClients();

            $employees = $this->userRepo->getAllVerifiedEmployeesExceptAdminOfCompany($selectUserColumn);


            $projectDetail = $this->projectService->findProjectDetailById($id,$with,$selectProjectColumn);

            if(AppHelper::ifDateInBsEnabled()){
                $projectDetail->start_date = AppHelper::dateInYmdFormatEngToNep($projectDetail->start_date);
                $projectDetail->deadline = AppHelper::dateInYmdFormatEngToNep($projectDetail->deadline);
            }
            foreach($projectDetail->assignedMembers as $key => $value){

                    $memberId[] = $value->user->id;
            }
            foreach($projectDetail->projectLeaders as $key => $value){

                    $leaderId[] = $value->user->id;
            }
            $projectDocument =  $projectDetail->projectAttachments;
            if(count($projectDocument) > 0){
                foreach($projectDocument as $key => $value){
                    if(!in_array($value->attachment_extension,['pdf','doc','docx','ppt','txt','xls','zip'])){
                        $images[] = $value;
                    }else{
                        $files[] = $value;
                    }
                }
            }


            return view($this->view . 'edit',
                compact('projectDetail', 'employees','memberId','clientLists','leaderId','images','files'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ProjectRequest $request, $projectId)
    {
        $this->authorize('edit_project');
        try {
            $validatedData = $request->validated();

            $previousEmployee = AssignedMember::where('assignable_id', $projectId)
                ->where('assignable_type','=' ,'project')
                ->pluck('member_id')
                ->toArray();


            $previousLeader = ProjectTeamLeader::where('project_id',$projectId)->get('leader_id')->toArray();

            $projectDetail = $this->projectService->updateProjectDetail($validatedData, $projectId);


            $previousLeaderIds = array_column($previousLeader, 'leader_id');
            $removedEmployeeIds = array_diff($previousEmployee, $validatedData['assigned_member']);

            $removedLeaderIds = array_diff($previousLeaderIds, $validatedData['project_leader']);

            $addedEmployeeIds = array_diff($validatedData['assigned_member'], $previousEmployee);

            $remainingEmployeeIds = array_intersect($previousEmployee, $validatedData['assigned_member']);

            $addedLeaderIds = array_diff($validatedData['project_leader'], $previousLeaderIds);

            $remainingLeaderIds = array_intersect($previousLeaderIds, $validatedData['project_leader']);

            $this->removeEmployeeFromTask($projectId, $removedEmployeeIds);


            if($projectDetail && $validatedData['notification'] == 1){


                $today = date('Y-m-d');

                $end = $projectDetail['deadline'] ;

                if(strtotime($today) <= strtotime($end)){

                    // project assign
                    $addedUserIds = array_unique(array_merge($addedEmployeeIds,$addedLeaderIds));
                    $addMessage = __('message.project_assign',['name'=>$validatedData['name'],'deadline'=>$validatedData['deadline']]);
                    $this->sendNotification($addedUserIds, $addMessage, $projectId);


                    // remove from project
                    $removedUserIds = array_unique(array_merge($removedEmployeeIds,$removedLeaderIds));
                    $removeMessage = __('message.project_remove',['name'=>$validatedData['name']]);
                    $this->sendNotification($removedUserIds, $removeMessage, $projectId);


                    // project change
                    $otherUserIds = array_unique(array_merge($remainingEmployeeIds,$remainingLeaderIds));
                    $changeMessage = __('message.project_change_msg',['name'=>$validatedData['name']]);
                    $this->sendNotification($otherUserIds, $changeMessage, $projectId);

                }
            }

            return redirect()
                ->route('admin.projects.index')
                ->with('success', __('message.project_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_project');
        try {
            DB::beginTransaction();
            $this->projectService->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_project');
        try {
            DB::beginTransaction();
            $this->projectService->deleteProjectDetail($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.project_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getProjectAssignedMembersByProjectId($projectId)
    {
        try {
            $member = [];
            $select = ['name', 'id'];
            $with = ['assignedMembers.user:id,name'];
            $projects = $this->projectService->findProjectDetailById($projectId,$with,$select);
            $projectTeam = $projects->assignedMembers;
            foreach($projectTeam as $key => $value){
                $member[$key]['id'] = $value->user->id;
                $member[$key]['name'] = $value->user->name;
            }
            return response()->json([
                'data' => $member
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    private function sendNotificationToAssignedProjectTeam($title,$message,$userIds,$id)
    {
        SMPushHelper::sendProjectManagementNotification($title,$message,$userIds,$id);
    }

    public function getEmployeesToAddTpProject($addType,$projectId)
    {
        try{
            $employeeType = ['member','leader'];
            if(!in_array($addType,$employeeType)){
                throw new Exception(__('message.something_went_wrong'),400);
            }
            $formData['url'] = ($addType == 'leader') ? route('admin.projects.update-leader-data') : route('admin.projects.update-member-data');
            $formData['title'] = ($addType == 'leader') ? 'Update Leaders' : 'Update Members';
            $formData['label'] = ($addType == 'leader') ? 'Project Leader' : 'Project Member';

            if($addType == 'leader'){
                $assignedEmployee = $this->projectService->getAllLeaderDetailAssignedInProject($projectId);
            }else{
                $assignedEmployee = $this->projectService->getAllMemberDetailAssignedInProject($projectId);
            }

            $alreadyAssignedEmployee = $assignedEmployee->pluck('id')->all();
            $select = ['id','name'];
            $employees = $this->userRepo->getAllVerifiedEmployeesExceptAdminOfCompany($select);
            return view($this->view . 'update-employee',
                compact('employees','formData','alreadyAssignedEmployee','projectId'));

        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function updateLeaderToProject(AssignEmployeeRequest $request)
    {
        try{
            $validatedData = $request->validated();
            $projectDetail = $this->projectService->findProjectDetailById($validatedData['project_id']);
            if(!$projectDetail){
                throw new Exception(__('message.project_not_found'),404);
            }
            $previousLeader = ProjectTeamLeader::where('project_id',$projectDetail['id'])->get('leader_id')->toArray();


            $status = $this->projectService->updateLeadersOfProject($projectDetail,$validatedData);


            $previousLeaderIds = array_column($previousLeader, 'leader_id');

            $removedLeaderIds = array_diff($previousLeaderIds, $validatedData['employee']);

            $addedLeaderIds = array_diff($validatedData['employee'], $previousLeaderIds);

            $remainingLeaderIds = array_intersect($previousLeaderIds, $validatedData['employee']);

            $projectMembers = AssignedMember::where('assignable_id', $projectDetail['id'])
                ->where('assignable_type','=' ,'project')
                ->pluck('member_id')
                ->toArray();
            $today = date('Y-m-d');

            $end = $projectDetail['deadline'] ;

            if($status  && strtotime($today) <= strtotime($end)){
                // project assign
                $addMessage = __('message.project_assign',['name'=>$projectDetail['name'],'deadline'=>$projectDetail['deadline']]);
                $this->sendNotification($addedLeaderIds, $addMessage, $projectDetail['id']);


                // remove from project
                $removeMessage = __('message.project_remove',['name'=>$projectDetail['name']]);
                $this->sendNotification($removedLeaderIds, $removeMessage, $projectDetail['id']);


                // project change
                $changeMessage = __('message.project_change_msg',['name'=>$projectDetail['name']]);
                $this->sendNotification( array_unique(array_merge($projectMembers,$remainingLeaderIds)), $changeMessage, $projectDetail['id']);
            }
            return redirect()
                ->route('admin.projects.show',$validatedData['project_id'])
                ->with('success', __('message.project_leader_updated',['name'=>ucfirst($projectDetail->name)]));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function updateMemberToProject(AssignEmployeeRequest $request)
    {
        try{
            $validatedData = $request->validated();


            $projectDetail = $this->projectService->findProjectDetailById($validatedData['project_id']);
            if(!$projectDetail){
                throw new Exception(__('message.project_not_found'),404);
            }
            $previousEmployee = AssignedMember::where('assignable_id', $projectDetail['id'])
                ->where('assignable_type','=' ,'project')
                ->pluck('member_id')
                ->toArray();
            $status = $this->projectService->updateMemberOfProject($projectDetail,$validatedData);

            $removedEmployeeIds = array_diff($previousEmployee, $validatedData['employee']);

            $this->removeEmployeeFromTask($projectDetail['id'], $removedEmployeeIds);


            $addedEmployeeIds = array_diff($validatedData['employee'], $previousEmployee);

            $remainingEmployeeIds = array_intersect($previousEmployee, $validatedData['employee']);

            $projectLeaders = ProjectTeamLeader::where('project_id',$projectDetail['id'])->get('leader_id')->toArray();

            $leaderIds = array_column($projectLeaders, 'leader_id');
            $today = date('Y-m-d');

            $end = $projectDetail['deadline'] ;

            if($status  && strtotime($today) <= strtotime($end)){
                // project assign
                $addMessage = __('message.project_assign',['name'=>$projectDetail['name'],'deadline'=>$projectDetail['deadline']]);
                $this->sendNotification($addedEmployeeIds, $addMessage, $projectDetail['id']);


                // remove from project
                $removeMessage = __('message.project_remove',['name'=>$projectDetail['name']]);
                $this->sendNotification($removedEmployeeIds, $removeMessage, $projectDetail['id']);


                // project change
                $changeMessage = __('message.project_change_msg',['name'=>$projectDetail['name']]);
                $this->sendNotification(array_unique(array_merge($leaderIds,$remainingEmployeeIds)), $changeMessage, $projectDetail['id']);
            }
            return redirect()
                ->route('admin.projects.show',$validatedData['project_id'])
                ->with('success',__('message.project_member_updated',['name'=>ucfirst($projectDetail->name)] ));

        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function sendNotification($userIds, $message, $id){
        $notificationData['title'] = __('message.project_notification');
        $notificationData['type'] = 'project';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $id;
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            $this->sendNotificationToAssignedProjectTeam(
                $notification->title,
                $notification->description,
                $notificationData['user_id'],
                $id);
        }
    }

    public function removeEmployeeFromTask($projectId, $removedEmployees)
    {

        if(count($removedEmployees) > 0){
            AssignedMember::whereIn('assignable_id', function($query) use ($projectId) {
                $query->select('id')
                    ->from('tasks')
                    ->where('project_id', $projectId);
            })
                ->where('assignable_type', 'task')
                ->whereIn('member_id', $removedEmployees)
                ->delete();
        }

    }

}
