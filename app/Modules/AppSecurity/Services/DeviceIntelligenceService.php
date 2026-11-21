<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Stores a privacy-minimized daily summary of successfully authorized traffic. */
class DeviceIntelligenceService
{
    public function recordAuthorizedRequest(RegisteredDevice $device, Request $request): void
    {
        if (!Schema::hasTable('app_device_intelligence')) return;

        $date = now()->toDateString();
        $ip = $request->ip();
        $ipHash = $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null;

        DB::transaction(function () use ($device, $date, $ipHash): void {
            $row = DB::table('app_device_intelligence')
                ->where('device_id', $device->id)
                ->where('observed_date', $date)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('app_device_intelligence')->insert([
                    'device_id' => $device->id,
                    'observed_date' => $date,
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                    'authorized_request_count' => 1,
                    'network_change_count' => 0,
                    'last_ip_hash' => $ipHash,
                    'version_code' => $device->version_code,
                    'android_sdk' => $device->android_sdk,
                    'observations' => json_encode(['network_change' => false], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return;
            }

            $changed = $ipHash !== null && $row->last_ip_hash !== null && !hash_equals($row->last_ip_hash, $ipHash);
            $observation = ['network_change' => $changed];
            DB::table('app_device_intelligence')->where('id', $row->id)->update([
                'last_seen_at' => now(),
                'authorized_request_count' => DB::raw('authorized_request_count + 1'),
                'network_change_count' => DB::raw('network_change_count + '.($changed ? '1' : '0')),
                'last_ip_hash' => $ipHash ?? $row->last_ip_hash,
                'version_code' => $device->version_code,
                'android_sdk' => $device->android_sdk,
                'observations' => json_encode($observation, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
        }, 3);
    }
}
