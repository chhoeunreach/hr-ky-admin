<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\AssignedMember;
use App\Models\Task;
use App\Repositories\UserRepository;
use App\Requests\Project\ProjectRequest;
use App\Requests\Task\AssignEmployeeRequest;
use App\Requests\Task\TaskRequest;
use App\Services\Notification\NotificationService;
use App\Services\Project\ProjectService;
use App\Services\Task\TaskService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class  TaskController extends Controller
{
    private $view = 'admin.task.';

    public ProjectService $projectService;
    public TaskService $taskService;
    public UserRepository $userRepo;
    private NotificationService $notificationService;

    public function __construct(TaskService $taskService,
                                ProjectService $projectService,
                                UserRepository $userRepo,
                                NotificationService $notificationService
    )
    {
        $this->taskService = $taskService;
        $this->projectService = $projectService;
        $this->userRepo = $userRepo;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $this->authorize('view_task_list');
        try {
            $filterParameters = [
                'task_id' => $request->task_id ?? null,
                'status' => $request->status ?? null,
                'priority' => $request->priority ?? null,
                'members' => $request->members ?? null,
                'project_id' => $request->project_id ?? null,
            ];
            $select = ['*'];
            $with = ['assignedMembers.user:id,name','project:name,id'];
            $projects = $this->projectService->getAllActiveProjects(['id','name']);
            $employees = $this->userRepo->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);
            $tasks = $this->taskService->getAllFilteredTasksPaginated($filterParameters, $select, $with);
            $allTasks = $this->taskService->getAllTasks(['id','name']);
            return view($this->view . 'index', compact(
                'tasks',
                'filterParameters',
                'projects',
                'employees',
            'allTasks'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_task');
        try {
            $projectSelect= ['name','id'];
            $projectLists = $this->projectService->getAllActiveProjects($projectSelect);
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'create',compact('projectLists','isBsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function createTaskFromProjectPage($projectId)
    {
        $this->authorize('create_task');
        try {
            $select = ['name', 'id'];
            $with = ['assignedMembers.user:id,name'];
            $project = $this->projectService->findProjectDetailById($projectId,$with,$select);
            $projectMember = $project->assignedMembers;
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'create',compact('project','projectMember','isBsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $this->authorize('create_task');
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();
            $task = $this->taskService->saveTaskDetail($validatedData);
            DB::commit();
            if($task && $validatedData['notification'] == 1){
                $addMessage = __('message.task_notification_message',['name'=>$validatedData['name'], 'end_date'=>$validatedData['end_date']]);
                $this->sendNotification($task->id, $validatedData['assigned_member'], $addMessage);
            }
            return redirect()
                ->route('admin.tasks.index')
                ->with('success',  __('message.task_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize('show_task_detail');
        try {
            $taskSelect= ['*'];
            $with = [
                'project:id,name',
                'assignedMembers.user:id,post_id,name,avatar',
                'assignedMembers.user.post:id,post_name',
                'taskChecklists.taskAssigned:id,name,avatar',
                'completedTaskChecklist:id,task_id',
                'taskAttachments',
                'taskComments.replies',
                'taskComments.replies.mentionedMember.user:id,name',
                'taskComments.mentionedMember.user:id,name,avatar',
                'taskComments.createdBy:id,name,avatar',
            ];
            $taskDetail = $this->taskService->findTaskDetailById($id,$with,$taskSelect);
            $images = [];
            $files = [];
            $taskAttachment = $taskDetail->taskAttachments;
            $comments = $taskDetail->taskComments;

            foreach ($taskAttachment as $key => $value){
                if(!in_array($value->attachment_extension,['pdf','doc','docx','ppt','txt','xls','zip'])){
                    $images[] = $value;
                }else{
                    $files[] = $value;
                }
            }
            return view($this->view . 'show',compact('taskDetail','images','files','comments'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_task');
        try {
            $images = [];
            $files = [];
            $memberId = [];
            $taskSelect = ['*'];
            $projectSelect= ['name','id'];
            $projectWith = ['assignedMembers.user:id,name'];
            $taskWith = ['project.assignedMembers.user:name,id','assignedMembers.user:id,name','taskAttachments'];
            $projectLists = $this->projectService->getAllActiveProjects($projectSelect,$projectWith);
            $taskDetail = $this->taskService->findTaskDetailById($id,$taskWith,$taskSelect);
            foreach($taskDetail->assignedMembers as $key => $value){
                $memberId[] = $value->user->id;
            }
            $attachments =  $taskDetail->taskAttachments;
            if(count($attachments) > 0){
                foreach($attachments as $key => $value){
                    if(!in_array($value->attachment_extension,['pdf','doc','docx','ppt','txt','xls','zip'])){
                        $images[] = $value;
                    }else{
                        $files[] = $value;
                    }
                }
            }
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'edit',
                compact('taskDetail',
                    'memberId',
                    'projectLists','images','files','isBsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(TaskRequest $request, $taskId)
    {
        $this->authorize('edit_task');
        try {
            $validatedData = $request->validated();
            $previousEmployee = AssignedMember::where('assignable_id', $taskId)
                ->where('assignable_type','=' ,'task')
                ->pluck('member_id')
                ->toArray();

            $taskDetail = $this->taskService->updateTaskDetail($validatedData, $taskId);

            $removedEmployeeIds = array_diff($previousEmployee, $validatedData['assigned_member']);
            $addedEmployeeIds = array_diff($validatedData['assigned_member'], $previousEmployee);

            $remainingEmployeeIds = array_intersect($previousEmployee, $validatedData['assigned_member']);

            if($taskDetail && $validatedData['notification'] == 1){

                $today = date('Y-m-d');

                $end = $validatedData['end_date'] ;

                if(strtotime($today) <= strtotime($end)){
                    // task assign
                    $addMessage = __('message.task_notification_message',['name'=>$validatedData['name'], 'end_date'=>$validatedData['end_date']]);
                    $this->sendNotification($taskId, $addedEmployeeIds, $addMessage);

                    // remove from task
                    $removeMessage = __('message.task_remove_notification_message',['name'=>$validatedData['name']]);
                    $this->sendNotification($taskId, $removedEmployeeIds, $removeMessage);

                    // task change
                    $changeMessage = __('message.task_change_msg',['name'=>$validatedData['name']]);
                    $this->sendNotification($taskId, $remainingEmployeeIds, $changeMessage);
                }
            }

            return redirect()
                ->route('admin.tasks.index')
                ->with('success',  __('message.task_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_task');
        try {
            DB::beginTransaction();
            $this->taskService->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_task');
        try {
            DB::beginTransaction();
            $this->taskService->deleteTaskDetail($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.task_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function getAllTaskByProjectId($projectId): JsonResponse|RedirectResponse
    {
        $this->authorize('view_task_list');
        try {
            $select = ['name', 'id'];
            $tasks = $this->taskService->getProjectTasks($projectId,$select);
            return response()->json([
                'data' => $tasks
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    private function sendNotification($taskId, $userIds, $message){
        $notificationData['title'] = __('message.task_notification');
        $notificationData['type'] = 'task';
        $notificationData['user_id'] = $userIds;
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $taskId;
        $notification = $this->notificationService->store($notificationData);
        if($notification){
            SMPushHelper::sendProjectManagementNotification($notification->title,
                $notification->description,
                $notificationData['user_id'],
                $taskId);
        }
    }

    public function getEmployeesToAddToTask($taskId)
    {
        try{
            $formData['url'] = route('admin.tasks.update-member-data');
            $formData['title'] = 'Update Members';
            $formData['label'] = 'Task Member';

            $taskDetail = $this->taskService->findTaskDetailById($taskId);

            $alreadyAssignedEmployee = AssignedMember::where('assignable_id',$taskId)
                ->where('assignable_type','=' ,'task')
                ->pluck('member_id')
                ->toArray();
            $employees = $this->projectService->getAllMemberDetailAssignedInProject($taskDetail->project_id);
            return view($this->view . 'update-employee',
                compact('employees','formData','alreadyAssignedEmployee','taskId'));

        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function updateTaskMember(AssignEmployeeRequest $request)
    {
        try{
            $validatedData = $request->validated();
            $taskDetail = $this->taskService->findTaskDetailById($validatedData['task_id']);
            if(!$taskDetail){
                throw new Exception(__('message.task_not_found'),404);
            }

            $previousEmployee = AssignedMember::where('assignable_id', $validatedData['task_id'])
                ->where('assignable_type','=' ,'task')
                ->pluck('member_id')
                ->toArray();

            $status = $this->taskService->updateMemberOfTask($taskDetail,$validatedData);


            $removedEmployeeIds = array_diff($previousEmployee, $validatedData['employee']);
            $addedEmployeeIds = array_diff($validatedData['employee'], $previousEmployee);

            $remainingEmployeeIds = array_intersect($previousEmployee, $validatedData['employee']);

            $today = date('Y-m-d');

            $end = $taskDetail['end_date'] ;

            if($status && strtotime($today) <= strtotime($end)){
                // task assign
                $addMessage = __('message.task_notification_message',['name'=>$taskDetail['name'], 'end_date'=>$taskDetail['end_date']]);
                $this->sendNotification($taskDetail['task_id'], $addedEmployeeIds, $addMessage);

                // remove from task
                $removeMessage = __('message.task_remove_notification_message',['name'=>$taskDetail['name']]);
                $this->sendNotification($taskDetail['task_id'], $removedEmployeeIds, $removeMessage);

                // task change
                $changeMessage = __('message.task_change_msg',['name'=>$taskDetail['name']]);
                $this->sendNotification($taskDetail['task_id'], $remainingEmployeeIds, $changeMessage);
            }
            return redirect()
                ->route('admin.tasks.show',$validatedData['task_id'])
                ->with('success', __('message.task_member_updated',['name'=>ucfirst($taskDetail['name'])]));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }
}
