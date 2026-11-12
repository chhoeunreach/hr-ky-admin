<?php
namespace App\Services\TeamMeeting;

use App\Models\MeetingAttendance;
use App\Models\TeamMeeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MeetingCheckInService
{
    public function qrPayload(TeamMeeting $meeting): string
    {
        return 'meeting-qr:'.Crypt::encryptString(json_encode([
            'meeting_id' => $meeting->id,
            'version' => (int) $meeting->attendance_qr_version,
            'purpose' => 'meeting-check-in',
        ], JSON_THROW_ON_ERROR));
    }

    public function window(TeamMeeting $meeting): array
    {
        $date = Carbon::parse($meeting->meeting_date);
        return [
            $meeting->attendance_opens_at ?? $date->copy()->startOfDay(),
            $meeting->attendance_closes_at ?? $date->copy()->endOfDay(),
        ];
    }

    public function status(TeamMeeting $meeting): string
    {
        if (!$meeting->attendance_enabled) return 'disabled';
        if ($meeting->meeting_published_at && Carbon::parse($meeting->meeting_published_at)->isFuture()) return 'upcoming';
        [$opens, $closes] = $this->window($meeting);
        if (now()->lt($opens)) return 'upcoming';
        return now()->gt($closes) ? 'closed' : 'open';
    }

    public function scan(string $payload, $user): array
    {
        try {
            if (!str_starts_with($payload, 'meeting-qr:')) throw new \RuntimeException();
            $data = json_decode(Crypt::decryptString(substr($payload, 11)), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || ($data['purpose'] ?? '') !== 'meeting-check-in'
                || !is_int($data['meeting_id'] ?? null) || $data['meeting_id'] < 1
                || !is_int($data['version'] ?? null) || $data['version'] < 1) {
                throw new \RuntimeException();
            }
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['qr_payload' => 'Invalid meeting QR code. Ask the organizer for the current QR.']);
        }

        return DB::transaction(function () use ($data, $user) {
            // Serialize scans and QR/settings changes on the same meeting.
            $meeting = TeamMeeting::query()->where('company_id', $user->company_id)
                ->lockForUpdate()->findOrFail($data['meeting_id']);
            abort_unless($meeting->teamMeetingParticipator()->where('meeting_participator_id', $user->id)->exists(),
                403, 'You are not assigned to this meeting.');
            if ((int) $data['version'] !== (int) $meeting->attendance_qr_version) {
                throw ValidationException::withMessages(['qr_payload' => 'This QR has been replaced. Scan the current meeting QR.']);
            }
            $existing = $meeting->meetingAttendances()->where('user_id', $user->id)->first();
            if ($existing) return ['attendance' => $existing, 'already_joined' => true];
            if ($this->status($meeting) !== 'open') {
                throw ValidationException::withMessages(['qr_payload' => 'Check-in is not open for this meeting.']);
            }
            $attendance = MeetingAttendance::create([
                'team_meeting_id' => $meeting->id, 'user_id' => $user->id,
                'checked_in_at' => now(), 'scan_type' => 'qr',
            ]);
            return ['attendance' => $attendance, 'already_joined' => false];
        });
    }
}
