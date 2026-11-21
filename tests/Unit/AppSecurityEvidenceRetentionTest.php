<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityEvidenceRetentionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.attestation_evidence_retention_days' => 90,
        ]);
        DB::purge('sqlite');
    }

    public function test_expiry_migration_backfills_existing_evidence_and_rolls_back_without_erasing_it(): void
    {
        Schema::connection('sqlite')->create('app_device_attestations', function (Blueprint $table): void {
            $table->id();
            $table->text('evidence_encrypted')->nullable();
            $table->string('verification_status')->default('pending');
            $table->json('verification_summary')->nullable();
            $table->timestamps();
        });
        $createdAt = now()->subDays(100)->startOfSecond();
        DB::table('app_device_attestations')->insert([
            'evidence_encrypted' => 'encrypted-chain',
            'verification_status' => 'verified',
            'verification_summary' => json_encode(['reason' => 'verified'], JSON_THROW_ON_ERROR),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $migration = require database_path('migrations/2026_11_20_120000_add_app_attestation_evidence_expiry.php');
        $migration->up();
        $row = DB::table('app_device_attestations')->first();

        $this->assertTrue(Schema::hasColumn('app_device_attestations', 'evidence_expires_at'));
        $this->assertTrue(Schema::hasIndex('app_device_attestations', 'appsec_attestation_evidence_expiry_index'));
        $this->assertSame($createdAt->copy()->addDays(90)->toDateTimeString(), $row->evidence_expires_at);

        $migration->down();
        $row = DB::table('app_device_attestations')->first();
        $this->assertFalse(Schema::hasColumn('app_device_attestations', 'evidence_expires_at'));
        $this->assertSame('encrypted-chain', $row->evidence_encrypted);
        $this->assertSame('verified', $row->verification_status);
    }

    public function test_cleanup_dry_run_preserves_expired_evidence_and_cleanup_erases_only_ciphertext(): void
    {
        Schema::connection('sqlite')->create('app_security_nonces', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('expires_at');
        });
        Schema::connection('sqlite')->create('app_security_refresh_token_history', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('session_id'); $table->char('token_sha256', 64)->unique();
            $table->timestamp('expires_at'); $table->timestamp('reuse_detected_at')->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_attestations', function (Blueprint $table): void {
            $table->id();
            $table->text('evidence_encrypted')->nullable();
            $table->timestamp('evidence_expires_at')->nullable();
            $table->string('verification_status')->default('pending');
            $table->json('verification_summary')->nullable();
            $table->timestamps();
        });
        $expiredId = DB::table('app_device_attestations')->insertGetId([
            'evidence_encrypted' => 'expired-ciphertext',
            'evidence_expires_at' => now()->subSecond(),
            'verification_status' => 'verified',
            'verification_summary' => json_encode(['reason' => 'chain_verified'], JSON_THROW_ON_ERROR),
            'created_at' => now()->subDays(91), 'updated_at' => now()->subDays(91),
        ]);
        $futureId = DB::table('app_device_attestations')->insertGetId([
            'evidence_encrypted' => 'current-ciphertext',
            'evidence_expires_at' => now()->addDay(),
            'verification_status' => 'verified',
            'verification_summary' => json_encode(['reason' => 'chain_verified'], JSON_THROW_ON_ERROR),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $expiredHashId = DB::table('app_security_refresh_token_history')->insertGetId([
            'session_id' => 1, 'token_sha256' => str_repeat('c', 64), 'expires_at' => now()->subSecond(),
            'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2),
        ]);
        $futureHashId = DB::table('app_security_refresh_token_history')->insertGetId([
            'session_id' => 1, 'token_sha256' => str_repeat('d', 64), 'expires_at' => now()->addDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Artisan::call('app-security:cleanup', ['--dry-run' => true]);
        $this->assertSame('expired-ciphertext', DB::table('app_device_attestations')->where('id', $expiredId)->value('evidence_encrypted'));
        $this->assertDatabaseHas('app_security_refresh_token_history', ['id' => $expiredHashId]);

        Artisan::call('app-security:cleanup');
        $expired = DB::table('app_device_attestations')->where('id', $expiredId)->first();
        $future = DB::table('app_device_attestations')->where('id', $futureId)->first();
        $this->assertNull($expired->evidence_encrypted);
        $this->assertSame('verified', $expired->verification_status);
        $this->assertSame(['reason' => 'chain_verified'], json_decode($expired->verification_summary, true));
        $this->assertSame('current-ciphertext', $future->evidence_encrypted);
        $this->assertDatabaseMissing('app_security_refresh_token_history', ['id' => $expiredHashId]);
        $this->assertDatabaseHas('app_security_refresh_token_history', ['id' => $futureHashId]);
    }
}
