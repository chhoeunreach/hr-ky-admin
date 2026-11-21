<?php

namespace App\Console\Commands;

use App\Modules\DailyRecords\RecordEvents;
use App\Modules\DailyRecords\RecordTime;
use App\Modules\DailyRecords\SendRecordNotification;
use App\Modules\DailyRecords\Tasks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ProcessDailyRecords extends Command
{
    protected $signature = 'daily-records:process';
    protected $description = 'Generate recurring employee tasks and deliver Daily Records notifications';

    public function handle(Tasks $tasks, RecordEvents $events): int
    {
        $now = RecordTime::now();
        DB::table('daily_task_series')->where('next_at', '<=', $now)->orderBy('next_at')->limit(100)
            ->pluck('id')->each(function (int $id) use ($tasks) {
                try {
                    $tasks->generate($id);
                } catch (\Throwable $exception) {
                    report($exception);
                }
            });

        // Process due alerts in bounded batches. Pending outbox keys are unique per deadline revision.
        foreach (['reminder' => 'remind_at', 'overdue' => 'due_at'] as $type => $column) {
            DB::table('daily_task_assignments as a')->select('a.*')
                ->whereIn('a.status', Tasks::INCOMPLETE)->whereNull('a.completed_at')
                ->whereNull('a.archived_at')->whereNull('a.superseded_at')
                ->whereNull($type === 'reminder' ? 'a.reminder_sent_at' : 'a.overdue_sent_at')
                ->where("a.$column", $type === 'reminder' ? '<=' : '<', $now)
                ->when($type === 'reminder', fn ($query) => $query->where('a.due_at', '>=', $now))
                ->orderBy("a.$column")->limit(250)->pluck('a.id')
                ->each(fn (int $id) => $this->queueAlert($id, $events, $type, $column, $now));
        }

        DB::table('daily_record_notifications')->whereNull('delivered_at')->orderBy('id')->limit(500)->pluck('id')
            ->each(fn (int $id) => SendRecordNotification::dispatch($id));
        return self::SUCCESS;
    }

    private function queueAlert(int $id, RecordEvents $events, string $type, string $column, string $now): void
    {
        DB::transaction(function () use ($id, $events, $type, $column, $now) {
            $flag = $type === 'reminder' ? 'reminder_sent_at' : 'overdue_sent_at';
            $row = DB::table('daily_task_assignments')->where('id', $id)->whereIn('status', Tasks::INCOMPLETE)
                ->whereNull('completed_at')->whereNull('archived_at')->whereNull('superseded_at')->whereNull($flag)
                ->where($column, $type === 'reminder' ? '<=' : '<', $now)
                ->when($type === 'reminder', fn ($query) => $query->where('due_at', '>=', $now))
                ->lockForUpdate()->first();
            if (!$row) {
                return;
            }
            $key = "$type:$row->id:$row->deadline_version";
            if ($type === 'reminder') {
                $events->notify($row->employee_id, $key, 'Task deadline approaching', 'An assignment is due soon. Open My Tasks for details.');
            } else {
                $events->notifyManagers($row, $key, 'Employee task overdue', 'An assignment has passed its deadline. Open the Task & Report Center for details.');
            }
            DB::table('daily_task_assignments')->where('id', $id)->update([$flag => $now]);
        }, 3);
    }
}
