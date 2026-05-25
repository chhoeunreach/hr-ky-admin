<?php

namespace App\Helpers;

use App\Models\GeneralSetting;

class MobileChatHelper
{
    public const MODE_ADMIN_ONLY = 'admin_only';
    public const MODE_ALL_EMPLOYEES = 'all_employees';

    public static function getScope(): string
    {
        $scope = (string) (GeneralSetting::where('key', 'mobile_chat_scope')->value('value') ?: '');

        return in_array($scope, [self::MODE_ADMIN_ONLY, self::MODE_ALL_EMPLOYEES], true)
            ? $scope
            : self::MODE_ALL_EMPLOYEES;
    }

    public static function isAdminOnlyMode(): bool
    {
        return self::getScope() === self::MODE_ADMIN_ONLY;
    }

    public static function scopeOptions(): array
    {
        return [
            self::MODE_ADMIN_ONLY => 'Chat only with admin',
            self::MODE_ALL_EMPLOYEES => 'Chat with each employee',
        ];
    }
}
