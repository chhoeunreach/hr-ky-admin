<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\AndroidKeyAttestationVerifier;
use Tests\TestCase;

class AppSecurityAttestationVerifierTest extends TestCase
{
    public function test_unconfigured_trust_roots_never_mark_an_attestation_verified(): void
    {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $request = openssl_csr_new(['commonName' => 'attestation-test'], $key);
        $certificate = openssl_csr_sign($request, null, $key, 1);
        openssl_x509_export($certificate, $pem);
        $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem));
        $encoded = base64_encode($der);
        config(['app_security.attestation_roots_path' => '/path/that/does/not/exist']);

        $result = app(AndroidKeyAttestationVerifier::class)->verify(
            [$encoded, $encoded], 'fresh-challenge', 'ZmFrZS1wdWJsaWMta2V5', 'com.kneayerng.hrms', str_repeat('A', 64)
        );

        $this->assertFalse($result['verified']);
        $this->assertSame('trust_roots_not_configured', $result['reason']);
    }

    public function test_malformed_or_short_certificate_chain_is_rejected(): void
    {
        $result = app(AndroidKeyAttestationVerifier::class)->verify(
            ['not-a-certificate'], 'challenge', 'key', 'com.kneayerng.hrms', str_repeat('A', 64)
        );

        $this->assertFalse($result['verified']);
        $this->assertSame('invalid_chain_length', $result['reason']);
    }
}
