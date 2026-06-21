<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\SellOutReport;
use App\Models\SellOutReportLine;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SellOutReportController extends Controller
{
    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $reports = SellOutReport::query()
            ->withCount(['lines', 'photos'])
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        $reports->getCollection()->transform(fn (SellOutReport $report) => $this->formatReportSummary($report));

        return AppHelper::sendSuccessResponse('Sell out reports found.', $reports);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateStoreRequest($request);

            DB::beginTransaction();

            $report = SellOutReport::create([
                'invoice_no' => $this->generateInvoiceNo(),
                'original_invoice_no' => $this->resolveOriginalInvoiceNo($validated),
                'user_id' => auth()->id(),
                'seller_name' => $this->resolveSellerName($validated),
                'branch_name' => $this->resolveBranchName($validated),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'note' => $validated['note'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($validated['lines'] as $lineData) {
                $identifier = $this->resolvePrimaryIdentifier($lineData);
                $qty = (float) ($lineData['qty'] ?? 1);
                $unitPrice = array_key_exists('unit_price', $lineData) && $lineData['unit_price'] !== null
                    ? (float) $lineData['unit_price']
                    : null;
                $subtotal = $unitPrice !== null ? round($qty * $unitPrice, 2) : null;
                $totalAmount += $subtotal ?? 0;

                $report->lines()->create([
                    'product_id' => $lineData['product_id'] ?? null,
                    'variation_id' => $lineData['variation_id'] ?? null,
                    'product_name' => $lineData['product_name'] ?? null,
                    'sku' => $lineData['sku'] ?? null,
                    'identifier_type' => $identifier['type'],
                    'primary_identifier' => $identifier['value'],
                    'imei' => $lineData['imei'] ?? null,
                    'imei2' => $lineData['imei2'] ?? null,
                    'serial_number' => $lineData['serial_number'] ?? null,
                    'model_number' => $lineData['model_number'] ?? null,
                    'color' => $lineData['color'] ?? null,
                    'storage' => $lineData['storage'] ?? null,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            $report->update(['total_amount' => round($totalAmount, 2)]);

            foreach ($request->file('photos', []) as $photo) {
                $path = $photo->store('sell_out_reports', 'public');

                $report->photos()->create([
                    'photo_path' => $path,
                    'photo_url' => Storage::disk('public')->url($path),
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            $report->load(['lines', 'photos']);

            DB::commit();

            $telegramMessageId = $this->sendTelegramNotification($report);

            if ($telegramMessageId !== null) {
                $report->update(['telegram_message_id' => (string) $telegramMessageId]);
                $report->telegram_message_id = (string) $telegramMessageId;
            }

            return response()->json([
                'status' => true,
                'message' => 'Sell out report created successfully.',
                'status_code' => 201,
                'data' => $this->formatReportDetail($report->fresh(['lines', 'photos'])->loadCount(['lines', 'photos'])),
            ], 201);
        } catch (ValidationException $e) {
            return AppHelper::sendErrorResponse('Validation failed.', 422, $e->errors());
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Sell out report creation failed.', [
                'exception' => $e->getMessage(),
            ]);

            return AppHelper::sendErrorResponse('Unable to create sell out report.', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $report = SellOutReport::with(['lines', 'photos', 'user'])->find($id);

        if (! $report) {
            return AppHelper::sendErrorResponse('Sell out report not found.', 404);
        }

        $report->loadCount(['lines', 'photos']);

        return AppHelper::sendSuccessResponse('Sell out report found.', $this->formatReportDetail($report));
    }

    public function destroy(int $id): JsonResponse
    {
        $report = SellOutReport::with('photos')->find($id);

        if (! $report) {
            return AppHelper::sendErrorResponse('Sell out report not found.', 404);
        }

        foreach ($report->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $report->delete();

        return AppHelper::sendSuccessResponse('Sell out report deleted successfully.');
    }

    /**
     * @throws ValidationException
     */
    private function validateStoreRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'seller_name' => ['nullable', 'string', 'max:255'],
            'original_invoice_no' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'extracted_text' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['nullable', 'integer'],
            'lines.*.variation_id' => ['nullable', 'integer'],
            'lines.*.product_name' => ['nullable', 'string', 'max:255'],
            'lines.*.sku' => ['nullable', 'string', 'max:255'],
            'lines.*.imei' => ['nullable', 'string', 'max:255'],
            'lines.*.imei2' => ['nullable', 'string', 'max:255'],
            'lines.*.serial_number' => ['nullable', 'string', 'max:255'],
            'lines.*.model_number' => ['nullable', 'string', 'max:255'],
            'lines.*.color' => ['nullable', 'string', 'max:255'],
            'lines.*.storage' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'min:1'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $lines = $request->input('lines', []);

            if (! is_array($lines)) {
                return;
            }

            $seenImeis = [];
            $seenSerials = [];
            $seenPrimaryIdentifiers = [];
            $primaryIdentifiersByIndex = [];
            $imeisByIndex = [];
            $serialsByIndex = [];

            foreach ($lines as $index => $line) {
                if (! is_array($line)) {
                    continue;
                }

                $line = $this->trimLineValues($line);
                $identifier = $this->resolvePrimaryIdentifier($line);

                if ($identifier['value'] === null) {
                    $validator->errors()->add("lines.$index.sku", 'SKU is required when IMEI and Serial Number are empty.');
                    continue;
                }

                $primaryIdentifiersByIndex[$index] = $identifier['value'];

                if ($identifier['value'] !== null) {
                    if (isset($seenPrimaryIdentifiers[$identifier['value']])) {
                        $validator->errors()->add("lines.$index.primary_identifier", 'Duplicate primary identifier in submitted product lines.');
                    }
                    $seenPrimaryIdentifiers[$identifier['value']] = true;
                }

                if (! empty($line['imei'])) {
                    $imeisByIndex[$index] = $line['imei'];
                    if (isset($seenImeis[$line['imei']])) {
                        $validator->errors()->add("lines.$index.imei", 'Duplicate IMEI in submitted product lines.');
                    }
                    $seenImeis[$line['imei']] = true;
                }

                if (! empty($line['serial_number'])) {
                    $serialsByIndex[$index] = $line['serial_number'];
                    if (isset($seenSerials[$line['serial_number']])) {
                        $validator->errors()->add("lines.$index.serial_number", 'Duplicate Serial Number in submitted product lines.');
                    }
                    $seenSerials[$line['serial_number']] = true;
                }
            }

            $existingImeis = $this->existingLineValues('imei', array_values($imeisByIndex));
            $existingSerials = $this->existingLineValues('serial_number', array_values($serialsByIndex));
            $existingPrimaryIdentifiers = $this->existingLineValues('primary_identifier', array_values($primaryIdentifiersByIndex));

            foreach ($imeisByIndex as $index => $imei) {
                if (in_array($imei, $existingImeis, true)) {
                    $validator->errors()->add("lines.$index.imei", 'This IMEI has already been sold.');
                }
            }

            foreach ($serialsByIndex as $index => $serialNumber) {
                if (in_array($serialNumber, $existingSerials, true)) {
                    $validator->errors()->add("lines.$index.serial_number", 'This Serial Number has already been sold.');
                }
            }

            foreach ($primaryIdentifiersByIndex as $index => $primaryIdentifier) {
                if (in_array($primaryIdentifier, $existingPrimaryIdentifiers, true)) {
                    $validator->errors()->add("lines.$index.primary_identifier", 'This primary identifier has already been sold.');
                }
            }
        });

        $validated = $validator->validate();

        $validated['lines'] = array_map(fn (array $line) => $this->trimLineValues($line), $validated['lines']);

        return $validated;
    }

    private function generateInvoiceNo(): string
    {
        $date = Carbon::now()->format('Ymd');
        $prefix = "SO-$date-";
        $lastInvoice = SellOutReport::query()
            ->where('invoice_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice, -4)) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function resolvePrimaryIdentifier(array $line): array
    {
        if (! empty($line['imei'])) {
            return ['type' => 'imei', 'value' => $line['imei']];
        }

        if (! empty($line['serial_number'])) {
            return ['type' => 'serial', 'value' => $line['serial_number']];
        }

        return [
            'type' => ! empty($line['sku']) ? 'sku' : null,
            'value' => $line['sku'] ?? null,
        ];
    }

    private function trimLineValues(array $line): array
    {
        foreach ($line as $key => $value) {
            if (is_string($value)) {
                $line[$key] = trim($value) !== '' ? trim($value) : null;
            }
        }

        return $line;
    }

    private function existingLineValues(string $column, array $values): array
    {
        $values = array_values(array_unique(array_filter($values)));

        if ($values === []) {
            return [];
        }

        return SellOutReportLine::query()
            ->whereIn($column, $values)
            ->pluck($column)
            ->all();
    }

    private function resolveSellerName(array $validated): ?string
    {
        return auth()->user()?->name ?: ($validated['seller_name'] ?? null);
    }

    private function resolveBranchName(array $validated): ?string
    {
        $user = auth()->user();

        if ($user) {
            $user->loadMissing('branch');
        }

        return $user?->branch?->name ?: ($validated['branch_name'] ?? null);
    }

    private function resolveOriginalInvoiceNo(array $validated): ?string
    {
        if (! empty($validated['original_invoice_no'])) {
            return $validated['original_invoice_no'];
        }

        $note = (string) ($validated['note'] ?? '');

        if (preg_match('/Invoice\s*:\s*([A-Za-z0-9\-_.\/]+)/i', $note, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function formatReportSummary(SellOutReport $report): array
    {
        return [
            'id' => $report->id,
            'invoice_no' => $report->invoice_no,
            'original_invoice_no' => $report->original_invoice_no,
            'seller_name' => $report->seller_name,
            'branch_name' => $report->branch_name,
            'customer_name' => $report->customer_name,
            'customer_phone' => $report->customer_phone,
            'payment_method' => $report->payment_method,
            'total_amount' => $report->total_amount,
            'note' => $report->note,
            'extracted_text' => $report->extracted_text,
            'created_at' => $report->created_at,
            'lines_count' => $report->lines_count ?? $report->lines()->count(),
            'photos_count' => $report->photos_count ?? $report->photos()->count(),
        ];
    }

    private function formatReportDetail(SellOutReport $report): array
    {
        return array_merge($this->formatReportSummary($report), [
            'lines' => $report->lines,
            'photos' => $report->photos,
        ]);
    }

    private function sendTelegramNotification(SellOutReport $report): ?int
    {
        $chatId = (string) config('services.telegram.chat_id', '');

        if ($chatId === '') {
            Log::warning('Sell out report Telegram notification skipped: TELEGRAM_CHAT_ID is missing.', [
                'sell_out_report_id' => $report->id,
            ]);
            return null;
        }

        $caption = $this->buildTelegramMessage($report);
        $firstPhoto = $report->photos->first();

        if (! $firstPhoto) {
            $sent = $this->telegramService->sendMessage($chatId, $caption);
            return $sent ? 0 : null;
        }

        $messageId = $this->telegramService->sendPhoto(
            $chatId,
            Storage::disk('public')->path($firstPhoto->photo_path),
            $caption
        );

        foreach ($report->photos->slice(1) as $photo) {
            $this->telegramService->sendPhoto(
                $chatId,
                Storage::disk('public')->path($photo->photo_path)
            );
        }

        return $messageId;
    }

    private function buildTelegramMessage(SellOutReport $report): string
    {
        $productLines = $report->lines
            ->values()
            ->map(function (SellOutReportLine $line, int $index) {
                return ($index + 1) . ". " . ($line->product_name ?: '-') . "\n"
                    . "   SKU: " . ($line->sku ?: '-') . "\n"
                    . "   Identifier: " . ($line->primary_identifier ?: '-') . "\n"
                    . "   IMEI: " . ($line->imei ?: '-') . "\n"
                    . "   Serial: " . ($line->serial_number ?: '-') . "\n"
                    . "   Color: " . ($line->color ?: '-') . "\n"
                    . "   Storage: " . ($line->storage ?: '-') . "\n"
                    . "   Qty: " . $line->qty . "\n"
                    . "   Price: $" . ($line->unit_price ?? '0.00') . "\n"
                    . "   Subtotal: $" . ($line->subtotal ?? '0.00');
            })
            ->implode("\n\n");

        return "📱 SELL OUT REPORT\n"
            . "🧾 Invoice: {$report->invoice_no}\n\n"
            . "🧾 Original Invoice: " . ($report->original_invoice_no ?: '-') . "\n"
            . "👤 Seller: " . ($report->seller_name ?: '-') . "\n"
            . "🏬 Branch: " . ($report->branch_name ?: '-') . "\n"
            . "👥 Customer: " . ($report->customer_name ?: '-') . "\n\n"
            . "📞 Customer Phone: " . ($report->customer_phone ?: '-') . "\n\n"
            . "📦 Products:\n"
            . $productLines . "\n\n"
            . "💰 Total: $" . ($report->total_amount ?? '0.00') . "\n"
            . "💳 Payment: " . ($report->payment_method ?: '-') . "\n\n"
            . "📝 Note:\n"
            . ($report->note ?: '-') . "\n\n"
            . "📄 OCR Text:\n"
            . ($report->extracted_text ?: '-');
    }
}
