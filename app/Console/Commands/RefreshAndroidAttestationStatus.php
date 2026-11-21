<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use JsonException;

class RefreshAndroidAttestationStatus extends Command
{
    protected $signature = 'app-security:refresh-attestation-status';
    protected $description = 'Refresh Google Android Key Attestation certificate status data';

    public function handle(): int
    {
        $response = Http::acceptJson()->connectTimeout(5)->timeout(15)
            ->get('https://android.googleapis.com/attestation/status');
        if (!$response->successful() || strlen($response->body()) > 5_000_000) {
            $this->error('Google attestation status could not be fetched safely; the existing snapshot was retained.');
            return self::FAILURE;
        }

        try {
            $source = json_decode($response->body(), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('Google returned invalid attestation status JSON; the existing snapshot was retained.');
            return self::FAILURE;
        }
        $entries = $source['entries'] ?? null;
        if (!is_array($entries)) {
            $this->error('Google status response did not contain an entries map; the existing snapshot was retained.');
            return self::FAILURE;
        }
        foreach ($entries as $serial => $entry) {
            if (!is_string($serial) || !preg_match('/^[a-f0-9]+$/i', $serial)
                || !is_array($entry) || !in_array($entry['status'] ?? null, ['REVOKED', 'SUSPENDED'], true)) {
                $this->error('Google status response had an invalid entry; the existing snapshot was retained.');
                return self::FAILURE;
            }
        }

        $cacheControl = $response->header('Cache-Control', '');
        $cacheTtl = preg_match('/(?:^|,)\s*max-age=(\d+)/i', $cacheControl, $matches)
            ? min(86400, max(1, (int) $matches[1])) : 21600;
        $path = (string) config('app_security.attestation_crl_path');
        $directory = dirname($path);
        if ($path === '' || (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory))) {
            $this->error('The private attestation status directory could not be created.');
            return self::FAILURE;
        }

        $snapshot = json_encode([
            'fetched_at' => time(),
            'cache_ttl_seconds' => $cacheTtl,
            'entries' => $entries,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $temporary = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (file_put_contents($temporary, $snapshot, LOCK_EX) === false || !rename($temporary, $path)) {
            @unlink($temporary);
            $this->error('The private attestation status snapshot could not be written.');
            return self::FAILURE;
        }
        @chmod($path, 0600);
        $this->info('Refreshed Android attestation status entries: ' . count($entries));
        return self::SUCCESS;
    }
}
