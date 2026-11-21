<?php

namespace Tests\Unit;

use App\Http\Middleware\VerifyAppSecurityAccess;
use App\Modules\AppSecurity\Services\SecuritySignatureService;
use Illuminate\Http\Request;
use Tests\TestCase;

class AppSecuritySignatureTest extends TestCase
{
    public function test_p256_device_signature_verifies_and_is_bound_to_exact_request(): void
    {
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        $this->assertNotFalse($privateKey);
        $details = openssl_pkey_get_details($privateKey);
        $publicPem = $details['key'];
        $publicDerBase64 = preg_replace(
            '/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/',
            '',
            $publicPem
        );
        $service = new SecuritySignatureService();
        $canonical = $service->canonicalRequest(
            'post', '/api/private?b=2&a=1', '{"value":1}', '1795077600',
            'qwertyuiopasdfghjklzxcvbnm', 'device-uuid', 'session-uuid'
        );
        openssl_sign($canonical, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $this->assertSame(64, strlen($service->publicKeyFingerprint($publicDerBase64)));
        $this->assertTrue($service->verify($canonical, $encodedSignature, $publicDerBase64));
        $this->assertFalse($service->verify($canonical . 'tampered', $encodedSignature, $publicDerBase64));
    }

    public function test_disabled_rollout_passes_existing_api_requests_without_security_headers(): void
    {
        config(['app_security.enabled' => false]);
        $nextWasCalled = false;
        $response = (new VerifyAppSecurityAccess())->handle(
            Request::create('/api/dashboard'),
            function () use (&$nextWasCalled) {
                $nextWasCalled = true;
                return response()->json(['ok' => true]);
            },
        );

        $this->assertTrue($nextWasCalled);
        $this->assertTrue($response->getData(true)['ok']);
    }

    public function test_enabled_rollout_rejects_missing_proof_before_controller_execution(): void
    {
        config(['app_security.enabled' => true]);
        $nextWasCalled = false;
        $response = (new VerifyAppSecurityAccess())->handle(
            Request::create('/api/dashboard'),
            function () use (&$nextWasCalled) {
                $nextWasCalled = true;
                return response()->json(['ok' => true]);
            },
        );

        $this->assertFalse($nextWasCalled);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('app_security', $response->getData(true)['error']);
    }
}
