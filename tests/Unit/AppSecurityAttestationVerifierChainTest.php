<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\AndroidKeyAttestationVerifier;
use Tests\TestCase;

class AppSecurityAttestationVerifierChainTest extends TestCase
{
    private string $directory;
    private string $challenge;
    private string $signer;
    private string $leafKeyDer;
    private array $chain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/ky-attestation-chain-' . bin2hex(random_bytes(5));
        mkdir($this->directory, 0700, true);
        $this->challenge = 'challenge bytes for attestation';
        $this->signer = hash('sha256', 'official apk signing certificate');
        $this->chain = $this->makeChain();
        config([
            'app_security.attestation_roots_path' => $this->directory . '/roots.pem',
            'app_security.attestation_crl_path' => $this->directory . '/status.json',
            'app_security.attestation_crl_max_age_seconds' => 86400,
            'app_security.attestation_min_security_level' => 1,
        ]);
        file_put_contents($this->directory . '/roots.pem', $this->chain['root_pem']);
        file_put_contents($this->directory . '/status.json', json_encode([
            'fetched_at' => time(), 'cache_ttl_seconds' => 21600, 'entries' => [],
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->directory);
        parent::tearDown();
    }

    public function test_trusted_chain_checks_challenge_key_and_application_identity(): void
    {
        $result = app(AndroidKeyAttestationVerifier::class)->verify(
            $this->chain['certificates'], $this->base64Url($this->challenge), base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms', $this->signer
        );

        $this->assertTrue($result['verified'], json_encode($result));
        $this->assertSame('tee', $result['security_level']);

        $wrongChallenge = app(AndroidKeyAttestationVerifier::class)->verify(
            $this->chain['certificates'], $this->base64Url('different challenge'), base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms', $this->signer
        );
        $this->assertFalse($wrongChallenge['verified']);
        $this->assertSame('challenge_mismatch', $wrongChallenge['reason']);

        $wrongPackage = app(AndroidKeyAttestationVerifier::class)->verify(
            $this->chain['certificates'], $this->base64Url($this->challenge), base64_encode($this->leafKeyDer),
            'com.example.other', $this->signer
        );
        $this->assertFalse($wrongPackage['verified']);
        $this->assertSame('application_identity_mismatch', $wrongPackage['reason']);

        $wrongSigner = app(AndroidKeyAttestationVerifier::class)->verify(
            $this->chain['certificates'], $this->base64Url($this->challenge), base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms', str_repeat('F', 64)
        );
        $this->assertFalse($wrongSigner['verified']);
        $this->assertSame('application_identity_mismatch', $wrongSigner['reason']);

        file_put_contents($this->directory . '/status.json', json_encode([
            'fetched_at' => time(), 'cache_ttl_seconds' => 21600,
            'entries' => [$this->chain['leaf_serial'] => ['status' => 'REVOKED']],
        ], JSON_THROW_ON_ERROR));
        $revoked = app(AndroidKeyAttestationVerifier::class)->verify(
            $this->chain['certificates'], $this->base64Url($this->challenge), base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms', $this->signer
        );
        $this->assertFalse($revoked['verified']);
        $this->assertSame('certificate_revoked_or_suspended', $revoked['reason']);
    }

    public function test_nearest_root_attestation_extension_is_used_instead_of_a_later_leaf_copy(): void
    {
        $chain = $this->makeChainWithIntermediateAttestation();
        file_put_contents($this->directory . '/roots.pem', $chain['root_pem']);

        $result = app(AndroidKeyAttestationVerifier::class)->verify(
            $chain['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );

        $this->assertTrue($result['verified'], json_encode($result));
    }

    public function test_rkp_provisioning_extension_must_be_followed_by_attestation_certificate(): void
    {
        $chain = $this->makeProvisionedChain();
        file_put_contents($this->directory . '/roots.pem', $chain['root_pem']);

        $result = app(AndroidKeyAttestationVerifier::class)->verify(
            $chain['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );

        $this->assertTrue($result['verified'], json_encode($result));
    }

    public function test_attestation_decoder_rejects_trailing_ber_values(): void
    {
        $chainWithTrailingExtensionValue = $this->makeChain(
            $this->attestationExtension() . "\x05\x00"
        );
        file_put_contents($this->directory . '/roots.pem', $chainWithTrailingExtensionValue['root_pem']);
        $extensionResult = app(AndroidKeyAttestationVerifier::class)->verify(
            $chainWithTrailingExtensionValue['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );
        $this->assertFalse($extensionResult['verified']);
        $this->assertSame('attestation_extension_invalid', $extensionResult['reason']);

        $chainWithTrailingApplicationIdValue = $this->makeChain($this->attestationExtension(true));
        file_put_contents($this->directory . '/roots.pem', $chainWithTrailingApplicationIdValue['root_pem']);
        $applicationIdResult = app(AndroidKeyAttestationVerifier::class)->verify(
            $chainWithTrailingApplicationIdValue['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );
        $this->assertFalse($applicationIdResult['verified']);
        $this->assertSame('application_identity_mismatch', $applicationIdResult['reason']);

        $chainWithUnexpectedDescriptionField = $this->makeChain(
            $this->attestationExtension(false, true)
        );
        file_put_contents($this->directory . '/roots.pem', $chainWithUnexpectedDescriptionField['root_pem']);
        $descriptionResult = app(AndroidKeyAttestationVerifier::class)->verify(
            $chainWithUnexpectedDescriptionField['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );
        $this->assertFalse($descriptionResult['verified']);
        $this->assertSame('attestation_description_incomplete', $descriptionResult['reason']);
    }

    public function test_expired_factory_certificates_are_allowed_only_for_the_documented_root_and_remain_revocable(): void
    {
        $legacyChain = $this->makeChain(null, 0, 'f92009e853b6b045');
        file_put_contents($this->directory . '/roots.pem', $legacyChain['root_pem']);
        usleep(1_100_000);
        $legacyResult = app(AndroidKeyAttestationVerifier::class)->verify(
            $legacyChain['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );
        $this->assertTrue($legacyResult['verified'], json_encode($legacyResult));

        file_put_contents($this->directory . '/status.json', json_encode([
            'fetched_at' => time(), 'cache_ttl_seconds' => 21600,
            'entries' => [$legacyChain['leaf_serial'] => ['status' => 'REVOKED']],
        ], JSON_THROW_ON_ERROR));
        $revokedLegacyResult = app(AndroidKeyAttestationVerifier::class)->verify(
            $legacyChain['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );
        $this->assertFalse($revokedLegacyResult['verified']);
        $this->assertSame('certificate_revoked_or_suspended', $revokedLegacyResult['reason']);

        $rkpChain = $this->makeChain(null, 0, 'rkp-root-serial');
        file_put_contents($this->directory . '/roots.pem', $rkpChain['root_pem']);
        file_put_contents($this->directory . '/status.json', json_encode([
            'fetched_at' => time(), 'cache_ttl_seconds' => 21600, 'entries' => [],
        ], JSON_THROW_ON_ERROR));
        usleep(1_100_000);
        $rkpResult = app(AndroidKeyAttestationVerifier::class)->verify(
            $rkpChain['certificates'],
            $this->base64Url($this->challenge),
            base64_encode($this->leafKeyDer),
            'com.kneayerng.hrms',
            $this->signer
        );
        $this->assertFalse($rkpResult['verified']);
        $this->assertSame('certificate_outside_validity', $rkpResult['reason']);
    }

    private function makeChain(
        ?string $extensionOverride = null,
        int $leafValidityDays = 365,
        ?string $rootSubjectSerial = null,
    ): array
    {
        $rootKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $rootSubject = ['commonName' => 'KNEAYERNG Attestation Test Root'];
        if ($rootSubjectSerial !== null) {
            $rootSubject['serialNumber'] = $rootSubjectSerial;
        }
        $rootRequest = openssl_csr_new($rootSubject, $rootKey);
        $rootCertificate = openssl_csr_sign($rootRequest, null, $rootKey, 365);
        openssl_x509_export($rootCertificate, $rootPem);

        $leafKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $leafRequest = openssl_csr_new(['commonName' => 'KNEAYERNG Device Key'], $leafKey);
        $keyDetails = openssl_pkey_get_details($leafKey);
        $this->leafKeyDer = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $keyDetails['key']));
        $extension = $extensionOverride ?? $this->attestationExtension();
        $config = "[req]\ndistinguished_name=req_dn\n[req_dn]\n[attestation]\n1.3.6.1.4.1.11129.2.1.17=DER:" . bin2hex($extension) . "\n";
        $configPath = $this->directory . '/openssl.cnf';
        file_put_contents($configPath, $config);
        $leafCertificate = openssl_csr_sign($leafRequest, $rootCertificate, $rootKey, $leafValidityDays, [
            'config' => $configPath,
            'x509_extensions' => 'attestation',
            'digest_alg' => 'sha256',
        ], 12345);
        openssl_x509_export($leafCertificate, $leafPem);
        $certificates = array_map(fn ($pem) => base64_encode(base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem))), [$leafPem, $rootPem]);
        $leafDetails = openssl_x509_parse($leafCertificate, false);
        $leafSerial = strtolower(ltrim((string) ($leafDetails['serialNumberHex'] ?? ''), '0'));
        return ['root_pem' => $rootPem, 'certificates' => $certificates, 'leaf_serial' => $leafSerial];
    }

    private function makeChainWithIntermediateAttestation(): array
    {
        $rootKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $rootRequest = openssl_csr_new(['commonName' => 'KNEAYERNG Attestation Test Root'], $rootKey);
        $rootCertificate = openssl_csr_sign($rootRequest, null, $rootKey, 365);
        openssl_x509_export($rootCertificate, $rootPem);

        $attestedKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $attestedRequest = openssl_csr_new(['commonName' => 'KNEAYERNG Attested Key'], $attestedKey);
        $attestedDetails = openssl_pkey_get_details($attestedKey);
        $this->leafKeyDer = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $attestedDetails['key']));
        $extensionConfig = "[req]\ndistinguished_name=req_dn\n[req_dn]\n[attestation]\n1.3.6.1.4.1.11129.2.1.17=DER:" . bin2hex($this->attestationExtension()) . "\n";
        $configPath = $this->directory . '/openssl-intermediate.cnf';
        file_put_contents($configPath, $extensionConfig);
        $attestedCertificate = openssl_csr_sign($attestedRequest, $rootCertificate, $rootKey, 365, [
            'config' => $configPath,
            'x509_extensions' => 'attestation',
            'digest_alg' => 'sha256',
        ], 12346);
        openssl_x509_export($attestedCertificate, $attestedPem);

        // This child certificate contains a later, attacker-controlled copy of
        // the extension. Android requires the nearest-to-root occurrence above.
        $childKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $childRequest = openssl_csr_new(['commonName' => 'Untrusted Extension Copy'], $childKey);
        $childConfig = "[req]\ndistinguished_name=req_dn\n[req_dn]\n[attestation]\n1.3.6.1.4.1.11129.2.1.17=DER:" . bin2hex($this->attestationExtension()) . "\n";
        $childConfigPath = $this->directory . '/openssl-child.cnf';
        file_put_contents($childConfigPath, $childConfig);
        $childCertificate = openssl_csr_sign($childRequest, $attestedCertificate, $attestedKey, 365, [
            'config' => $childConfigPath,
            'x509_extensions' => 'attestation',
            'digest_alg' => 'sha256',
        ], 12347);
        openssl_x509_export($childCertificate, $childPem);

        $certificates = array_map(
            fn ($pem) => base64_encode(base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem))),
            [$childPem, $attestedPem, $rootPem]
        );
        return ['root_pem' => $rootPem, 'certificates' => $certificates];
    }

    private function makeProvisionedChain(): array
    {
        $rootKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $rootRequest = openssl_csr_new(['commonName' => 'KNEAYERNG Attestation Test Root'], $rootKey);
        $rootCertificate = openssl_csr_sign($rootRequest, null, $rootKey, 365);
        openssl_x509_export($rootCertificate, $rootPem);

        $provisioningKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $provisioningRequest = openssl_csr_new(['commonName' => 'KNEAYERNG RKP Provisioner'], $provisioningKey);
        $provisioningConfig = "[req]\ndistinguished_name=req_dn\n[req_dn]\n[provisioning]\n1.3.6.1.4.1.11129.2.1.30=DER:0403A10100\n";
        $provisioningConfigPath = $this->directory . '/openssl-provisioning.cnf';
        file_put_contents($provisioningConfigPath, $provisioningConfig);
        $provisioningCertificate = openssl_csr_sign($provisioningRequest, $rootCertificate, $rootKey, 365, [
            'config' => $provisioningConfigPath,
            'x509_extensions' => 'provisioning',
            'digest_alg' => 'sha256',
        ], 12348);
        openssl_x509_export($provisioningCertificate, $provisioningPem);

        $leafKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $leafRequest = openssl_csr_new(['commonName' => 'KNEAYERNG RKP Attested Key'], $leafKey);
        $leafDetails = openssl_pkey_get_details($leafKey);
        $this->leafKeyDer = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $leafDetails['key']));
        $attestationConfig = "[req]\ndistinguished_name=req_dn\n[req_dn]\n[attestation]\n1.3.6.1.4.1.11129.2.1.17=DER:" . bin2hex($this->attestationExtension()) . "\n";
        $attestationConfigPath = $this->directory . '/openssl-rkp-attestation.cnf';
        file_put_contents($attestationConfigPath, $attestationConfig);
        $leafCertificate = openssl_csr_sign($leafRequest, $provisioningCertificate, $provisioningKey, 365, [
            'config' => $attestationConfigPath,
            'x509_extensions' => 'attestation',
            'digest_alg' => 'sha256',
        ], 12349);
        openssl_x509_export($leafCertificate, $leafPem);

        $certificates = array_map(
            fn ($pem) => base64_encode(base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem))),
            [$leafPem, $provisioningPem, $rootPem]
        );
        return ['root_pem' => $rootPem, 'certificates' => $certificates];
    }

    private function attestationExtension(
        bool $appendTrailingApplicationIdValue = false,
        bool $appendUnexpectedDescriptionField = false,
    ): string
    {
        $packageInfo = $this->sequence($this->octets('com.kneayerng.hrms') . $this->integer(1));
        $applicationId = $this->sequence(
            $this->set($packageInfo) . $this->set($this->octets(hex2bin($this->signer)))
        );
        if ($appendTrailingApplicationIdValue) {
            $applicationId .= "\x05\x00";
        }
        $rootOfTrust = $this->sequence(
            $this->octets('verified-boot-key') . $this->boolean(true) . $this->enumerated(0) . $this->octets('boot-hash')
        );
        $authorizationList = $this->sequence(
            $this->contextExplicit(1, $this->set($this->integer(2) . $this->integer(3)))
            . $this->contextExplicit(2, $this->integer(3))
            . $this->contextExplicit(3, $this->integer(256))
            . $this->contextExplicit(5, $this->set($this->integer(4)))
            . $this->contextExplicit(10, $this->integer(1))
            . $this->contextExplicit(704, $rootOfTrust)
            . $this->contextExplicit(709, $this->octets($applicationId))
        );
        $description =
            $this->integer(4) . $this->enumerated(1) . $this->integer(4) . $this->enumerated(1)
            . $this->octets($this->challenge) . $this->octets('') . $this->sequence('') . $authorizationList;
        if ($appendUnexpectedDescriptionField) {
            $description .= $this->integer(1);
        }
        return $this->sequence($description);
    }

    private function sequence(string $contents): string { return $this->tlv(0x30, $contents); }
    private function set(string $contents): string { return $this->tlv(0x31, $contents); }
    private function octets(string $contents): string { return $this->tlv(0x04, $contents); }
    private function integer(int $value): string
    {
        $bytes = ltrim(pack('N', $value), "\0");
        if ($bytes === '' || (ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\0" . $bytes;
        }
        return $this->tlv(0x02, $bytes);
    }
    private function enumerated(int $value): string { return $this->tlv(0x0a, chr($value)); }
    private function boolean(bool $value): string { return $this->tlv(0x01, $value ? "\xff" : "\0"); }

    private function contextExplicit(int $tag, string $contents): string
    {
        $parts = [];
        do {
            array_unshift($parts, $tag & 0x7f);
            $tag >>= 7;
        } while ($tag > 0);
        for ($index = 0; $index < count($parts) - 1; $index++) {
            $parts[$index] |= 0x80;
        }
        return $this->tlv("\xbf" . implode('', array_map('chr', $parts)), $contents);
    }

    private function tlv(string|int $tag, string $contents): string
    {
        $length = strlen($contents);
        if ($length < 128) {
            $encodedLength = chr($length);
        } else {
            $bytes = ltrim(pack('N', $length), "\0");
            $encodedLength = chr(0x80 | strlen($bytes)) . $bytes;
        }
        return (is_int($tag) ? chr($tag) : $tag) . $encodedLength . $contents;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
