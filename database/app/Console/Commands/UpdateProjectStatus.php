<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Enum\TerminationStatusEnum;
use App\Models\Project;
use App\Models\Resignation;
use App\Models\Task;
use App\Models\Termination;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateProjectStatus extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:update-project-status';

    protected $description = 'Update Project Status.';

    public function handle()
    {
        $now = Carbon::today();

        Project::where(function ($query) use ($now) {
            $query->where('status', '!=', 'cancelled')
                ->where('start_date', '<', $now) // Case 1: Start date is less than today
            ->orWhere(function ($subQuery) use ($now) { // Case 2:deadline is less than today and not null
                $subQuery->where('deadline', '<', $now);
            });
        })
            ->update(['status' => 'completed','is_active'=>0]);


        Project::where(function ($query) use ($now) {

            $query->where('status', '!=', 'cancelled')
                ->where('start_date', '=', $now) // Case 1: Start date is today
            ->orWhere(function ($subQuery) use ($now) { // Case 2: Start date is past, deadline is not null and >= today
                $subQuery->where('start_date', '<', $now)
                    ->where('deadline', '>=', $now);
            });
        })
            ->update(['status' => 'in_progress','is_active'=>1]);

        Project::where('start_date', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->update(['status' => 'not_started','is_active'=>0]);

        Project::where('status', '=', 'cancelled')
            ->update(['is_active'=>0]);


        Task::where(function ($query) use ($now) {
            $query->where('status', '!=', 'cancelled')
                ->where('start_date', '<', $now) // Case 1: Start date is less than today
                ->orWhere(function ($subQuery) use ($now) { // Case 2:end_date is less than today and not null
                    $subQuery->where('end_date', '<', $now);
                });
        })
            ->update(['status' => 'completed','is_active'=>0]);


        Task::where(function ($query) use ($now) {

            $query->where('status', '!=', 'cancelled')
                ->where('start_date', '=', $now) // Case 1: Start date is today
                ->orWhere(function ($subQuery) use ($now) { // Case 2: Start date is past, end_date is not null and >= today
                    $subQuery->where('start_date', '<', $now)
                        ->where('end_date', '>=', $now);
                });
        })
            ->update(['status' => 'in_progress','is_active'=>1]);

        Task::where('start_date', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->update(['status' => 'not_started','is_active'=>0]);

        Task::where('status', '=', 'cancelled')
            ->update(['is_active'=>0]);

        $this->info('Project and Task Status Updated Successfully!');
    }
}
