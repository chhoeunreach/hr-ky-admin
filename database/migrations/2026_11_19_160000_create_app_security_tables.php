<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_security_schema_migration_objects')) {
            $this->createTracked('app_security_schema_migration_objects', function (Blueprint $table) {
                $table->id();
                $table->string('table_name', 100)->unique();
                $table->timestamps();
            });
        }

        $this->createTracked('app_security_releases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('package_name', 128);
            $table->string('version_name', 80);
            $table->unsignedBigInteger('version_code');
            $table->char('signing_sha256', 64);
            $table->char('apk_sha256', 64);
            $table->unsignedBigInteger('apk_size');
            $table->string('download_path', 1024)->nullable();
            $table->text('release_notes')->nullable();
            $table->unsignedBigInteger('minimum_supported_version')->default(0);
            $table->unsignedSmallInteger('minimum_android_sdk')->default(28);
            $table->unsignedSmallInteger('target_android_sdk')->default(36);
            $table->string('release_status', 24)->default('draft');
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->unique(['package_name', 'version_code']);
            $table->index(['release_status', 'released_at']);
        });

        $this->createTracked('app_registered_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_type', 32)->default('staff');
            $table->uuid('installation_uuid')->unique();
            $table->longText('public_key');
            $table->char('public_key_fingerprint', 64)->unique();
            $table->string('package_name', 128);
            $table->char('signing_sha256', 64);
            $table->string('version_name', 80);
            $table->unsignedBigInteger('version_code');
            $table->unsignedSmallInteger('android_sdk')->nullable();
            $table->string('android_version', 80)->nullable();
            $table->string('device_model', 191)->nullable();
            $table->string('attestation_level', 32)->default('unverified');
            $table->string('device_status', 24)->default('pending');
            $table->string('pre_block_status', 24)->nullable();
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'device_status']);
            $table->index(['device_status', 'last_seen_at']);
            $table->index('package_name');
            $table->index('signing_sha256');
        });

        $this->createTracked('app_device_challenges', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('app_registered_devices')->nullOnDelete();
            $table->string('purpose', 40);
            $table->char('challenge_sha256', 64);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
            $table->index(['purpose', 'expires_at', 'used_at']);
        });

        $this->createTracked('app_device_attestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('app_registered_devices')->cascadeOnDelete();
            $table->foreignId('challenge_id')->nullable()->constrained('app_device_challenges')->nullOnDelete();
            $table->longText('evidence_encrypted')->nullable();
            $table->string('verification_status', 24)->default('pending');
            $table->string('security_level', 32)->nullable();
            $table->json('verification_summary')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['device_id', 'verification_status']);
        });

        $this->createTracked('app_security_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('device_id')->constrained('app_registered_devices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('session_token_sha256', 64)->unique();
            $table->char('refresh_token_sha256', 64)->nullable()->unique();
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 191)->nullable();
            $table->timestamps();
            $table->index(['device_id', 'revoked_at', 'expires_at']);
            $table->index(['user_id', 'revoked_at']);
        });

        $this->createTracked('app_device_integrity_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('app_registered_devices')->nullOnDelete();
            $table->string('signal', 80);
            $table->string('severity', 16)->default('info');
            $table->unsignedTinyInteger('risk_points')->default(0);
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['device_id', 'occurred_at']);
            $table->index(['signal', 'severity', 'occurred_at']);
        });

        $this->createTracked('app_device_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('app_registered_devices')->cascadeOnDelete();
            $table->unsignedTinyInteger('risk_score');
            $table->string('risk_level', 16);
            $table->string('policy_version', 40);
            $table->json('reasons')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['device_id', 'calculated_at']);
            $table->index(['risk_level', 'calculated_at']);
        });

        $this->createTracked('app_security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 80);
            $table->string('severity', 16)->default('info');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('app_registered_devices')->nullOnDelete();
            $table->uuid('request_uuid')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('details')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['event_type', 'occurred_at']);
            $table->index(['device_id', 'occurred_at']);
        });

        $this->createTracked('app_security_block_rules', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 24);
            $table->string('target_value', 160);
            $table->string('reason', 512);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_value', 'is_active'], 'appsec_block_rules_target_idx');
        });

        $this->createTracked('app_security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->json('setting_value');
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        $this->createTracked('app_security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('actor_type', 24)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 80);
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 80)->nullable();
            $table->json('previous_state')->nullable();
            $table->json('new_state')->nullable();
            $table->string('reason', 512)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['action', 'occurred_at']);
            $table->index(['target_type', 'target_id']);
        });

        $this->createTracked('app_security_nonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('app_registered_devices')->cascadeOnDelete();
            $table->uuid('session_uuid');
            $table->char('nonce_sha256', 64);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->useCurrent();
            $table->timestamps();
            $table->unique(['session_uuid', 'nonce_sha256'], 'appsec_nonce_session_hash_unique');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        $trackingTable = 'app_security_schema_migration_objects';
        if (!Schema::hasTable($trackingTable)) {
            return;
        }

        $allowedTables = [
            'app_security_releases', 'app_registered_devices', 'app_device_challenges',
            'app_device_attestations', 'app_security_sessions', 'app_device_integrity_checks',
            'app_device_risk_scores', 'app_security_logs', 'app_security_block_rules',
            'app_security_settings', 'app_security_audit_logs', 'app_security_nonces',
        ];

        foreach (DB::table($trackingTable)->orderByDesc('id')->pluck('table_name') as $tableName) {
            if (in_array($tableName, $allowedTables, true)) {
                Schema::dropIfExists($tableName);
            }
        }

        Schema::dropIfExists($trackingTable);
    }

    private function createTracked(string $tableName, callable $definition): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, $definition);
        DB::table('app_security_schema_migration_objects')->insert([
            'table_name' => $tableName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

};
