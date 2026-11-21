<?php

namespace App\Services\Maintenance;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemDiagnostics
{
    public const LOG_LIMIT = 50;

    private const LOG_DAYS = 7;
    private const LOG_BYTES_PER_FILE = 262144;

    public function __construct(private UserRepository $users) {}

    public function checks(): array
    {
        $checks = [
            'debug_mode' => $this->check(fn () => config('app.debug') ? 'warning' : 'passed'),
            'log_storage' => $this->check(fn () => is_writable(storage_path('logs')) ? 'passed' : 'error'),
        ];

        $checks['database'] = $this->check(function () {
            DB::select('SELECT 1');

            return 'passed';
        });

        $databaseAvailable = $checks['database']['status'] === 'passed';
        $checks['payday_queue'] = $databaseAvailable ? $this->check(fn () => Schema::hasColumns(
            'payroll_queue_sessions',
            ['department_id', 'target_employee_id', 'max_active_serving']
        ) && Schema::hasTable('payroll_queue_tickets') && Schema::hasTable('payroll_queue_invites') ? 'passed' : 'error') : ['status' => 'unavailable'];
        $checks['salary_directory'] = $databaseAvailable ? $this->check(function () {
            $count = $this->users->countActiveEmployeesExcludedFromSalaryDirectory();

            return $count ? ['status' => 'warning', 'details' => ['count' => $count]] : 'passed';
        }) : ['status' => 'unavailable'];

        return $checks;
    }

    private function check(callable $run): array
    {
        try {
            $result = $run();

            return is_array($result) ? $result : ['status' => $result];
        } catch (Throwable) {
            return ['status' => 'unavailable'];
        }
    }

    public function recentLogs(?string $severity): array
    {
        $files = [];
        foreach (['daily', 'single'] as $driver) {
            $path = config("logging.channels.{$driver}.path");
            if (!is_string($path) || $path === '') {
                continue;
            }
            if ($driver === 'single') {
                $files[] = $path;
                continue;
            }
            for ($day = 0; $day < self::LOG_DAYS; $day++) {
                $files[] = dirname($path).'/'.pathinfo($path, PATHINFO_FILENAME).'-'
                    .now()->subDays($day)->toDateString().'.'.pathinfo($path, PATHINFO_EXTENSION);
            }
        }

        $entries = [];
        $unreadable = false;
        $cutoff = now()->subDays(self::LOG_DAYS - 1)->startOfDay()->toDateTimeString();
        foreach (array_unique($files) as $file) {
            if (!is_file($file)) {
                continue;
            }
            $contents = $this->readTail($file);
            if ($contents === null) {
                $unreadable = true;
                continue;
            }

            preg_match_all('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] [\w.-]+\.(WARNING|ERROR|CRITICAL|ALERT|EMERGENCY): ([^\r\n]*)/m', $contents, $matches, PREG_SET_ORDER);
            foreach ($matches as [, $timestamp, $level, $message]) {
                $entrySeverity = $level === 'WARNING' ? 'warning' : 'error';
                if ($timestamp < $cutoff || ($severity && $severity !== $entrySeverity)) {
                    continue;
                }

                $entries[] = [
                    'timestamp' => $timestamp,
                    'level' => strtolower($level),
                    'category' => $this->categorize($message),
                ];
            }
        }

        usort($entries, fn ($a, $b) => strcmp($b['timestamp'], $a['timestamp']));

        return ['entries' => array_slice($entries, 0, self::LOG_LIMIT), 'unreadable' => $unreadable];
    }

    private function readTail(string $file): ?string
    {
        $stream = @fopen($file, 'rb');
        if ($stream === false) {
            return null;
        }

        try {
            $size = fstat($stream)['size'] ?? 0;
            if ($size > self::LOG_BYTES_PER_FILE) {
                if (fseek($stream, -self::LOG_BYTES_PER_FILE, SEEK_END) !== 0) {
                    return null;
                }
                fgets($stream, self::LOG_BYTES_PER_FILE);
            }

            $contents = stream_get_contents($stream, self::LOG_BYTES_PER_FILE);

            return $contents === false ? null : $contents;
        } finally {
            fclose($stream);
        }
    }

    private function categorize(string $message): string
    {
        return match (true) {
            str_contains($message, 'SQLSTATE[42S02]'), str_contains($message, 'SQLSTATE[42S22]') => 'schema',
            str_contains($message, 'SQLSTATE[HY000] [2002]'), str_contains($message, 'SQLSTATE[28000]') => 'database_connection',
            str_contains($message, 'SQLSTATE[') => 'database_query',
            str_starts_with($message, 'Firebase ') => 'firebase',
            str_contains($message, 'Permission denied'), str_contains($message, 'No space left on device') => 'filesystem',
            default => 'application',
        };
    }
}
