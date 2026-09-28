<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ErrorDigestToTelegram extends Command
{
    protected $signature = 'errors:telegram-digest
        {--hours=24 : How many hours back to scan on the very first run}
        {--health-only : Skip log scanning and only alert when the server is unhealthy}
        {--force-health : Send the health report even when everything is healthy}
        {--dry-run : Print the report instead of sending it to Telegram}
        {--chat-id= : Override the destination chat ID}';

    protected $description = 'Scan Laravel error logs + server health and send a digest to the Telegram group';

    /** Telegram hard limit for a message is 4096 characters. */
    private const MESSAGE_LIMIT = 4096;

    private const STATE_FILE = 'app/error-digest-state.json';

    private const HEALTH_STATE_FILE = 'app/health-alert-state.json';

    /** Do not repeat an unhealthy alert more often than this. */
    private const HEALTH_ALERT_COOLDOWN = 3600;

    /** Log levels included in the digest. */
    private const LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY', 'WARNING'];

    public function handle(TelegramService $telegram): int
    {
        $chatId = $this->resolveChatId();

        if ($chatId === '' && ! $this->option('dry-run')) {
            $this->error('No chat ID configured. Set TELEGRAM_CHAT_ID in .env.');

            return self::FAILURE;
        }

        $health = $this->serverHealth();

        if ($this->option('health-only')) {
            return $this->runHealthOnly($chatId, $health, $telegram);
        }

        $state = $this->readState();
        $firstRun = $state === [];

        $files = $this->logFiles();
        $newEntries = $this->collectEntries($files, $state);
        $cursor = $this->cursor($files);

        $message = $newEntries === []
            ? $this->buildHealthyDigest($health, $firstRun)
            : $this->buildDigest($newEntries, $health, $firstRun);

        if ($this->option('dry-run')) {
            $this->line(html_entity_decode(strip_tags($message)));

            return self::SUCCESS;
        }

        $result = $this->deliver($chatId, $message, $telegram);

        // Advance the cursor only after the digest has actually gone out, so a
        // failed send is retried on the next run instead of losing the errors.
        if ($result === self::SUCCESS) {
            $this->writeState($cursor);
        }

        return $result;
    }

    /**
     * Alert-only mode used by a frequent cron: sends nothing while the server
     * is healthy, and throttles repeated alerts while it stays unhealthy.
     */
    private function runHealthOnly(string $chatId, array $health, TelegramService $telegram): int
    {
        $status = $health['status'];

        if ($status === 'ok' && ! $this->option('force-health')) {
            $this->info('Server healthy. Nothing sent.');
            $this->clearHealthAlert();

            return self::SUCCESS;
        }

        if (! $this->option('force-health') && ! $this->healthAlertDue()) {
            $this->info('Unhealthy alert already sent within the cooldown window.');

            return self::SUCCESS;
        }

        $message = $this->buildHealthMessage($health);

        if ($this->option('dry-run')) {
            $this->line(html_entity_decode(strip_tags($message)));

            return self::SUCCESS;
        }

        $result = $this->deliver($chatId, $message, $telegram);

        if ($result === self::SUCCESS) {
            $this->writeHealthAlert(time());
        }

        return $result;
    }

    private function resolveChatId(): string
    {
        $chatId = trim((string) $this->option('chat-id'));

        if ($chatId === '') {
            $chatId = trim((string) config('services.telegram.chat_id', ''));
        }

        return $chatId;
    }

    private function deliver(string $chatId, string $message, TelegramService $telegram): int
    {
        foreach ($this->chunkMessage($message) as $part) {
            if (! $telegram->sendMessage($chatId, $part, 'HTML')) {
                $error = $telegram->lastError() ?: 'unknown Telegram error';

                $this->error('Failed to send digest: ' . $error);
                Log::error('Error digest could not be sent to Telegram.', [
                    'chatId' => $chatId,
                    'error' => $error,
                ]);

                return self::FAILURE;
            }
        }

        $this->info('Report sent to Telegram chat ' . $chatId . '.');

        return self::SUCCESS;
    }

    /* ---------------------------------------------------------------------
     | Server health
     * ------------------------------------------------------------------- */

    /**
     * Probe disk, load, memory, core services and the database, then return a
     * rolled-up status plus the individual checks.
     *
     * @return array{status:string, checks:array<int, array{label:string, status:string, detail:string}>}
     */
    private function serverHealth(): array
    {
        $checks = [];

        // --- Root filesystem -------------------------------------------------
        $pct = $this->diskUsage('/');

        if ($pct === null) {
            $checks[] = $this->check('Disk /', 'warn', 'could not be read');
        } elseif ($pct >= 90) {
            $checks[] = $this->check('Disk /', 'crit', "{$pct}% full (critical >= 90%)");
        } elseif ($pct >= 80) {
            $checks[] = $this->check('Disk /', 'warn', "{$pct}% full (warning >= 80%)");
        } else {
            $checks[] = $this->check('Disk /', 'ok', "{$pct}% used");
        }

        // --- Load average ----------------------------------------------------
        $cores = $this->coreCount();
        $load = $this->loadAverage();

        if ($load === null || $cores === null) {
            $checks[] = $this->check('Load', 'warn', 'could not be read');
        } elseif ($load > $cores * 2) {
            $checks[] = $this->check('Load', 'crit', sprintf('%.2f on %d cores', $load, $cores));
        } elseif ($load > $cores) {
            $checks[] = $this->check('Load', 'warn', sprintf('%.2f on %d cores', $load, $cores));
        } else {
            $checks[] = $this->check('Load', 'ok', sprintf('%.2f on %d cores', $load, $cores));
        }

        // --- Memory ----------------------------------------------------------
        $memory = $this->memory();

        if ($memory === null) {
            $checks[] = $this->check('Memory', 'warn', 'could not be read');
        } else {
            $availableMb = $memory['available'];

            if ($availableMb < 300) {
                $checks[] = $this->check('Memory', 'crit', "{$availableMb} MB available of {$memory['total']} MB");
            } elseif ($availableMb < 800) {
                $checks[] = $this->check('Memory', 'warn', "{$availableMb} MB available of {$memory['total']} MB");
            } else {
                $checks[] = $this->check('Memory', 'ok', "{$availableMb} MB available of {$memory['total']} MB");
            }
        }

        // --- Core services ---------------------------------------------------
        foreach (['nginx', 'mysql', 'php8.3-fpm', 'redis-server'] as $service) {
            $state = $this->serviceState($service);

            if ($state === 'unknown') {
                // Not installed on this box - not a failure.
                continue;
            }

            $checks[] = $this->check(
                $service,
                $state === 'active' ? 'ok' : 'crit',
                $state
            );
        }

        // --- Database connectivity -------------------------------------------
        try {
            DB::connection()->getPdo();
            $checks[] = $this->check('Database', 'ok', 'connected');
        } catch (\Throwable $e) {
            $checks[] = $this->check('Database', 'crit', Str::limit($e->getMessage(), 120, '…'));
        }

        // --- Uptime (informational) -------------------------------------------
        $uptime = $this->uptime();

        if ($uptime !== null) {
            $checks[] = $this->check('Uptime', 'ok', $uptime);
        }

        $statuses = array_column($checks, 'status');
        $status = in_array('crit', $statuses, true)
            ? 'crit'
            : (in_array('warn', $statuses, true) ? 'warn' : 'ok');

        return ['status' => $status, 'checks' => $checks];
    }

    private function check(string $label, string $status, string $detail): array
    {
        return ['label' => $label, 'status' => $status, 'detail' => $detail];
    }

    private function buildHealthMessage(array $health): string
    {
        $emoji = ['ok' => '✅', 'warn' => '🟡', 'crit' => '🔴'][$health['status']];
        $label = ['ok' => 'HEALTHY', 'warn' => 'WARNING', 'crit' => 'CRITICAL'][$health['status']];

        $lines = [
            sprintf('%s <b>Server health: %s</b>', $emoji, $label),
            '<b>Host:</b> ' . $this->escape(gethostname() ?: 'unknown'),
            '<b>Time:</b> ' . now()->format('Y-m-d H:i:s'),
            '',
        ];

        foreach ($health['checks'] as $check) {
            $icon = ['ok' => '✅', 'warn' => '⚠️', 'crit' => '❌'][$check['status']];

            // Healthy lines stay terse; problems get spelled out.
            $detail = $check['status'] === 'ok'
                ? $check['detail']
                : '<b>' . $this->escape($check['detail']) . '</b>';

            $lines[] = sprintf('%s <b>%s</b> — %s', $icon, $this->escape($check['label']), $detail);
        }

        if ($health['status'] !== 'ok') {
            $lines[] = '';
            $lines[] = 'Action required: check the server.';
        }

        return implode("\n", $lines);
    }

    private function diskUsage(string $path): ?int
    {
        $output = $this->probe(sprintf('df -P %s 2>/dev/null | tail -1', escapeshellarg($path)));

        // df -P columns end with the mount point, so anchor on the % in-line.
        if ($output === '' || ! preg_match('/(\d+)\s*%/', $output, $m)) {
            return null;
        }

        return (int) $m[1];
    }

    private function coreCount(): ?int
    {
        $output = $this->probe('nproc 2>/dev/null');

        return $output === '' ? null : max(1, (int) $output);
    }

    private function loadAverage(): ?float
    {
        $raw = @file_get_contents('/proc/loadavg');

        if ($raw === false) {
            return null;
        }

        $parts = explode(' ', trim($raw));

        return isset($parts[0]) && is_numeric($parts[0]) ? (float) $parts[0] : null;
    }

    private function memory(): ?array
    {
        $raw = @file_get_contents('/proc/meminfo');

        if ($raw === false) {
            return null;
        }

        $values = [];

        foreach (explode("\n", $raw) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                $values[$m[1]] = (int) $m[2];
            }
        }

        if (! isset($values['MemTotal'])) {
            return null;
        }

        $available = $values['MemAvailable']
            ?? ($values['MemFree'] ?? 0) + ($values['Buffers'] ?? 0) + ($values['Cached'] ?? 0);

        return [
            'total' => (int) round($values['MemTotal'] / 1024),
            'available' => (int) round($available / 1024),
        ];
    }

    private function serviceState(string $service): string
    {
        $output = $this->probe(sprintf('systemctl is-active %s 2>/dev/null', escapeshellarg($service)));

        if ($output === '') {
            // systemctl missing or the unit is not installed.
            $output = $this->probe(sprintf('service %s status 2>/dev/null | head -1', escapeshellarg($service)));

            if ($output === '') {
                return 'unknown';
            }

            return stripos($output, 'is running') !== false ? 'active' : 'inactive';
        }

        if (stripos($output, 'not-found') !== false || stripos($output, 'inactive') !== false && strpos($output, 'dead') === false) {
            // Distinguish "unit does not exist" from "unit stopped".
            $loaded = $this->probe(sprintf('systemctl list-unit-files %s 2>/dev/null', escapeshellarg($service)));

            if (trim($loaded) === '' || strpos($loaded, $service) === false) {
                return 'unknown';
            }
        }

        $first = strtok($output, "\n");

        if ($first === false || stripos($first, 'unknown') !== false) {
            return 'unknown';
        }

        return trim($first);
    }

    private function uptime(): ?string
    {
        $raw = @file_get_contents('/proc/uptime');

        if ($raw === false) {
            return null;
        }

        $seconds = (int) (float) explode(' ', trim($raw))[0];
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return "{$days}d {$hours}h {$minutes}m";
    }

    private function probe(string $command): string
    {
        try {
            $process = new Process(['bash', '-c', $command]);
            $process->setTimeout(8);
            $process->run();

            return trim($process->getOutput());
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function healthAlertDue(): bool
    {
        $last = (int) $this->readHealthAlert();

        return $last === 0 || (time() - $last) >= self::HEALTH_ALERT_COOLDOWN;
    }

    private function readHealthAlert(): int
    {
        $path = storage_path(self::HEALTH_STATE_FILE);

        return is_file($path) ? (int) json_decode((string) file_get_contents($path), true) : 0;
    }

    private function writeHealthAlert(int $timestamp): void
    {
        $path = storage_path(self::HEALTH_STATE_FILE);

        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, (string) $timestamp);
    }

    private function clearHealthAlert(): void
    {
        $path = storage_path(self::HEALTH_STATE_FILE);

        if (is_file($path)) {
            @unlink($path);
        }
    }

    /* ---------------------------------------------------------------------
     | Log scanning
     * ------------------------------------------------------------------- */

    /**
     * Read every laravel-*.log file and return the entries written since the
     * stored cursor. Entries are keyed by a normalised signature so repeats of
     * the same error collapse into a single line with a count.
     */
    private function collectEntries(array $files, array $state): array
    {
        // On the very first run there is no cursor, so fall back to a time
        // window instead of replaying the entire log history.
        $since = now()->subHours((int) $this->option('hours'))->getTimestamp();
        $entries = [];

        foreach ($files as $file) {
            $offset = max(0, (int) ($state[$file] ?? 0));

            $handle = @fopen($file, 'rb');

            if ($handle === false) {
                continue;
            }

            fseek($handle, $offset);

            $pending = null;

            while (($line = fgets($handle)) !== false) {
                if (($match = $this->matchEntry($line)) !== null) {
                    $this->flushEntry($pending, $entries, $since);
                    $pending = $match;
                    continue;
                }

                // Continuation line of the current entry (stack trace, context).
                if ($pending !== null) {
                    $pending['raw'] .= $line;
                    $pending['body'] .= $line;
                }
            }

            $this->flushEntry($pending, $entries, $since);
            fclose($handle);
        }

        krsort($entries);

        return $entries;
    }

    /**
     * @return array{time:int, level:string, message:string, headline:string, context:array, raw:string}|null
     */
    private function matchEntry(string $line): ?array
    {
        if (! preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})]\s+\S+\.([A-Z]+):\s*(.*)$/m', $line, $m)) {
            return null;
        }

        if (! in_array($m[2], self::LEVELS, true)) {
            return null;
        }

        return [
            'time' => strtotime($m[1]),
            'level' => $m[2],
            'body' => trim($m[3]),
            'raw' => $line,
        ];
    }

    /**
     * Separate "Human readable text. {json context}" into the text and the
     * decoded context array, so the digest shows the text instead of the blob.
     *
     * @return array{0:string, 1:array}
     */
    private function splitMessage(string $message): array
    {
        $start = strpos($message, '{');

        if ($start === false) {
            return [$message, []];
        }

        $headline = trim(substr($message, 0, $start));

        $json = substr($message, $start);

        // Exception payloads embed raw newlines/tabs inside JSON strings, which
        // makes them undecodable as-is. Strip control characters first.
        $context = json_decode(preg_replace('/[\x00-\x1F]/', ' ', $json), true);

        if (! is_array($context)) {
            // Trailing stack trace made the JSON invalid - keep the whole text.
            return [$message, []];
        }

        return [$headline === '' ? $message : $headline, $context];
    }

    private function flushEntry(?array $entry, array &$entries, int $since): void
    {
        if ($entry === null || $entry['time'] < $since) {
            return;
        }

        // Split only now, once every continuation line has been appended - the
        // JSON context often spans several lines when it carries a stack trace.
        [$headline, $context] = $this->splitMessage($entry['body']);

        $signature = $this->signature($headline);

        if (! isset($entries[$signature])) {
            $entries[$signature] = [
                'signature' => $signature,
                'headline' => $headline,
                'level' => $entry['level'],
                'count' => 0,
                'first' => $entry['time'],
                'last' => $entry['time'],
                'samples' => [],
            ];
        }

        $entries[$signature]['count']++;
        $entries[$signature]['first'] = min($entries[$signature]['first'], $entry['time']);
        $entries[$signature]['last'] = max($entries[$signature]['last'], $entry['time']);

        // Keep a couple of concrete examples (chat ID, status code, ...) so the
        // grouped line still says where the errors actually happened.
        $sample = $this->formatContext($context);

        if ($sample !== '' && ! in_array($sample, $entries[$signature]['samples'], true)
            && count($entries[$signature]['samples']) < 3) {
            $entries[$signature]['samples'][] = $sample;
        }
    }

    /**
     * Reduce a message to a stable fingerprint: session IDs, hashes, IPs, URLs,
     * quoted values and numbers become placeholders so the same error type
     * groups together.
     */
    private function signature(string $headline): string
    {
        $headline = Str::limit($headline, 400, '');

        $headline = preg_replace('/sess_[A-Za-z0-9]+/', 'sess_<id>', $headline);
        $headline = preg_replace('/\b[0-9a-f]{32,}\b/i', '<hash>', $headline);
        $headline = preg_replace('/https?:\/\/\S+/', '<url>', $headline);
        $headline = preg_replace('/\b\d{1,3}(\.\d{1,3}){3}\b/', '<ip>', $headline);
        $headline = preg_replace('/"[^"]*"/', '"<value>"', $headline);
        $headline = preg_replace('/\b\d+\b/', '#', $headline);
        $headline = preg_replace('/\s+/', ' ', $headline);

        return trim($headline);
    }

    /** Compress the JSON context into one short, readable example line. */
    private function formatContext(array $context): string
    {
        $parts = [];

        if (isset($context['chatId'])) {
            $parts[] = 'chat ' . $context['chatId'];
        }

        if (isset($context['method'])) {
            $parts[] = (string) $context['method'];
        }

        if (isset($context['type'])) {
            $parts[] = (string) $context['type'];
        }

        if (isset($context['actionKey'])) {
            $parts[] = 'action ' . $context['actionKey'];
        }

        if (isset($context['branchName']) && $context['branchName'] !== '') {
            $parts[] = 'branch ' . $context['branchName'];
        }

        if (isset($context['departmentName']) && $context['departmentName'] !== '') {
            $parts[] = 'dept ' . $context['departmentName'];
        }

        // The Telegram API reports its reason inside the response body.
        if (isset($context['body'])) {
            $body = json_decode((string) $context['body'], true);

            if (is_array($body)) {
                if (isset($body['error_code'])) {
                    $parts[] = 'HTTP ' . $body['error_code'];
                }

                if (isset($body['description'])) {
                    $parts[] = Str::limit((string) $body['description'], 80, '…');
                }
            } elseif (isset($context['status'])) {
                $parts[] = 'HTTP ' . $context['status'];
            }
        } elseif (isset($context['status'])) {
            $parts[] = 'HTTP ' . $context['status'];
        }

        if ($parts === [] && isset($context['exception'])) {
            $parts[] = $this->compactException((string) $context['exception']);
        }

        return implode(' · ', $parts);
    }

    /**
     * Turn a verbose exception dump into its useful core, e.g.
     * "[object] (RuntimeException(code: 0): Telegram sendPhoto failed ... at /var/...:180)"
     * becomes "RuntimeException: Telegram sendPhoto failed ...".
     */
    private function compactException(string $exception): string
    {
        $exception = preg_replace('/\s+/', ' ', $exception);
        $exception = preg_replace('/^.*?\((\w+)\(code: \d+\):\s*/', '$1: ', $exception);
        $exception = preg_replace('/\s+at\s+\/\S+.*$/', '', $exception);
        $exception = preg_replace('/^\[object\]\s*/', '', $exception);

        return Str::limit(trim($exception), 130, '…');
    }

    /* ---------------------------------------------------------------------
     | Digest rendering
     * ------------------------------------------------------------------- */

    private function buildHealthyDigest(array $health, bool $firstRun): string
    {
        $lines = [
            '✅ <b>HRMS Error Digest</b>',
            $firstRun
                ? 'No errors found in the last ' . $this->option('hours') . ' hours.'
                : 'No new errors since the previous digest.',
            '<b>Generated:</b> ' . now()->format('Y-m-d H:i:s'),
            '',
        ];

        return implode("\n", $lines) . "\n\n" . $this->healthSection($health);
    }

    private function buildDigest(array $entries, array $health, bool $firstRun): string
    {
        $total = array_sum(array_column($entries, 'count'));
        $errors = array_sum(array_column(array_filter(
            $entries,
            fn (array $e) => $e['level'] !== 'WARNING'
        ), 'count'));
        $warnings = $total - $errors;

        $span = $firstRun ? 'last ' . $this->option('hours') . ' hours' : 'since previous digest';

        $lines = [
            '<b>📋 HRMS Error Digest</b>',
            '<b>Period:</b> ' . $span,
            '<b>Unique:</b> ' . count($entries) . '  |  <b>Errors:</b> ' . $errors . '  |  <b>Warnings:</b> ' . $warnings,
            '<b>Generated:</b> ' . now()->format('Y-m-d H:i:s'),
            '',
            $this->healthSection($health),
            '',
            '<b>── Errors ──</b>',
            '',
        ];

        // Busiest errors first so the digest leads with what matters.
        $sorted = collect($entries)
            ->sortByDesc(fn (array $e) => $e['count'])
            ->values();

        foreach ($sorted as $index => $entry) {
            $lines[] = sprintf(
                '<b>%d.</b> [%s] ×%d — %s',
                $index + 1,
                $entry['level'],
                $entry['count'],
                $this->escape(Str::limit($entry['headline'], 250, '…'))
            );

            foreach ($entry['samples'] as $sample) {
                $lines[] = '    ↳ ' . $this->escape($sample);
            }

            $lines[] = '    <i>last: ' . date('m-d H:i', $entry['last']) . '</i>';
            $lines[] = '';
        }

        $lines[] = 'Full details: storage/logs/laravel-' . now()->format('Y-m-d') . '.log';

        return implode("\n", $lines);
    }

    private function healthSection(array $health): string
    {
        $emoji = ['ok' => '✅', 'warn' => '🟡', 'crit' => '🔴'][$health['status']];
        $label = ['ok' => 'HEALTHY', 'warn' => 'WARNING', 'crit' => 'CRITICAL'][$health['status']];

        $lines = [sprintf('<b>🖥 Server:</b> %s %s', $emoji, $label)];

        foreach ($health['checks'] as $check) {
            if ($check['status'] === 'ok') {
                continue;
            }

            $icon = $check['status'] === 'warn' ? '⚠️' : '❌';
            $lines[] = sprintf(
                '  %s <b>%s</b> — %s',
                $icon,
                $this->escape($check['label']),
                $this->escape($check['detail'])
            );
        }

        if ($health['status'] === 'ok') {
            $summary = collect($health['checks'])
                ->filter(fn (array $c) => in_array($c['label'], ['Disk /', 'Load', 'Memory', 'Uptime'], true))
                ->map(fn (array $c) => $this->escape($c['label']) . ': ' . $this->escape($c['detail']))
                ->implode('  ·  ');

            $lines[] = '  <i>' . $summary . '</i>';
        }

        return implode("\n", $lines);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Split on line boundaries so no single part exceeds Telegram's 4096
     * character message limit.
     */
    private function chunkMessage(string $message): array
    {
        if (mb_strlen($message) <= self::MESSAGE_LIMIT) {
            return [$message];
        }

        $chunks = [];
        $current = '';

        foreach (explode("\n", $message) as $line) {
            $candidate = $current === '' ? $line : $current . "\n" . $line;

            if (mb_strlen($candidate) > self::MESSAGE_LIMIT - 1) {
                $chunks[] = $current;
                $current = $line;
                continue;
            }

            $current = $candidate;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /** @return string[] */
    private function logFiles(): array
    {
        $files = glob(storage_path('logs/laravel-*.log')) ?: [];
        sort($files);

        return $files;
    }

    /** Byte offset of the end of each log file, used as the next-run cursor. */
    private function cursor(array $files): array
    {
        $cursor = [];

        foreach ($files as $file) {
            $size = @filesize($file);

            if ($size !== false) {
                $cursor[$file] = $size;
            }
        }

        return $cursor;
    }

    private function readState(): array
    {
        $path = storage_path(self::STATE_FILE);

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeState(array $cursor): void
    {
        $path = storage_path(self::STATE_FILE);

        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, json_encode($cursor, JSON_PRETTY_PRINT));
    }
}
