<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellOutReport;
use App\Models\SellOutReportLine;
use App\Models\TelegramGroup;
use App\Requests\SellOutReport\StoreSellOutReportRequest;
use App\Requests\SellOutReport\UpdateSellOutReportRequest;
use App\Resources\SellOutReport\SellOutReportResource;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SellOutReportController extends Controller
{
    public function __construct(private TelegramService $telegramService)
    {
    }

    public function index(): JsonResponse
    {
        $reports = SellOutReport::query()
            ->where('user_id', auth()->id())
            ->with(['lines', 'photos'])
            ->withCount(['lines', 'photos'])
            ->latest()
            ->get()
            ->map(fn (SellOutReport $report) => (new SellOutReportResource($report))->toArray(request()));

        return response()->json([
            'status' => true,
            'data' => $reports,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $report = SellOutReport::query()
            ->where('user_id', auth()->id())
            ->with(['lines', 'photos'])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => (new SellOutReportResource($report))->toArray(request()),
        ]);
    }

    public function store(StoreSellOutReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $report = DB::transaction(function () use ($request, $validated) {
            $totalAmount = collect($validated['lines'])->sum(function (array $line): float {
                return round((int) $line['qty'] * (float) $line['unit_price'], 2);
            });
            $totalQty = collect($validated['lines'])->sum(fn (array $line): int => (int) $line['qty']);
            $commission = $validated['commission'] ?? ($totalQty * 0.25);

            $report = SellOutReport::create([
                'user_id' => auth()->id(),
                'invoice_no' => $this->generateInvoiceNo(),
                'original_invoice_no' => $this->resolveOriginalInvoiceNo($validated),
                'seller_name' => $validated['seller_name'],
                'branch_name' => $validated['branch_name'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'service_type' => $this->filledString($validated['service_type'] ?? null, 'Sale'),
                'payment_method' => $this->filledString($validated['payment_method'] ?? null, 'Cash'),
                'note' => $validated['note'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'total_amount' => round($totalAmount, 2),
                'commission' => round((float) $commission, 2),
            ]);

            foreach ($validated['lines'] as $index => $line) {
                $qty = (int) $line['qty'];
                $unitPrice = (float) $line['unit_price'];

                $lineModel = $report->lines()->create([
                    'product_name' => $line['product_name'],
                    'sku' => $line['sku'] ?? null,
                    'imei' => $line['imei'] ?? null,
                    'imei2' => $line['imei2'] ?? null,
                    'serial_number' => $line['serial_number'] ?? null,
                    'model_number' => $line['model_number'] ?? null,
                    'color' => $line['color'] ?? null,
                    'storage' => $line['storage'] ?? null,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($qty * $unitPrice, 2),
                ]);

                $this->storeLinePhotos($report, $lineModel, $request, $index);
            }

            foreach ($request->file('photos', []) as $photo) {
                $photoPath = $photo->store('sell-out-reports', 'public');

                $report->photos()->create([
                    'photo_path' => $photoPath,
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            return $report;
        });

        $report->load(['lines', 'photos', 'user'])->loadCount(['lines', 'photos']);

        // Telegram delivery involves uploading every photo over the network;
        // deferring it until after the response is sent keeps submission
        // latency down to just the DB write, regardless of photo count/size.
        dispatch(function () use ($report) {
            $this->sendSellOutReportTelegram($report);
        })->afterResponse();

        return response()->json([
            'status' => true,
            'message' => 'Sell Out Report submitted successfully!',
            'data' => (new SellOutReportResource($report))->toArray(request()),
        ], 201);
    }

    public function update(UpdateSellOutReportRequest $request, int $id): JsonResponse
    {
        $report = SellOutReport::query()
            ->where('user_id', auth()->id())
            ->with(['lines', 'photos'])
            ->findOrFail($id);

        if (! $report->created_at->isToday()) {
            return response()->json([
                'status' => false,
                'message' => 'This report can no longer be updated because it was not created today.',
            ], 403);
        }

        $validated = $request->validated();

        $report = DB::transaction(function () use ($request, $validated, $report) {
            $totalAmount = collect($validated['lines'])->sum(function (array $line): float {
                return round((int) $line['qty'] * (float) $line['unit_price'], 2);
            });
            $totalQty = collect($validated['lines'])->sum(fn (array $line): int => (int) $line['qty']);
            $commission = $validated['commission'] ?? ($totalQty * 0.25);

            $report->update([
                'seller_name' => $validated['seller_name'],
                'branch_name' => $validated['branch_name'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'service_type' => $this->filledString($validated['service_type'] ?? null, 'Sale'),
                'payment_method' => $this->filledString($validated['payment_method'] ?? null, 'Cash'),
                'note' => $validated['note'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'total_amount' => round($totalAmount, 2),
                'commission' => round((float) $commission, 2),
            ]);

            $report->lines()->delete();

            foreach ($validated['lines'] as $index => $line) {
                $qty = (int) $line['qty'];
                $unitPrice = (float) $line['unit_price'];

                $lineModel = $report->lines()->create([
                    'product_name' => $line['product_name'],
                    'sku' => $line['sku'] ?? null,
                    'imei' => $line['imei'] ?? null,
                    'imei2' => $line['imei2'] ?? null,
                    'serial_number' => $line['serial_number'] ?? null,
                    'model_number' => $line['model_number'] ?? null,
                    'color' => $line['color'] ?? null,
                    'storage' => $line['storage'] ?? null,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($qty * $unitPrice, 2),
                ]);

                $this->storeLinePhotos($report, $lineModel, $request, $index);
            }

            foreach ($request->file('photos', []) as $photo) {
                $photoPath = $photo->store('sell-out-reports', 'public');

                $report->photos()->create([
                    'photo_path' => $photoPath,
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            return $report;
        });

        $report->load(['lines', 'photos', 'user'])->loadCount(['lines', 'photos']);

        return response()->json([
            'status' => true,
            'message' => 'Sell Out Report updated successfully!',
            'data' => (new SellOutReportResource($report))->toArray(request()),
        ]);
    }

    public function destroyPhoto(int $id, int $photoId): JsonResponse
    {
        $report = SellOutReport::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (! $report->created_at->isToday()) {
            return response()->json([
                'status' => false,
                'message' => 'This report can no longer be updated because it was not created today.',
            ], 403);
        }

        $photo = $report->photos()->findOrFail($photoId);

        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();

        return response()->json([
            'status' => true,
            'message' => 'Photo removed successfully.',
        ]);
    }

    public function resendTelegram(int $id): JsonResponse
    {
        $report = SellOutReport::query()
            ->where('user_id', auth()->id())
            ->with(['lines', 'photos', 'user'])
            ->findOrFail($id);

        $sent = $this->sendSellOutReportTelegram($report);

        return response()->json([
            'status' => $sent,
            'message' => $sent
                ? 'Sell Out Report resent to Telegram successfully.'
                : 'Failed to resend the Sell Out Report to Telegram. Please try again.',
        ], $sent ? 200 : 500);
    }

    public function destroy(int $id): JsonResponse
    {
        $report = SellOutReport::query()
            ->where('user_id', auth()->id())
            ->with('photos')
            ->findOrFail($id);

        foreach ($report->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $report->delete();

        return response()->json([
            'status' => true,
        ]);
    }

    private function storeLinePhotos(
        SellOutReport $report,
        SellOutReportLine $line,
        Request $request,
        int $index
    ): void {
        foreach ($request->file("lines.$index.photos", []) as $photo) {
            $photoPath = $photo->store('sell-out-reports', 'public');

            $report->photos()->create([
                'sell_out_report_line_id' => $line->id,
                'photo_path' => $photoPath,
                'original_name' => $photo->getClientOriginalName(),
            ]);
        }
    }

    private function generateInvoiceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'SO-' . $date . '-';
        $lastInvoiceNo = SellOutReport::query()
            ->lockForUpdate()
            ->where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $nextNumber = 1;
        if ($lastInvoiceNo) {
            $nextNumber = ((int) substr($lastInvoiceNo, -4)) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function resolveOriginalInvoiceNo(array $validated): ?string
    {
        if (! empty($validated['original_invoice_no'])) {
            return trim($validated['original_invoice_no']);
        }

        foreach ([$validated['note'] ?? '', $validated['extracted_text'] ?? ''] as $text) {
            $invoiceNo = $this->extractInvoiceNo((string) $text);

            if ($invoiceNo !== null) {
                return $invoiceNo;
            }
        }

        return null;
    }

    private function extractInvoiceNo(string $text): ?string
    {
        $patterns = [
            '/\b(?:original\s+)?invoice\s*(?:no\.?|number|#)?\s*[:\-]\s*([A-Za-z0-9][A-Za-z0-9\-_.\/]{2,})/i',
            '/\binv\s*(?:no\.?|number|#)?\s*[:\-]\s*([A-Za-z0-9][A-Za-z0-9\-_.\/]{2,})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1], " \t\n\r\0\x0B.,;:");
            }
        }

        return null;
    }

    private function filledString(?string $value, string $default): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $default;
    }

    private function sendSellOutReportTelegram(SellOutReport $report): bool
    {
        try {
            $message = $this->buildSellOutTelegramMessage($report);
            $photoPaths = $report->photos
                ->map(fn ($photo) => Storage::disk('public')->path($photo->photo_path))
                ->filter(fn (string $path): bool => is_file($path))
                ->values()
                ->all();

            $chatIds = $this->telegramService->chatIdsForAction(
                TelegramGroup::sellOutEventKeyForServiceType($report->service_type),
                (string) ($report->branch_name ?? ''),
                ''
            );

            foreach ($chatIds as $chatId) {
                if ($photoPaths !== []) {
                    $this->telegramService->sendMediaGroup($chatId, $photoPaths, $message);
                } else {
                    $this->telegramService->sendMessage($chatId, $message);
                }
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Sell out report Telegram notification failed.', [
                'sell_out_report_id' => $report->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function buildSellOutTelegramMessage(SellOutReport $report): string
    {
        $lines = ['🛒 វិក្កយបត្រ'];
        $lines[] = "Invoice: {$report->invoice_no}";

        foreach ($report->lines as $line) {
            $lines[] = "ទំនិញ: {$line->product_name} ចំនួន{$line->qty} តម្លៃ: \$"
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

        return $digits !== '' ? substr($digits, -4) : '';
    }
}
