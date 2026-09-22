<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupDatabaseToTelegram extends Command
{
    protected $signature = 'backup:database-telegram';

    protected $description = 'Backup the HRMS MySQL database and send it to Telegram';

    public function handle(TelegramService $telegram): int
    {
        $database = (string) config('database.connections.mysql.database');
        $username = (string) config('database.connections.mysql.username');
        $password = (string) config('database.connections.mysql.password');
        $host = (string) config('database.connections.mysql.host', '127.0.0.1');
        $port = (string) config('database.connections.mysql.port', '3306');

        if ($database === '' || $username === '') {
            $this->error('Database configuration is missing.');
            return self::FAILURE;
        }

        $chatId = '-930580993';

        if ($chatId === '') {
            $this->error('TELEGRAM_CHAT_ID is not configured.');
            return self::FAILURE;
        }

        $backupDir = storage_path('app/backup-temp');

        if (! is_dir($backupDir) && ! mkdir($backupDir, 0775, true) && ! is_dir($backupDir)) {
            $this->error('Unable to create backup directory: ' . $backupDir);
            return self::FAILURE;
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFile = $backupDir . "/hrms_database_{$timestamp}.sql";
        $gzipFile = $sqlFile . '.gz';

        $this->info('Creating database backup...');

        try {
            $command = [
                'mysqldump',
                '--host=' . $host,
                '--port=' . $port,
                '--user=' . $username,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--events',
                '--result-file=' . $sqlFile,
                $database,
            ];

            $process = new Process($command);
            $process->setTimeout(600);

            // Avoid exposing the database password in the process command line.
            $process->setEnv(array_merge($_ENV, [
                'MYSQL_PWD' => $password,
            ]));

            $process->run();

            if (! $process->isSuccessful()) {
                throw new \RuntimeException(
                    'mysqldump failed: ' . trim($process->getErrorOutput())
                );
            }

            if (! is_file($sqlFile) || filesize($sqlFile) === 0) {
                throw new \RuntimeException('Database dump was not created or is empty.');
            }

            $this->info('Compressing backup...');

            $gzip = new Process([
                'gzip',
                '-f',
                $sqlFile,
            ]);

            $gzip->setTimeout(300);
            $gzip->run();

            if (! $gzip->isSuccessful()) {
                throw new \RuntimeException(
                    'gzip failed: ' . trim($gzip->getErrorOutput())
                );
            }

            if (! is_file($gzipFile) || filesize($gzipFile) === 0) {
                throw new \RuntimeException('Compressed backup was not created.');
            }

            $sizeMb = round(filesize($gzipFile) / 1024 / 1024, 2);

            $this->info("Backup created: {$sizeMb} MB");
            $this->info('Sending database backup to Telegram...');

            $caption =
                "KNEAYERNG HRMS Database Backup\n" .
                "Database: {$database}\n" .
                "Date: " . now()->format('Y-m-d H:i:s') . "\n" .
                "Size: {$sizeMb} MB";

            $sent = $telegram->sendDocument(
                $chatId,
                $gzipFile,
                basename($gzipFile),
                $caption
            );

            if (! $sent) {
                throw new \RuntimeException(
                    $telegram->lastError() ?: 'Telegram sendDocument failed.'
                );
            }

            $this->info('Database backup sent to Telegram successfully.');

            Log::info('HRMS database backup sent to Telegram.', [
                'database' => $database,
                'file' => basename($gzipFile),
                'size_mb' => $sizeMb,
            ]);

            // Delete temporary backup only after Telegram confirms success.
            @unlink($gzipFile);

            $this->info('Temporary backup removed.');

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());

            Log::error('HRMS Telegram database backup failed.', [
                'error' => $e->getMessage(),
            ]);

            // Keep backup files when something fails for troubleshooting/recovery.
            $this->warn('Temporary backup files were kept if they were created.');

            return self::FAILURE;
        }
    }
}
