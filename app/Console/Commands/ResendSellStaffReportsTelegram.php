<?php

namespace App\Console\Commands;

use App\Models\SellOutReport;
use App\Models\TelegramGroup;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResendSellStaffReportsTelegram extends Command
{
    protected $signature = 'sell-staff-report:resend-telegram
        {--date= : Report date, defaults to today}
        {--from=15:00 : Start time for the selected date}
        {--dry-run : Show matching reports without sending Telegram messages}';

    protected $description = 'Resend Sell Staff Reports to Telegram for a date/time window';

    public function handle(TelegramService $telegramService): int
    {
        $date = $this->option('date') ?: now()->toDateString();
        $from = $this->option('from') ?: '15:00';

        try {
            $startAt = Carbon::parse($date . ' ' . $from);
            $endAt = Carbon::parse($date)->endOfDay();
        } catch (\Throwable $exception) {
            $this->error('Invalid --date or --from value.');

            return self::FAILURE;
        }

        $reports = SellOutReport::query()
            ->with(['lines', 'photos', 'user:id,name,employee_code,username'])
            ->whereBetween('created_at', [$startAt, $endAt])
            ->orderBy('created_at')
            ->get();

        if ($reports->isEmpty()) {
            $this->info('No Sell Staff Reports found from ' . $startAt->format('Y-m-d H:i:s') . '.');

            return self::SUCCESS;
        }

        $this->info('Found ' . $reports->count() . ' Sell Staff Reports from ' . $startAt->format('Y-m-d H:i:s') . '.');

        if ($this->option('dry-run')) {
            $reports->each(function (SellOutReport $report): void {
                $this->line("#{$report->id} {$report->invoice_no} {$report->service_type} {$report->branch_name} {$report->created_at}");
            });

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($reports as $report) {
            $ok = $this->sendReport($telegramService, $report);

            if ($ok) {
                $sent++;
                $this->line("Sent #{$report->id} {$report->invoice_no}");
                continue;
            }

            $failed++;
            $this->warn("Failed #{$report->id} {$report->invoice_no}: " . ($telegramService->lastError() ?: 'Unknown Telegram error.'));
        }

        $this->info("Done. Sent: {$sent}. Failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function sendReport(TelegramService $telegramService, SellOutReport $report): bool
    {
        $chatIds = $telegramService->chatIdsForAction(
            TelegramGroup::sellOutEventKeyForServiceType($report->service_type),
            (string) ($report->branch_name ?? ''),
            ''
        );

        if ($chatIds === []) {
            return false;
        }

        $message = $this->buildMessage($report);
        $photoPaths = $report->photos
            ->map(fn ($photo) => Storage::disk('public')->path($photo->photo_path))
            ->filter(fn (string $path): bool => is_file($path))
            ->values()
            ->all();

        $ok = true;
        foreach ($chatIds as $chatId) {
            $sent = $photoPaths !== []
                ? $telegramService->sendMediaGroup($chatId, $photoPaths, $message) !== null
                : $telegramService->sendMessage($chatId, $message);

            $ok = $sent && $ok;
        }

        return $ok;
    }

    private function buildMessage(SellOutReport $report): string
    {
        $lines = ['វិក្កយបត្រ'];
        $lines[] = "Invoice: {$report->invoice_no}";

        foreach ($report->lines as $line) {
            $lines[] = "ទំនិញ: {$line->product_name} ចំនួន{$line->qty} តម្លៃ: $"
                . number_format((float) $line->unit_price, 2);

            if (! empty($line->serial_number)) {
                $lines[] = "SN: {$line->serial_number}";
            }
        }

        $userId = $this->userIdNumber($report->user->employee_code ?? null);
        $sellerName = $report->seller_name ?: 'N/A';
        $branchName = $report->branch_name ?: 'N/A';
        $lines[] = "ID: {$userId} {$sellerName} (សាខា៖ {$branchName})";

        if ($report->customer_phone) {
            $lines[] = "ទំនាក់ទំនង: {$report->customer_phone}";
        }

        $phoneLast4 = $this->phoneLast4Digits($report->customer_phone);
        $remark = trim($userId . '-' . $phoneLast4, '-');
        if ($remark !== '') {
            $lines[] = "សម្គាល់: {$remark}";
        }

        return implode("\n", $lines);
    }

    private function userIdNumber(?string $employeeCode): string
    {
        $digits = ltrim(preg_replace('/\D/', '', (string) $employeeCode), '0');

        return $digits !== '' ? $digits : '0';
    }

    private function phoneLast4Digits(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return $digits !== '' ? Str::substr($digits, -4) : '';
    }
}
