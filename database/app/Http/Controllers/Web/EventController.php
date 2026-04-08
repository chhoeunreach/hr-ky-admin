<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\TeamMeeting;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\Event\EventRequest;
use App\Requests\TeamMeeting\TeamMeetingRequest;
use App\Services\EventManagement\EventService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    private $view = 'admin.event.';

    public function __construct(protected EventService $eventService, protected UserRepository $userRepository, protected DepartmentRepository $departmentRepository)
    {}

    public function index()
    {
        $this->authorize('list_event');
        try {

            $this->updateEventStatus();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $events = [];
            $select = ['*'];
            $with = [];
            $perPage = 6;
            $eventLists = $this->eventService->getAllEvents($select, $with);

            if($isBsEnabled){
                $events = $eventLists;
            }else{
                foreach ($eventLists as $event) {
                    $events[] = [
                        'id' => $event->id,
                        'title' => substr($event->title, 0, 12) . (strlen($event->title) > 12 ? '...' : ''),
                        'start' => $event->start_date,
                        'end' => $event->end_date ?? '',
                        'color'=>$event->background_color,

                    ];
                }
            }

            $upcomingEvents = $this->eventService->getActiveBackendEvents($perPage);
            $pastEvents = $this->eventService->getPastBackendEvents($perPage);
            $userIds = [];
            return view($this->view . 'index', compact('events','upcomingEvents','pastEvents','isBsEnabled','userIds'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_event');
        try{

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectDepartment = ['id', 'dept_name'];
            $selectUser = ['id', 'name'];
            $departments = $this->departmentRepository->getAllActiveDepartments([],$selectDepartment);
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);
            $userIds = [];
            return view($this->view . 'create',
                compact('departments', 'users','isBsEnabled','userIds')
            );
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(EventRequest $request)
    {
        $this->authorize('create_event');
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();
            $eventDetail = $this->eventService->storeEvent($validatedData);
            DB::commit();
            if($eventDetail && $validatedData['notification'] == 1){

                $message = 'Your are invited to participate in '. ucfirst($eventDetail['title']);

                if(isset($eventDetail['end_date'])){
                    $message .=' starting from '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($eventDetail['end_date']);
                }else{
                    $message .=' on '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']);
                }

                $this->sendNoticeNotification('Event Notification', $message, $validatedData['employee_id']);
            }
            return redirect()
                ->route('admin.event.index')
                ->with('success', __('message.event_create'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $this->authorize('show_event');
            $select = ['*'];
            $eventDetail = $this->eventService->findEventDetailById($id, $select);

            $eventDetail->title = ucfirst($eventDetail->title);
            $eventDetail->location = ucfirst($eventDetail->location);
            $eventDetail->start_date = AppHelper::formatDateForView($eventDetail->start_date);
            $eventDetail->start_time = AppHelper::convertLeaveTimeFormat($eventDetail->start_time);
            $eventDetail->attachment = $eventDetail->attachment ? asset(Event::UPLOAD_PATH.$eventDetail->attachment):'';
            $eventDetail->end_date = isset($eventDetail->end_date) ? AppHelper::formatDateForView($eventDetail->end_date):'';
            $eventDetail->end_time = AppHelper::convertLeaveTimeFormat($eventDetail->end_time);
            $eventDetail->description = removeHtmlTags($eventDetail->description);
            $eventDetail->creator = $eventDetail->createdBy->name;
            return response()->json([
                'data' => $eventDetail,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_event');
        try {
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $with = ['eventDepartment','eventUser'];
            $selectDepartment = ['id', 'dept_name'];
            $selectUser = ['id', 'name'];
            $departments = $this->departmentRepository->getAllActiveDepartments([],$selectDepartment);
            $users = $this->userRepository->getAllVerifiedEmployeeOfCompany($selectUser);

            $eventDetail = $this->eventService->findEventDetailById($id, ['*'],$with);

            $departmentIds = [];
            foreach ($eventDetail->eventDepartment as $key => $value) {
                $departmentIds[] = $value->department_id;
            }
            $userIds = [];
            foreach ($eventDetail->eventUser as $key => $value) {
                $userIds[] = $value->user_id;
            }
            $select = ['name', 'id'];

            // Fetch users by selected departments
            $filteredUsers = !empty($departmentIds)
                ? $this->userRepository->getActiveEmployeesByDepartment($departmentIds, $select)
                : $users;


            return view($this->view . 'edit', compact('eventDetail', 'departments', 'users', 'userIds','departmentIds','isBsEnabled','filteredUsers'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(EventRequest $request, $id)
    {
        $this->authorize('edit_event');
        try {
            $validatedData = $request->validated();

            $previousEmployee = EventUser::where('event_id',$id)->get('user_id')->toArray();

            DB::beginTransaction();
            $eventDetail = $this->eventService->update($id, $validatedData);
            DB::commit();
            $previousEmployeeIds = array_column($previousEmployee, 'user_id');
            $removedIds = array_diff($previousEmployeeIds, $validatedData['employee_id']);
            $addedEmployeeIds = array_diff($validatedData['employee_id'], $previousEmployeeIds);

            $remainingEmployeeIds = array_intersect($previousEmployeeIds, $validatedData['employee_id']);

            if($eventDetail && $validatedData['notification'] == 1){

                $sendNotification = false;
                $today = date('Y-m-d H:i');
                $start = $eventDetail['start_date'].' '. $eventDetail['end_time'] ;
                if(isset($eventDetail['end_date'])){
                    $end = $eventDetail['end_date'] .' '. $eventDetail['end_time'];

                    if(strtotime($today) <= strtotime($end)){

                        $sendNotification = true;
                    }

                }else{

                    if(strtotime($today) <= strtotime($start)){

                        $sendNotification = true;
                    }

                }
                if($sendNotification){

                    // invitation
                    $message = 'Your are invited to participate in '. ucfirst($eventDetail['title']);

                    if(isset($eventDetail['end_date'])){
                        $message .=' starting from '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($eventDetail['end_date']);
                    }else{
                        $message .=' on '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']);
                    }

                    $this->sendNoticeNotification('Event Notification', $message, $addedEmployeeIds);

                    // removal
                    $removeMassage = 'Sorry, we have cancelled your invitation in '. ucfirst($eventDetail['title']);

                    if(isset($eventDetail['end_date'])){
                        $removeMassage .=' starting from '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']). ' to '. \App\Helpers\AppHelper::formatDateForView($eventDetail['end_date']);
                    }else{
                        $removeMassage .=' on '.\App\Helpers\AppHelper::formatDateForView($eventDetail['start_date']);
                    }
                    $this->sendNoticeNotification('Event Notification', $removeMassage, $removedIds);


                    // change
                    $message = 'The event "' . ucfirst($eventDetail['title']) . '" that you are participating in has been updated';
                    $this->sendNoticeNotification('Event Notification', $message, $remainingEmployeeIds);

                }

            }
            return redirect()->route('admin.event.index')->with('success', __('message.event_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_event');
        try {
            DB::beginTransaction();
            $this->eventService->deleteEvent($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.event_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function removeImage($id)
    {
        $this->authorize('delete_event');
        try {
            DB::beginTransaction();
                $this->eventService->removeEventAttachment($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.image_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateEventStatus()
    {

        $this->eventService->updateStatus();
    }


    private function sendNoticeNotification($title, $description, $userIds)
    {
        SMPushHelper::sendEventNotification($title, $description, $userIds);
    }
}
