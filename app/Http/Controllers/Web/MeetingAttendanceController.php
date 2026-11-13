<?php
namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\TeamMeeting;
use App\Services\TeamMeeting\MeetingCheckInService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeetingAttendanceController extends Controller
{
    use CustomAuthorizesRequests;

    private function meeting($id): TeamMeeting
    {
        return TeamMeeting::query()->where('company_id', AppHelper::getAuthUserCompanyId())
            ->when(!auth('admin')->check() && !AppHelper::canAccessAllBranches() && auth()->user()?->branch_id,
                fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->findOrFail($id);
    }

    public function show($id, MeetingCheckInService $service)
    {
        $this->authorize('show_team_meeting');
        $meeting = $this->meeting($id);
        $meeting->load(['teamMeetingParticipator.participator', 'meetingAttendances.user']);
        $attendances = $meeting->meetingAttendances->keyBy('user_id');
        $qrPayload = $service->qrPayload($meeting);
        $status = $service->status($meeting);
        [$opensAt, $closesAt] = $service->window($meeting);
        return view('admin.teamMeeting.attendance', compact('meeting', 'attendances', 'qrPayload', 'status', 'opensAt', 'closesAt'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit_team_meeting');
        $validated = $request->validate([
            'attendance_enabled' => ['required', 'boolean'],
            'attendance_opens_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'attendance_closes_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ]);
        DB::transaction(function () use ($id, $validated) {
            $meeting = $this->meeting($id);
            $meeting = TeamMeeting::query()->lockForUpdate()->findOrFail($meeting->id);
            $opens = $validated['attendance_opens_at'] ?? \Carbon\Carbon::parse($meeting->meeting_date)->startOfDay();
            $closes = $validated['attendance_closes_at'] ?? \Carbon\Carbon::parse($meeting->meeting_date)->endOfDay();
            if (\Carbon\Carbon::parse($closes)->lte(\Carbon\Carbon::parse($opens))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['attendance_closes_at' => 'Closing time must be after opening time.']);
            }
            // Avoid changing the meeting publication timestamp for attendance settings.
            $meeting->forceFill($validated)->saveQuietly();
        });
        return back()->with('success', 'Meeting check-in settings saved.');
    }

    public function rotate($id)
    {
        $this->authorize('edit_team_meeting');
        $meeting = $this->meeting($id);
        // Rotation must not republish the meeting or notify model observers.
        $meeting->incrementQuietly('attendance_qr_version');
        return back()->with('success', 'New QR created. Previously printed codes no longer work.');
    }

    public function joinList($id)
    {
        $this->authorize('show_team_meeting');
        $meeting = $this->meeting($id);
        $joined = $meeting->meetingAttendances()
            ->with('user')
            ->whereHas('user')
            ->orderBy('checked_in_at')
            ->get()
            ->map(function ($attendance) {
                return [
                    'name' => ucfirst($attendance->user->name),
                    'checked_in_at' => $attendance->checked_in_at?->format('Y-m-d H:i:s'),
                ];
            })->values();

        return response()->json([
            'meeting' => [
                'id' => $meeting->id,
                'title' => ucfirst($meeting->title),
                'meeting_date' => AppHelper::formatDateForView($meeting->meeting_date, false),
            ],
            'joined_count' => $joined->count(),
            'invited_count' => $meeting->teamMeetingParticipator()->count(),
            'joined' => $joined,
        ]);
    }

    public function export($id)
    {
        $this->authorize('show_team_meeting');
        $meeting = $this->meeting($id);
        $meeting->load(['teamMeetingParticipator.participator', 'meetingAttendances.user']);
        $attendances = $meeting->meetingAttendances->keyBy('user_id');
        return response()->streamDownload(function () use ($meeting, $attendances) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Employee', 'Status', 'Checked in at']);
            $users = $meeting->teamMeetingParticipator->pluck('participator')->filter()
                ->merge($meeting->meetingAttendances->pluck('user')->filter())->unique('id');
            foreach ($users as $user) {
                $name = preg_match('/^[=+@\-\t\r]/u', $user->name) ? "'".$user->name : $user->name;
                $attendance = $attendances->get($user->id);
                fputcsv($file, [$name, $attendance ? 'Joined' : 'Pending', $attendance?->checked_in_at?->format('Y-m-d H:i:s')]);
            }
            fclose($file);
        }, 'meeting-'.$meeting->id.'-attendance.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
