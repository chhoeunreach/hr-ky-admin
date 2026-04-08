<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Models\Resignation;
use App\Models\User;
use Illuminate\Console\Command;

class DisableResignedUser extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:disable-user';

    protected $description = 'Disable resigned employees of company.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');
        $resignationData = Resignation::query()
            ->where('status', ResignationStatusEnum::approved->value)
            ->whereRaw('DATE_ADD(last_working_day, INTERVAL 1 DAY) = ?', [$todayDate])
            ->get();

        if (!empty($resignationData)) {

            foreach ($resignationData as $resignation) {

                $userDetail = User::where('id', $resignation->employee_id)->first();

                $userDetail->tokens()->delete();

                $userDetail->update([
                    'is_active' => false,
                    'logout_status' => true,
                    'fcm_token' => null,
                    'online_status' => false
                ]);

            }

        }
        $this->info('Resigned employee account deactivated!');
    }
}
