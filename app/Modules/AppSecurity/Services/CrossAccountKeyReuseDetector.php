<?php

namespace App\Modules\AppSecurity\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Detects a device signing key bound to more than one account without exposing linked account identities. */
class CrossAccountKeyReuseDetector
{
    public function inspect(string $publicKeyFingerprint, int $currentUserId): array
    {
        if (!Schema::hasTable('app_registered_devices')) {
            return ['detected' => false, 'linked_account_count' => 0];
        }

        $linkedAccountCount = (int) DB::table('app_registered_devices')
            ->where('public_key_fingerprint', $publicKeyFingerprint)
            ->where('user_id', '<>', $currentUserId)
            ->distinct()
            ->count('user_id');

        return [
            'detected' => $linkedAccountCount > 0,
            'linked_account_count' => $linkedAccountCount,
        ];
    }
}
