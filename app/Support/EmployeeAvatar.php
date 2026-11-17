<?php

namespace App\Support;

use App\Models\DCardEmployee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class EmployeeAvatar
{
    private const DEFAULT_PHOTO = 'assets/images/img.png';

    public static function borderColor(int $employeeId): string
    {
        $colors = ['var(--bs-primary, #5546ed)', '#5546ed', '#10b981', '#e11d48', '#0284c7'];
        return $colors[$employeeId % count($colors)];
    }

    public static function loadDCardOverrides(): Collection
    {
        if (! Schema::hasTable('d_card_employees')) {
            return new Collection();
        }

        return DCardEmployee::query()->get()->keyBy('employee_code');
    }

    public static function url(User $user, ?Collection $dCardOverrides = null): string
    {
        $employeeCode = trim((string) ($user->employee_code ?: sprintf('EMP-%04d', $user->id ?? 0)));

        if ($employeeCode !== '') {
            $override = $dCardOverrides
                ? $dCardOverrides->get($employeeCode)
                : (Schema::hasTable('d_card_employees')
                    ? DCardEmployee::where('employee_code', $employeeCode)->first()
                    : null);

            if ($override?->profile_photo_url) {
                return $override->profile_photo_url;
            }
        }

        return $user->avatar
            ? asset(User::AVATAR_UPLOAD_PATH . $user->avatar)
            : asset(self::DEFAULT_PHOTO);
    }
}
