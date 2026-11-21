<?php

namespace App\Modules\AppSecurity\Services;

use phpseclib3\File\ASN1;
use phpseclib3\File\X509;
use Throwable;

/**
 * Fail-closed verifier for Android Key Attestation evidence.
 * Trust roots and a fresh Google attestation-status snapshot must be provisioned
 * outside the web root before this service can return a verified result.
 */
class AndroidKeyAttestationVerifier
{
    private const EXTENSION_OID = '1.3.6.1.4.1.11129.2.1.17';
    private const PROVISIONING_INFO_EXTENSION_OID = '1.3.6.1.4.1.11129.2.1.30';
    private const LEGACY_FACTORY_ROOT_SERIAL = 'f92009e853b6b045';

    public function verify(
        array $encodedChain,
        string $challenge,
        string $publicKeyBase64,
        string $packageName,
        string $signingSha256
    ): array {
        try {
            return $this->verifyOrThrow($encodedChain, $challenge, $publicKeyBase64, $packageName, $signingSha256);
        } catch (Throwable $exception) {
            report($exception);
            return ['verified' => false, 'reason' => 'verification_error'];
        }
    }

    private function verifyOrThrow(array $encodedChain, string $challenge, string $publicKeyBase64, string $packageName, string $signingSha256): array
    {
        if (count($encodedChain) < 2 || count($encodedChain) > 10) {
            return ['verified' => false, 'reason' => 'invalid_chain_length'];
        }

        $chain = [];
        foreach ($encodedChain as $certificate) {
            $der = base64_decode($certificate, true);
            if ($der === false || strlen($der) > 20000) {
                return ['verified' => false, 'reason' => 'invalid_certificate_encoding'];
            }
            $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END CERTIFICATE-----\n";
            $parsed = openssl_x509_read($pem);
            $details = $parsed ? openssl_x509_parse($parsed, false) : false;
            if (!$parsed || !$details) {
                return ['verified' => false, 'reason' => 'invalid_certificate'];
            }
            $chain[] = ['pem' => $pem, 'certificate' => $parsed, 'details' => $details, 'der' => $der];
        }

        $roots = $this->trustedRoots();
        if (!$roots) {
            return ['verified' => false, 'reason' => 'trust_roots_not_configured'];
        }

        $chainRoot = $chain[count($chain) - 1];
        $root = null;
        foreach ($roots as $candidate) {
            if ($this->fingerprint($candidate['der']) === $this->fingerprint($chainRoot['der'])) {
                $root = $chainRoot;
                break;
            }
            if (($chainRoot['details']['issuer'] ?? null) === ($candidate['details']['subject'] ?? null)
                && openssl_x509_verify($chainRoot['certificate'], openssl_pkey_get_public($candidate['certificate'])) === 1) {
                $root = $candidate;
                $chain[] = $candidate;
                break;
            }
        }
        if (!$root) {
            return ['verified' => false, 'reason' => 'untrusted_root'];
        }

        $allowExpiredFactoryCertificates = strtolower((string) (
            $root['details']['subject']['serialNumber'] ?? ''
        )) === self::LEGACY_FACTORY_ROOT_SERIAL;

        for ($i = 0; $i < count($chain) - 1; $i++) {
            $child = $chain[$i];
            $issuer = $chain[$i + 1];
            if (($child['details']['issuer'] ?? null) !== ($issuer['details']['subject'] ?? null)
                || openssl_x509_verify($child['certificate'], openssl_pkey_get_public($issuer['certificate'])) !== 1) {
                return ['verified' => false, 'reason' => 'invalid_chain_signature'];
            }
            if (($child['details']['validFrom_time_t'] ?? PHP_INT_MAX) > time()
                || ($child['details']['validTo_time_t'] ?? 0) < time()) {
                if (!$allowExpiredFactoryCertificates) {
                    return ['verified' => false, 'reason' => 'certificate_outside_validity'];
                }
            }
        }

        $crl = $this->freshRevocationList();
        if ($crl === null) {
            return ['verified' => false, 'reason' => 'revocation_data_unavailable_or_stale'];
        }
        foreach (array_slice($chain, 0, -1) as $entry) {
            $serial = strtolower(ltrim((string) ($entry['details']['serialNumberHex'] ?? ''), '0'));
            if ($serial !== '' && isset($crl['entries'][$serial])) {
                return ['verified' => false, 'reason' => 'certificate_revoked_or_suspended'];
            }
        }

        // Android chains are leaf-first. Per Android's verification guidance,
        // trust the first key-attestation extension encountered from the root
        // toward the leaf, not necessarily the leaf's extension. Later copies
        // may have been added by an attacker extending the submitted chain.
        $attestationCertificateIndex = null;
        $provisioningInfoCertificateIndex = null;
        $attestationCertificateParser = null;
        for ($index = count($chain) - 2; $index >= 0; $index--) {
            $parser = new X509();
            if (!$parser->loadX509($chain[$index]['pem'])) {
                return ['verified' => false, 'reason' => 'certificate_parse_failed'];
            }
            if ($provisioningInfoCertificateIndex === null
                && is_string($parser->getExtension(self::PROVISIONING_INFO_EXTENSION_OID))) {
                $provisioningInfoCertificateIndex = $index;
            }
            if ($attestationCertificateIndex === null
                && is_string($parser->getExtension(self::EXTENSION_OID))) {
                $attestationCertificateIndex = $index;
                $attestationCertificateParser = $parser;
            }
        }
        if ($attestationCertificateIndex === null) {
            return ['verified' => false, 'reason' => 'attestation_extension_missing'];
        }
        if ($provisioningInfoCertificateIndex !== null
            && $attestationCertificateIndex !== $provisioningInfoCertificateIndex - 1) {
            return ['verified' => false, 'reason' => 'provisioning_attestation_chain_invalid'];
        }

        $extension = $attestationCertificateParser?->getExtension(self::EXTENSION_OID);
        if (!is_string($extension)) {
            return ['verified' => false, 'reason' => 'attestation_extension_missing'];
        }
        $description = $this->decodeSingleBer($extension);
        if ($description === null || ($description['type'] ?? null) !== ASN1::TYPE_SEQUENCE) {
            return ['verified' => false, 'reason' => 'attestation_extension_invalid'];
        }
        $fields = $description['content'] ?? [];
        $fieldTypes = [
            ASN1::TYPE_INTEGER,
            ASN1::TYPE_ENUMERATED,
            ASN1::TYPE_INTEGER,
            ASN1::TYPE_ENUMERATED,
            ASN1::TYPE_OCTET_STRING,
            ASN1::TYPE_OCTET_STRING,
            ASN1::TYPE_SEQUENCE,
            ASN1::TYPE_SEQUENCE,
        ];
        if (count($fields) !== count($fieldTypes)) {
            return ['verified' => false, 'reason' => 'attestation_description_incomplete'];
        }
        foreach ($fieldTypes as $index => $type) {
            if (($fields[$index]['type'] ?? null) !== $type) {
                return ['verified' => false, 'reason' => 'attestation_description_invalid'];
            }
        }

        $attestationSecurityLevel = $this->integerValue($fields[1]);
        $keyMintSecurityLevel = $this->integerValue($fields[3]);
        $minimumSecurityLevel = (int) config('app_security.attestation_min_security_level', 1);
        if ($attestationSecurityLevel < $minimumSecurityLevel || $keyMintSecurityLevel < $minimumSecurityLevel) {
            return ['verified' => false, 'reason' => 'security_level_below_policy'];
        }
        $attestationChallenge = $this->octets($fields[4]);
        $challengeBase64 = strtr($challenge, '-_', '+/');
        $challengeBase64 .= str_repeat('=', (4 - strlen($challengeBase64) % 4) % 4);
        $expectedChallenge = base64_decode($challengeBase64, true);
        if ($expectedChallenge === false || !hash_equals($expectedChallenge, (string) $attestationChallenge)) {
            return ['verified' => false, 'reason' => 'challenge_mismatch'];
        }

        $applicationId = $this->findContextOctets($fields[7], 709);
        if ($applicationId === null || !$this->applicationIdentityMatches($applicationId, $packageName, $signingSha256)) {
            return ['verified' => false, 'reason' => 'application_identity_mismatch'];
        }

        if (!$this->keyPolicyMatches($fields[7])) {
            return ['verified' => false, 'reason' => 'hardware_key_policy_mismatch'];
        }
        $rootOfTrust = $this->findContextNode($fields[7], 704);
        $bootFields = $rootOfTrust['content'] ?? [];
        if (($rootOfTrust['type'] ?? null) !== ASN1::TYPE_SEQUENCE
            || !in_array(count($bootFields), [3, 4], true)
            || ($bootFields[0]['type'] ?? null) !== ASN1::TYPE_OCTET_STRING
            || ($bootFields[1]['type'] ?? null) !== ASN1::TYPE_BOOLEAN
            || ($bootFields[2]['type'] ?? null) !== ASN1::TYPE_ENUMERATED
            || (isset($bootFields[3]) && ($bootFields[3]['type'] ?? null) !== ASN1::TYPE_OCTET_STRING)) {
            return ['verified' => false, 'reason' => 'verified_boot_policy_mismatch'];
        }
        $deviceLocked = $bootFields[1]['content'] ?? false;
        $verifiedBootState = isset($bootFields[2]) ? $this->integerValue($bootFields[2]) : -1;
        if (!is_bool($deviceLocked) || !$deviceLocked || $verifiedBootState !== 0) {
            return ['verified' => false, 'reason' => 'verified_boot_policy_mismatch'];
        }

        $attestedPublicKey = openssl_pkey_get_public($chain[$attestationCertificateIndex]['certificate']);
        $attestedKeyDetails = $attestedPublicKey ? openssl_pkey_get_details($attestedPublicKey) : false;
        $registeredKeyDer = base64_decode($publicKeyBase64, true);
        $attestedPemBody = is_array($attestedKeyDetails) ? preg_replace('/-----[^-]+-----|\s+/', '', $attestedKeyDetails['key']) : '';
        $attestedKeyDer = is_string($attestedPemBody) ? base64_decode($attestedPemBody, true) : false;
        if ($registeredKeyDer === false || $attestedKeyDer === false || !hash_equals($attestedKeyDer, $registeredKeyDer)) {
            return ['verified' => false, 'reason' => 'registered_key_mismatch'];
        }

        return [
            'verified' => true,
            'reason' => null,
            'security_level' => $attestationSecurityLevel === 2 ? 'strongbox' : 'tee',
            'attestation_version' => $this->integerValue($fields[0]),
            'keymint_security_level' => $keyMintSecurityLevel,
            'os_version' => $this->contextInteger($fields[7], 705) ?? $this->contextInteger($fields[6], 705),
            'os_patch_level' => $this->contextInteger($fields[7], 706) ?? $this->contextInteger($fields[6], 706),
            'verified_boot_state' => 'verified',
            'device_locked' => true,
            'root_fingerprint' => $this->fingerprint($root['der']),
            'verified_at' => now()->toIso8601String(),
        ];
    }

    private function trustedRoots(): array
    {
        $path = config('app_security.attestation_roots_path');
        if (!is_string($path) || !is_readable($path)) {
            return [];
        }
        $contents = file_get_contents($path);
        preg_match_all('/-----BEGIN CERTIFICATE-----(.*?)-----END CERTIFICATE-----/s', $contents ?: '', $matches);
        $roots = [];
        foreach ($matches[0] ?? [] as $pem) {
            $certificate = openssl_x509_read($pem);
            $details = $certificate ? openssl_x509_parse($certificate, false) : false;
            $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem) ?: '', true);
            if ($certificate && $details && $der !== false) {
                $roots[] = compact('pem', 'certificate', 'details', 'der');
            }
        }
        return $roots;
    }

    private function freshRevocationList(): ?array
    {
        $path = config('app_security.attestation_crl_path');
        if (!is_string($path) || !is_readable($path)) {
            return null;
        }
        $list = json_decode((string) file_get_contents($path), true);
        if (!is_array($list) || !is_array($list['entries'] ?? null) || !is_numeric($list['fetched_at'] ?? null)) {
            return null;
        }
        $maxAge = min(
            (int) config('app_security.attestation_crl_max_age_seconds', 86400),
            max(0, (int) ($list['cache_ttl_seconds'] ?? 86400))
        );
        if ((int) $list['fetched_at'] > time() || time() - (int) $list['fetched_at'] > $maxAge) {
            return null;
        }
        return $list;
    }

    private function findContextOctets(array $node, int $tag): ?string
    {
        $inner = $this->findContextNode($node, $tag);
        return is_array($inner) && ($inner['type'] ?? null) === ASN1::TYPE_OCTET_STRING
            ? ($inner['content'] ?? null) : null;
    }

    private function decodeSingleBer(string $encoded): ?array
    {
        $decoded = ASN1::decodeBER($encoded);
        $node = $decoded[0] ?? null;
        if (!is_array($node) || count($decoded) !== 1
            || ($node['length'] ?? null) !== strlen($encoded)) {
            return null;
        }

        return $node;
    }

    private function findContextNode(array $node, int $tag): ?array
    {
        foreach ($node['content'] ?? [] as $field) {
            if (($field['type'] ?? null) === ASN1::CLASS_CONTEXT_SPECIFIC && ($field['constant'] ?? null) === $tag) {
                $inner = $field['content'][0] ?? null;
                return is_array($inner) ? $inner : null;
            }
        }
        return null;
    }

    private function contextInteger(array $node, int $tag): ?int
    {
        $value = $this->findContextNode($node, $tag);
        return is_array($value) ? $this->integerValue($value) : null;
    }

    private function keyPolicyMatches(array $hardwareEnforced): bool
    {
        $purposes = $this->findContextNode($hardwareEnforced, 1);
        $digests = $this->findContextNode($hardwareEnforced, 5);
        $algorithm = $this->contextInteger($hardwareEnforced, 2);
        $keySize = $this->contextInteger($hardwareEnforced, 3);
        $curve = $this->contextInteger($hardwareEnforced, 10);
        $purposeValues = $purposes['content'] ?? [];
        $digestValues = $digests['content'] ?? [];
        if (($purposes['type'] ?? null) !== ASN1::TYPE_SET
            || ($digests['type'] ?? null) !== ASN1::TYPE_SET
            || $purposeValues === [] || $digestValues === []) {
            return false;
        }
        foreach (array_merge($purposeValues, $digestValues) as $value) {
            if (($value['type'] ?? null) !== ASN1::TYPE_INTEGER) {
                return false;
            }
        }
        $purposeInts = array_map(fn (array $value) => $this->integerValue($value), $purposeValues);
        $digestInts = array_map(fn (array $value) => $this->integerValue($value), $digestValues);

        return in_array(2, $purposeInts, true) && in_array(3, $purposeInts, true)
            && $algorithm === 3 && $keySize === 256 && in_array(4, $digestInts, true) && $curve === 1;
    }

    private function applicationIdentityMatches(string $encoded, string $packageName, string $signingSha256): bool
    {
        $applicationId = $this->decodeSingleBer($encoded);
        $sequence = $applicationId['content'] ?? null;
        if (($applicationId['type'] ?? null) !== ASN1::TYPE_SEQUENCE
            || !is_array($sequence) || count($sequence) !== 2
            || ($sequence[0]['type'] ?? null) !== ASN1::TYPE_SET
            || ($sequence[1]['type'] ?? null) !== ASN1::TYPE_SET) {
            return false;
        }
        $expectedDigest = strtolower(preg_replace('/[^a-f0-9]/i', '', $signingSha256) ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $expectedDigest)) {
            return false;
        }
        $packages = $sequence[0]['content'] ?? [];
        $digests = $sequence[1]['content'] ?? [];
        $packageMatches = false;
        foreach ($packages as $packageInfo) {
            $parts = $packageInfo['content'] ?? [];
            if (($packageInfo['type'] ?? null) !== ASN1::TYPE_SEQUENCE
                || count($parts) !== 2
                || ($parts[0]['type'] ?? null) !== ASN1::TYPE_OCTET_STRING
                || ($parts[1]['type'] ?? null) !== ASN1::TYPE_INTEGER) {
                return false;
            }
            if (($parts[0]['content'] ?? null) === $packageName) {
                $packageMatches = true;
            }
        }
        if (!$packageMatches) {
            return false;
        }
        foreach ($digests as $digest) {
            $value = $digest['content'] ?? null;
            if (($digest['type'] ?? null) !== ASN1::TYPE_OCTET_STRING
                || !is_string($value) || strlen($value) !== 32) {
                return false;
            }
            if (hash_equals($expectedDigest, bin2hex($value))) {
                return true;
            }
        }
        return false;
    }

    private function integerValue(array $node): int
    {
        if (!in_array($node['type'] ?? null, [ASN1::TYPE_INTEGER, ASN1::TYPE_ENUMERATED], true)) {
            return -1;
        }
        if (($node['type'] ?? null) === ASN1::TYPE_NULL) {
            return -1;
        }
        return isset($node['content']) && is_object($node['content']) && method_exists($node['content'], 'toString')
            ? (int) $node['content']->toString() : -1;
    }

    private function octets(array $node): ?string
    {
        return ($node['type'] ?? null) === ASN1::TYPE_OCTET_STRING && is_string($node['content'] ?? null)
            ? $node['content'] : null;
    }

    private function fingerprint(string $der): string
    {
        return strtoupper(hash('sha256', $der));
    }
}
