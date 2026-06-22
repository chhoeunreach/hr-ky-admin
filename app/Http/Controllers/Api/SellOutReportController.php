<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellOutReport;
use App\Requests\SellOutReport\StoreSellOutReportRequest;
use App\Resources\SellOutReport\SellOutReportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SellOutReportController extends Controller
{
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

            foreach ($validated['lines'] as $line) {
                $qty = (int) $line['qty'];
                $unitPrice = (float) $line['unit_price'];

                $report->lines()->create([
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
            }

            foreach ($request->file('photos', []) as $photo) {
                $photoPath = $photo->store('sell-out-reports', 'public');

                $report->photos()->create([
                    'photo_path' => $photoPath,
                    'photo_url' => Storage::disk('public')->url($photoPath),
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            return $report;
        });

        $report->load(['lines', 'photos'])->loadCount(['lines', 'photos']);

        return response()->json([
            'status' => true,
            'message' => 'Sell Out Report submitted successfully!',
            'data' => (new SellOutReportResource($report))->toArray(request()),
        ], 201);
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

}
