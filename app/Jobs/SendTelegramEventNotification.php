<?php

namespace App\Jobs;

use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramEventNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public string $eventKey,
        public string $messageText,
        public ?string $parseMode = 'HTML',
        public ?string $branchName = null,
        public ?string $departmentName = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $photoPath = null
    ) {}

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(TelegramService $telegramService): void
    {
        $botToken = TelegramBotSettings::botToken();
        if ($botToken === '') {
            Log::info('Telegram notification skipped: Bot token not configured.', [
                'eventKey' => $this->eventKey,
            ]);
            return;
        }

        if ($this->photoPath && is_file($this->photoPath)) {
            $telegramService->sendPhotoToAction(
                $this->eventKey,
                $this->photoPath,
                $this->messageText,
                $this->parseMode,
                $this->branchName,
                $this->departmentName,
                $this->latitude,
                $this->longitude
            );
        } else {
            $telegramService->sendToAction(
                $this->eventKey,
                $this->messageText,
                $this->parseMode,
                $this->branchName,
                $this->departmentName,
                $this->latitude,
                $this->longitude
            );
        }
    }
}
