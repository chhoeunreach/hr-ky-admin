<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_registered_devices')) {
            return;
        }

        $hasGlobalInstallationUnique = Schema::hasIndex(
            'app_registered_devices',
            'app_registered_devices_installation_uuid_unique'
        );
        $hasGlobalFingerprintUnique = Schema::hasIndex(
            'app_registered_devices',
            'app_registered_devices_public_key_fingerprint_unique'
        );
        $hasAccountInstallationUnique = Schema::hasIndex(
            'app_registered_devices',
            'app_registered_devices_user_installation_unique'
        );
        $hasFingerprintIndex = Schema::hasIndex(
            'app_registered_devices',
            'app_registered_devices_public_key_fingerprint_index'
        );

        if (!$hasGlobalInstallationUnique && !$hasGlobalFingerprintUnique
            && $hasAccountInstallationUnique && $hasFingerprintIndex
        ) {
            return;
        }

        Schema::table('app_registered_devices', function (Blueprint $table) use (
            $hasGlobalInstallationUnique,
            $hasGlobalFingerprintUnique,
            $hasAccountInstallationUnique,
            $hasFingerprintIndex
        ): void {
            if ($hasGlobalInstallationUnique) {
                $table->dropUnique('app_registered_devices_installation_uuid_unique');
            }
            if ($hasGlobalFingerprintUnique) {
                $table->dropUnique('app_registered_devices_public_key_fingerprint_unique');
            }
            if (!$hasAccountInstallationUnique) {
                $table->unique(
                    ['user_id', 'installation_uuid'],
                    'app_registered_devices_user_installation_unique'
                );
            }
            if (!$hasFingerprintIndex) {
                $table->index('public_key_fingerprint', 'app_registered_devices_public_key_fingerprint_index');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_registered_devices')) {
            return;
        }

        $hasSharedInstallations = DB::table('app_registered_devices')
            ->select('installation_uuid')
            ->groupBy('installation_uuid')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        if ($hasSharedInstallations) {
            throw new RuntimeException(
                'Cannot restore global installation UUID uniqueness while accounts share an installation. Keep the migration applied or resolve account records first.'
            );
        }

        Schema::table('app_registered_devices', function (Blueprint $table): void {
            $table->dropUnique('app_registered_devices_user_installation_unique');
            $table->dropIndex('app_registered_devices_public_key_fingerprint_index');
            $table->unique('installation_uuid');
            $table->unique('public_key_fingerprint');
        });
    }
};
