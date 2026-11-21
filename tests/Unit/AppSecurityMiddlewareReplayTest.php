<?php

namespace Tests\Unit;

use App\Http\Middleware\VerifyAppSecurityAccess;
use App\Models\User;
use App\Modules\AppSecurity\Services\SecuritySignatureService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityMiddlewareReplayTest extends TestCase
{
    private mixed $privateKey;
    private User $user;
    private string $deviceUuid = '23d3f9ad-349d-4dd3-909a-92ff71c10d3b';
    private string $sessionUuid = '6e255587-0f28-44a0-a766-0b50339097ce';
    private string $sessionToken = 'test-session-token-that-is-long-enough';
    private string $signingSha256 = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.enabled' => true,
            'app_security.package_name' => 'com.kneayerng.hrms',
            'app_security.nonce_ttl_seconds' => 300,
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::connection('sqlite')->create('app_security_releases', function (Blueprint $table): void {
            $table->id();
            $table->string('package_name');
            $table->unsignedBigInteger('version_code');
            $table->string('release_status');
            $table->string('signing_sha256');
            $table->boolean('is_mandatory')->default(false);
            $table->unsignedBigInteger('minimum_supported_version')->default(0);
        });
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('user_id');
            $table->string('device_status');
            $table->longText('public_key');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->string('package_name');
            $table->string('signing_sha256');
            $table->unsignedBigInteger('version_code');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_block_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('target_type');
            $table->string('target_value');
            $table->boolean('is_active');
            $table->timestamp('expires_at')->nullable();
        });
        Schema::connection('sqlite')->create('app_security_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('user_id');
            $table->string('session_token_sha256');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_nonces', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->uuid('session_uuid');
            $table->string('nonce_sha256');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at');
            $table->timestamps();
            $table->unique(['session_uuid', 'nonce_sha256']);
        });
        Schema::connection('sqlite')->create('app_security_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->string('severity');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->uuid('request_uuid')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_integrity_checks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('signal');
            $table->string('severity');
            $table->unsignedTinyInteger('risk_points');
            $table->text('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_risk_scores', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->unsignedTinyInteger('risk_score');
            $table->string('risk_level');
            $table->string('policy_version');
            $table->text('reasons')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
        });

        $this->privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        self::assertNotFalse($this->privateKey);
        $details = openssl_pkey_get_details($this->privateKey);
        $publicDer = preg_replace(
            '/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/',
            '',
            $details['key']
        );
        DB::table('users')->insert(['id' => 1]);
        $this->user = User::query()->findOrFail(1);
        DB::table('app_security_releases')->insert([
            'package_name' => 'com.kneayerng.hrms',
            'version_code' => 1,
            'release_status' => 'active',
            'signing_sha256' => $this->signingSha256,
            'is_mandatory' => false,
            'minimum_supported_version' => 0,
        ]);
        DB::table('app_registered_devices')->insert([
            'uuid' => $this->deviceUuid,
            'user_id' => $this->user->id,
            'device_status' => 'approved',
            'package_name' => 'com.kneayerng.hrms',
            'signing_sha256' => $this->signingSha256,
            'version_code' => 1,
            'public_key' => $publicDer,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('app_security_sessions')->insert([
            'uuid' => $this->sessionUuid,
            'device_id' => 1,
            'user_id' => $this->user->id,
            'session_token_sha256' => hash('sha256', $this->sessionToken),
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function test_valid_request_nonce_is_consumed_once_and_replay_is_rejected(): void
    {
        $nonce = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $timestamp = (string) now()->timestamp;
        $request = $this->signedRequest('{"value":1}', null, $nonce, $timestamp);
        $response = (new VerifyAppSecurityAccess())->handle(
            $request,
            fn () => response()->json(['ok' => true]),
        );
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, DB::table('app_security_nonces')->count());

        $replay = $this->signedRequest('{"value":1}', null, $nonce, $timestamp);
        $response = (new VerifyAppSecurityAccess())->handle(
            $replay,
            fn () => response()->json(['ok' => true]),
        );
        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('Request nonce has already been used.', $response->getData(true)['message']);
        $this->assertSame(1, DB::table('app_security_nonces')->count());
        $this->assertSame('request_nonce_replayed', DB::table('app_security_logs')->value('event_type'));
        $this->assertSame(40, DB::table('app_registered_devices')->value('risk_score'));
        $this->assertSame('request_nonce_replayed', DB::table('app_device_integrity_checks')->value('signal'));
    }

    public function test_concurrent_requests_with_the_same_nonce_allow_at_most_one_request(): void
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('Concurrent replay verification requires pcntl.');
        }

        $databasePath = tempnam(sys_get_temp_dir(), 'ky-replay-db-');
        $barrierPath = tempnam(sys_get_temp_dir(), 'ky-replay-start-');
        $resultPaths = [
            tempnam(sys_get_temp_dir(), 'ky-replay-result-'),
            tempnam(sys_get_temp_dir(), 'ky-replay-result-'),
        ];
        $processIds = [];

        try {
            unlink($databasePath);
            DB::connection('sqlite')->getPdo()->exec(
                "VACUUM main INTO '" . str_replace("'", "''", $databasePath) . "'"
            );
            config(['database.connections.sqlite.database' => $databasePath]);
            DB::purge('sqlite');
            DB::reconnect('sqlite');

            $nonce = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
            $timestamp = (string) now()->timestamp;

            foreach ($resultPaths as $resultPath) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    $this->fail('Could not fork a replay test worker.');
                }
                if ($pid === 0) {
                    try {
                        DB::purge('sqlite');
                        DB::reconnect('sqlite');
                        $deadline = microtime(true) + 10;
                        while (!file_exists($barrierPath) && microtime(true) < $deadline) {
                            usleep(1000);
                        }
                        if (!file_exists($barrierPath)) {
                            file_put_contents($resultPath, 'barrier_timeout');
                            exit(0);
                        }

                        $request = $this->signedRequest('{}', null, $nonce, $timestamp);
                        $response = (new VerifyAppSecurityAccess())->handle(
                            $request,
                            fn () => response()->json(['ok' => true]),
                        );
                        file_put_contents($resultPath, (string) $response->getStatusCode());
                        exit(0);
                    } catch (\Throwable $exception) {
                        file_put_contents($resultPath, 'exception:' . $exception->getMessage());
                        exit(1);
                    }
                }
                $processIds[] = $pid;
            }

            touch($barrierPath);
            foreach ($processIds as $pid) {
                pcntl_waitpid($pid, $status);
                $this->assertTrue(pcntl_wifexited($status), 'Replay worker did not exit normally.');
            }

            DB::purge('sqlite');
            DB::reconnect('sqlite');
            $statuses = array_map(static fn (string $path): string => (string) file_get_contents($path), $resultPaths);
            $allowedCount = count(array_filter($statuses, static fn (string $status): bool => $status === '200'));
            $this->assertSame(1, $allowedCount, 'Exactly one concurrent request must reach the protected handler.');
            $this->assertContains($statuses[0], ['200', '409', '503']);
            $this->assertContains($statuses[1], ['200', '409', '503']);
            $this->assertSame(1, DB::table('app_security_nonces')->count());
        } finally {
            foreach ($processIds as $pid) {
                pcntl_waitpid($pid, $status, WNOHANG);
            }
            DB::purge('sqlite');
            config(['database.connections.sqlite.database' => ':memory:']);
            foreach (array_merge([$databasePath, $barrierPath], $resultPaths) as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_protected_requests_reject_devices_without_the_configured_attestation_os_claims(): void
    {
        config(['app_security.minimum_attested_android_os_version' => 130000]);
        $request = $this->signedRequest('{}');
        $continued = false;

        $response = (new VerifyAppSecurityAccess())->handle($request, function () use (&$continued) {
            $continued = true;
            return response()->json(['ok' => true]);
        });

        $this->assertSame(428, $response->getStatusCode());
        $this->assertFalse($continued);
        $this->assertSame('attestation_os_policy_failed', DB::table('app_security_logs')->orderByDesc('id')->value('event_type'));
    }

    public function test_modified_body_fails_signature_verification_before_nonce_consumption(): void
    {
        $request = $this->signedRequest('{"value":2}', '{"value":1}');
        $response = (new VerifyAppSecurityAccess())->handle(
            $request,
            fn () => response()->json(['ok' => true]),
        );
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Request signature verification failed.', $response->getData(true)['message']);
        $this->assertSame(0, DB::table('app_security_nonces')->count());
        $this->assertSame('request_signature_invalid', DB::table('app_security_logs')->value('event_type'));
        $this->assertSame(100, DB::table('app_registered_devices')->value('risk_score'));
    }

    private function signedRequest(
        string $body,
        ?string $signedBody = null,
        ?string $nonceValue = null,
        ?string $timestampValue = null,
    ): Request {
        $timestamp = $timestampValue ?? (string) now()->timestamp;
        $nonce = $nonceValue ?? rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $uri = '/api/private?b=2&a=1';
        $canonical = app(SecuritySignatureService::class)->canonicalRequest(
            'POST', $uri, $signedBody ?? $body, $timestamp, $nonce, $this->deviceUuid, $this->sessionUuid,
        );
        openssl_sign($canonical, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $request = Request::create($uri, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);
        $request->setUserResolver(fn () => $this->user);
        $request->headers->set('X-KY-Device', $this->deviceUuid);
        $request->headers->set('X-KY-Session', $this->sessionUuid);
        $request->headers->set('X-KY-Session-Token', $this->sessionToken);
        $request->headers->set('X-KY-Timestamp', $timestamp);
        $request->headers->set('X-KY-Nonce', $nonce);
        $request->headers->set('X-KY-Signature', $encodedSignature);
        return $request;
    }
}
