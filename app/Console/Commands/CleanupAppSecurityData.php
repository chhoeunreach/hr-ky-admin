<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupAppSecurityData extends Command
{
    protected $signature = 'app-security:cleanup {--dry-run : Report eligible rows without deleting them}';
    protected $description = 'Expire replay and challenge records, erase expired attestation evidence, and age out bounded telemetry';

    public function handle(): int
    {
        if (!Schema::hasTable('app_security_nonces')
            && !Schema::hasTable('app_device_attestations')
            && !Schema::hasTable('app_security_refresh_token_history')) {
            $this->warn('App Security tables are not installed.');
            return self::SUCCESS;
        }

        $cutoffs = [
            'app_security_nonces' => ['expires_at', now()],
            'app_device_challenges' => ['expires_at', now()],
            'app_security_refresh_token_history' => ['expires_at', now()],
            'app_device_integrity_checks' => ['occurred_at', now()->subDays(180)],
            'app_device_risk_scores' => ['calculated_at', now()->subDays(365)],
            'app_security_logs' => ['occurred_at', now()->subDays(365)],
            'app_device_intelligence' => ['observed_date', now()->subDays(365)->toDateString()],
        ];

        $total = 0;
        foreach ($cutoffs as $table => [$column, $cutoff]) {
            if (!Schema::hasTable($table)) continue;
            $query = DB::table($table)->where($column, '<', $cutoff);
            $count = (clone $query)->count();
            $total += $count;
            $this->line("{$table}: {$count} eligible rows");
            if (!$this->option('dry-run') && $count > 0) {
                $query->orderBy($column)->chunkById(1000, function ($rows) use ($table): void {
                    DB::table($table)->whereIn('id', $rows->pluck('id'))->delete();
                });
            }
        }

        $evidenceCount = 0;
        if (Schema::hasTable('app_device_attestations') && Schema::hasColumn('app_device_attestations', 'evidence_expires_at')) {
            $evidence = DB::table('app_device_attestations')
                ->whereNotNull('evidence_encrypted')
                ->whereNotNull('evidence_expires_at')
                ->where('evidence_expires_at', '<=', now());
            $evidenceCount = (clone $evidence)->count();
            $this->line("app_device_attestations encrypted evidence: {$evidenceCount} eligible records (verification summaries are preserved)");
            if (!$this->option('dry-run') && $evidenceCount > 0) {
                $evidence->orderBy('id')->chunkById(500, function ($rows): void {
                    DB::table('app_device_attestations')
                        ->whereIn('id', $rows->pluck('id'))
                        ->whereNotNull('evidence_encrypted')
                        ->update(['evidence_encrypted' => null, 'updated_at' => now()]);
                });
            }
        }
        $total += $evidenceCount;

        $this->info($this->option('dry-run') ? "{$total} rows/records eligible; nothing deleted." : "Removed or erased {$total} expired or aged records.");
        return self::SUCCESS;
    }
}
