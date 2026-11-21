<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RecordEvents
{
    public function __construct(private RecordAccess $access) {}

    public function add(Admin|User $actor, string $action, ?int $assignment, ?int $entry = null,
        array $details = [], ?string $status = null, ?bool $onTime = null): int
    {
        return DB::table('daily_record_events')->insertGetId([
            'actor_id' => $actor->id, 'actor_type' => $this->access->type($actor),
            'assignment_id' => $assignment, 'entry_id' => $entry, 'action' => $action,
            'details' => json_encode($details, JSON_THROW_ON_ERROR), 'status' => $status, 'on_time' => $onTime,
            'created_at' => RecordTime::now(),
        ]);
    }

    public function notify(int $user, string $key, string $title, string $message): void
    {
        DB::table('daily_record_notifications')->insertOrIgnore([
            'event_key' => "$key:$user", 'user_id' => $user, 'title' => $title,
            'message' => $message, 'created_at' => RecordTime::now(),
        ]);
    }

    public function notifyManagers(object $record, string $key, string $title, string $message, bool $restricted = false): void
    {
        $employee = DB::table('users')->where('id', $record->employee_id)->first(['supervisor_id', 'department_id']);
        $head = DB::table('departments')->where('id', $employee?->department_id)->value('dept_head_id');
        $ids = array_filter([$employee?->supervisor_id, $head]);
        if (!empty($record->task_id)) {
            $owner = DB::table('daily_tasks')->where('id', $record->task_id)->where('actor_type', 'employee')->value('actor_id');
            if ($owner) {
                $ids[] = $owner;
            }
        }

        foreach (User::withoutGlobalScope('branch')->whereIn('id', array_unique($ids))->get() as $manager) {
            if (!$this->access->manages($manager) || ($restricted && !$this->access->allows($manager, RecordAccess::SENSITIVE))) {
                continue;
            }
            $isTask = isset($record->task_id);
            $table = $isTask ? 'daily_task_assignments' : 'daily_entries';
            if ($this->access->constrain(DB::table("$table as r"), $manager, 'r', true)->where('r.id', $record->id)->exists()) {
                $this->notify($manager->id, $key, $title, $message);
            }
        }
    }
}
