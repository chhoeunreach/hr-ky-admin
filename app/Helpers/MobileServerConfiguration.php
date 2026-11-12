<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class MobileServerConfiguration
{
    private static function normalize(?string $url): ?string
    {
        if (!$url || !filter_var(trim($url), FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url(trim($url));
        if (!in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }
        return rtrim(trim($url), '/').'/';
    }

    public static function primaryUrl(): string
    {
        return self::normalize(config('app.mobile_app_url'))
            ?? self::normalize(config('app.url'))
            ?? 'http://localhost/';
    }

    public static function fallbackUrl(): ?string
    {
        $backup = self::normalize(config('app.mobile_app_fallback_url'))
            ?? self::normalize(config('app.fallback_url'));
        return $backup === self::primaryUrl() ? null : $backup;
    }

    public static function qrPayload(): ?string
    {
        $backup = self::fallbackUrl();
        if (!$backup) {
            return null;
        }
        return base64_encode(json_encode([
            'server_url' => self::primaryUrl(),
            'server_fallback_url' => $backup,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /**
     * The address a phone can actually reach: the host currently serving the
     * admin panel. Falls back to the configured primary for local hosts
     * (localhost/127.0.0.1) that a phone cannot scan-usefully connect to.
     */
    public static function primaryUrlFromRequest(Request $request): string
    {
        $host = $request->getSchemeAndHttpHost();
        if ($host && !in_array(parse_url($host, PHP_URL_HOST), ['localhost', '127.0.0.1', '::1'], true)) {
            $normalized = self::normalize($host);
            if ($normalized) {
                return $normalized;
            }
        }

        return self::primaryUrl();
    }

    public static function qrPayloadForRequest(Request $request): ?string
    {
        $backup = self::fallbackUrl();
        $primary = self::primaryUrlFromRequest($request);
        if (!$backup || $backup === $primary) {
            return null;
        }

        return base64_encode(json_encode([
            'server_url' => $primary,
            'server_fallback_url' => $backup,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
