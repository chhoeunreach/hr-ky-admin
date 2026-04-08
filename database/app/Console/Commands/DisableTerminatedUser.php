<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Enum\TerminationStatusEnum;
use App\Models\Resignation;
use App\Models\Termination;
use App\Models\User;
use Illuminate\Console\Command;

class DisableTerminatedUser extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:disable-terminated-user';

    protected $description = 'Disable terminated employees of company.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $terminationData = Termination::query()
            ->where('status', TerminationStatusEnum::approved->value)
            ->whereRaw('DATE_ADD(termination_date, INTERVAL 1 DAY) = ?', [$todayDate])
            ->get();

        if (!empty($terminationData)) {

            foreach ($terminationData as $termination) {

                $userDetail = User::where('id', $termination->employee_id)->first();

                $userDetail->tokens()->delete();

                $userDetail->update([
                    'is_active' => false,
                    'logout_status' => true,
                    'fcm_token' => null,
                    'online_status' => false
                ]);

            }

        }
        $this->info('Terminated employee account deactivated!');
    }
}
