<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\MeetingAttendance;
use App\Models\MeetingParticipatorDetail;
use App\Models\TeamMeeting;
use App\Resources\TeamMeeting\TeamMeetingCollection;
use App\Resources\TeamMeeting\TeamMeetingResource;
use App\Services\TeamMeeting\TeamMeetingService;
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
                ->where('meeting_published_at', '>=', Carbon::now()->subMonth(12))
                ->orderByDesc('meeting_date')
                ->get();

            $records = $meetings->map(function (TeamMeeting $meeting) {
                $attendance = $meeting->meetingAttendances->first();

                return [
                    'id' => $meeting->id,
                    'title' => ucfirst($meeting->title),
                    'venue' => ucfirst($meeting->venue),
                    'meeting_date' => AppHelper::formatDateForView($meeting->meeting_date, false),
                    'meeting_start_time' => \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceView($meeting->meeting_start_time),
                    'qr_payload' => $this->qrPayloadForMeeting($meeting->id),
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
        try {
            $validated = $request->validate([
                'qr_payload' => ['required', 'string'],
                'latitude' => ['nullable', 'numeric'],
                'longitude' => ['nullable', 'numeric'],
                'device_id' => ['nullable', 'string', 'max:255'],
            ]);

            $meetingId = $this->meetingIdFromQrPayload($validated['qr_payload']);
            if (!$meetingId) {
                return AppHelper::sendErrorResponse('Invalid meeting QR code.', 422);
            }

            $userId = auth()->id();
            $meeting = TeamMeeting::query()->find($meetingId);
            if (!$meeting) {
                return AppHelper::sendErrorResponse('Meeting not found.', 404);
            }

            $isParticipant = MeetingParticipatorDetail::query()
                ->where('team_meeting_id', $meeting->id)
                ->where('meeting_participator_id', $userId)
                ->exists();

            if (!$isParticipant) {
                return AppHelper::sendErrorResponse('You are not assigned to this meeting.', 403);
            }

            if (!Carbon::parse($meeting->meeting_date)->isSameDay(Carbon::today())) {
                return AppHelper::sendErrorResponse('Meeting attendance can only be recorded on the meeting date.', 422);
            }

            $existingAttendance = MeetingAttendance::query()
                ->where('team_meeting_id', $meeting->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingAttendance) {
                return AppHelper::sendSuccessResponse('Meeting attendance already recorded.', [
                    'attendance' => $this->meetingAttendancePayload($existingAttendance),
                    'already_joined' => true,
                ]);
            }

            $attendance = MeetingAttendance::query()->create([
                'team_meeting_id' => $meeting->id,
                'user_id' => $userId,
                'checked_in_at' => Carbon::now(),
                'scan_type' => 'qr',
                'qr_payload' => $validated['qr_payload'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'device_id' => $validated['device_id'] ?? null,
            ]);

            return AppHelper::sendSuccessResponse('Meeting attendance recorded successfully.', [
                'attendance' => $this->meetingAttendancePayload($attendance),
                'already_joined' => false,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    private function meetingIdFromQrPayload(string $payload): ?int
    {
        $payload = trim($payload);

        if (is_numeric($payload)) {
            return (int) $payload;
        }

        if (preg_match('/^meeting:(\d+)$/i', $payload, $matches)) {
            return (int) $matches[1];
        }

        $decoded = json_decode($payload, true);
        if (is_array($decoded)) {
            $meetingId = $decoded['meeting_id'] ?? $decoded['team_meeting_id'] ?? null;
            return is_numeric($meetingId) ? (int) $meetingId : null;
        }

        return null;
    }

    private function qrPayloadForMeeting(int $meetingId): string
    {
        return 'meeting:' . $meetingId;
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

