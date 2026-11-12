<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\MeetingAttendance;
use App\Models\TeamMeeting;
use App\Resources\TeamMeeting\TeamMeetingCollection;
use App\Resources\TeamMeeting\TeamMeetingResource;
use App\Services\TeamMeeting\TeamMeetingService;
use App\Services\TeamMeeting\MeetingCheckInService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMeetingApiController extends Controller
{
    private TeamMeetingService $teamMeetingService;

    public function __construct(TeamMeetingService $teamMeetingService)
    {
        $this->teamMeetingService = $teamMeetingService;
    }

    public function getAllAssignedTeamMeetingDetail(Request $request): TeamMeetingCollection|JsonResponse
    {
        try {
            $perPage = $request->get('per_page') ?? 20;
            $teamMeeting = $this->teamMeetingService->getAllAssignedTeamMeetingDetail($perPage);
            return new TeamMeetingCollection($teamMeeting);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function findTeamMeetingDetail($meetingId): JsonResponse
    {
        try {
            $detail = [];
            $teamMeetingDetail = $this->teamMeetingService->findOrFailTeamMeetingDetailById($meetingId);
            $detail[] = new TeamMeetingResource($teamMeetingDetail);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $detail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function meetingAttendanceStatus(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $meetings = TeamMeeting::query()
                ->with(['meetingAttendances' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
                ->whereHas('teamMeetingParticipator', function ($query) use ($userId) {
                    $query->where('meeting_participator_id', $userId);
                })
                ->where('company_id', auth()->user()->company_id)
                ->where('meeting_published_at', '<=', now())
                ->where('meeting_date', '>=', Carbon::now()->subMonth(12))
                ->orderByDesc('meeting_date')
                ->get();

            $checkIn = app(MeetingCheckInService::class);
            $records = $meetings->map(function (TeamMeeting $meeting) use ($checkIn) {
                $attendance = $meeting->meetingAttendances->first();
                [$opensAt, $closesAt] = $checkIn->window($meeting);

                return [
                    'id' => $meeting->id,
                    'title' => ucfirst($meeting->title),
                    'venue' => ucfirst($meeting->venue),
                    'meeting_date' => AppHelper::formatDateForView($meeting->meeting_date, false),
                    'meeting_start_time' => \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceView($meeting->meeting_start_time),
                    'attendance_status' => $checkIn->status($meeting),
                    'check_in_opens_at' => $opensAt->toIso8601String(),
                    'check_in_closes_at' => $closesAt->toIso8601String(),
                    'is_joined' => (bool) $attendance,
                    'checked_in_at' => $attendance?->checked_in_at?->format('Y-m-d H:i:s'),
                    'checked_in_at_formatted' => $attendance ? convertDateTimeFormat($attendance->checked_in_at) : null,
                ];
            })->values();

            return AppHelper::sendSuccessResponse(__('index.data_found'), [
                'meetings' => $records,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function scanMeetingAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate(['qr_payload' => ['required', 'string', 'max:4096']]);
        $result = app(MeetingCheckInService::class)
            ->scan(trim($validated['qr_payload']), $request->user());
        return AppHelper::sendSuccessResponse(
            $result['already_joined'] ? 'Meeting attendance already recorded.' : 'Meeting attendance recorded successfully.',
            ['attendance' => $this->meetingAttendancePayload($result['attendance']),
             'already_joined' => $result['already_joined']]
        );
    }

    private function meetingAttendancePayload(MeetingAttendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'team_meeting_id' => $attendance->team_meeting_id,
            'user_id' => $attendance->user_id,
            'checked_in_at' => $attendance->checked_in_at?->format('Y-m-d H:i:s'),
            'checked_in_at_formatted' => $attendance->checked_in_at ? convertDateTimeFormat($attendance->checked_in_at) : null,
        ];
    }

}

