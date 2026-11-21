<?php

namespace App\Modules\AppSecurity\Services;

class SecuritySignatureService
{
    public function publicKeyPem(string $encodedPublicKey): string
    {
        if (str_contains($encodedPublicKey, 'BEGIN PUBLIC KEY')) {
            return $encodedPublicKey;
        }

        $der = base64_decode($encodedPublicKey, true);
        if ($der === false || $der === '') {
            throw new AppSecurityException('Invalid device public key.', 422);
        }

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    public function publicKeyFingerprint(string $encodedPublicKey): string
    {
        $pem = $this->publicKeyPem($encodedPublicKey);
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new AppSecurityException('Invalid device public key.', 422);
        }
        $details = openssl_pkey_get_details($key);
        if (($details['type'] ?? null) !== OPENSSL_KEYTYPE_EC) {
            throw new AppSecurityException('Device key must use elliptic-curve cryptography.', 422);
        }
        $curve = strtolower((string) ($details['ec']['curve_name'] ?? ''));
        if (!in_array($curve, ['prime256v1', 'secp256r1'], true)) {
            throw new AppSecurityException('Device key must use the P-256 curve.', 422);
        }
        $encoded = preg_replace('/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/', '', $pem);
        $der = base64_decode((string) $encoded, true);
        if ($der === false) {
            throw new AppSecurityException('Invalid device public key.', 422);
        }
        return hash('sha256', $der);
    }

    public function verify(string $message, string $signature, string $encodedPublicKey): bool
    {
        $key = openssl_pkey_get_public($this->publicKeyPem($encodedPublicKey));
        $rawSignature = $this->decodeBase64Url($signature);
        if ($key === false || $rawSignature === false) {
            return false;
        }
        return openssl_verify($message, $rawSignature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    public function decodeBase64Url(string $value): string|false
    {
        $normalized = strtr($value, '-_', '+/');
        $normalized .= str_repeat('=', (4 - strlen($normalized) % 4) % 4);
        return base64_decode($normalized, true);
    }

    public function canonicalRequest(
        string $method,
        string $requestUri,
        string $body,
        string $timestamp,
        string $nonce,
        string $deviceUuid,
        string $sessionUuid
    ): string {
        return strtoupper($method) . "\n"
            . $requestUri . "\n"
            . hash('sha256', $body) . "\n"
            . $timestamp . "\n"
            . $nonce . "\n"
            . $deviceUuid . "\n"
            . $sessionUuid;
    }
}
