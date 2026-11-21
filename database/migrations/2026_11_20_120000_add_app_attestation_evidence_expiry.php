<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'appsec_attestation_evidence_expiry_index';

    public function up(): void
    {
        if (!Schema::hasTable('app_device_attestations')) return;

        if (!Schema::hasColumn('app_device_attestations', 'evidence_expires_at')) {
            Schema::table('app_device_attestations', function (Blueprint $table): void {
                $table->timestamp('evidence_expires_at')->nullable();
            });
        }
        if (!Schema::hasIndex('app_device_attestations', self::INDEX)) {
            Schema::table('app_device_attestations', function (Blueprint $table): void {
                $table->index('evidence_expires_at', self::INDEX);
            });
        }

        $retentionDays = min(3650, max(1, (int) config('app_security.attestation_evidence_retention_days', 90)));
        DB::table('app_device_attestations')
            ->whereNotNull('evidence_encrypted')
            ->whereNull('evidence_expires_at')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($retentionDays): void {
                foreach ($rows as $row) {
                    $createdAt = $row->created_at ? Carbon::parse($row->created_at) : now();
                    DB::table('app_device_attestations')->where('id', $row->id)->update([
                        'evidence_expires_at' => $createdAt->copy()->addDays($retentionDays),
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_device_attestations')
            || !Schema::hasColumn('app_device_attestations', 'evidence_expires_at')) return;

        if (Schema::hasIndex('app_device_attestations', self::INDEX)) {
            Schema::table('app_device_attestations', function (Blueprint $table): void {
                $table->dropIndex(self::INDEX);
            });
        }
        Schema::table('app_device_attestations', function (Blueprint $table): void {
            $table->dropColumn('evidence_expires_at');
        });
    }
};
