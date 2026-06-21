<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellOutReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->map(fn (SellOutReport $report) => $this->formatReport($report, true));

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
            'data' => $this->formatReport($report),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seller_name' => ['required', 'string', 'max:255'],
            'original_invoice_no' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'extracted_text' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_name' => ['required', 'string', 'max:255'],
            'lines.*.sku' => ['nullable', 'string', 'max:255'],
            'lines.*.imei' => ['nullable', 'string', 'max:255'],
            'lines.*.imei2' => ['nullable', 'string', 'max:255'],
            'lines.*.serial_number' => ['nullable', 'string', 'max:255'],
            'lines.*.model_number' => ['nullable', 'string', 'max:255'],
            'lines.*.color' => ['nullable', 'string', 'max:255'],
            'lines.*.storage' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ]);

        $report = DB::transaction(function () use ($request, $validated) {
            $totalAmount = collect($validated['lines'])->sum(function (array $line): float {
                return round((int) $line['qty'] * (float) $line['unit_price'], 2);
            });

            $report = SellOutReport::create([
                'user_id' => auth()->id(),
                'invoice_no' => $this->generateInvoiceNo(),
                'original_invoice_no' => $validated['original_invoice_no'] ?? null,
                'seller_name' => $validated['seller_name'],
                'branch_name' => $validated['branch_name'],
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'service_type' => $validated['service_type'],
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'total_amount' => round($totalAmount, 2),
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
                $report->photos()->create([
                    'photo_path' => $photo->store('sell-out-reports', 'public'),
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            return $report;
        });

        $report->load(['lines', 'photos'])->loadCount(['lines', 'photos']);

        return response()->json([
            'status' => true,
            'data' => $this->formatReport($report),
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
        $lastId = (int) SellOutReport::query()
            ->lockForUpdate()
            ->max('id');

        return 'SOR-' . str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);
    }

    private function formatReport(SellOutReport $report, bool $summary = false): array
    {
        $data = [
            'id' => $report->id,
            'invoice_no' => $report->invoice_no,
            'original_invoice_no' => $report->original_invoice_no ?? '',
            'seller_name' => $report->seller_name,
            'branch_name' => $report->branch_name,
            'customer_name' => $report->customer_name,
            'customer_phone' => $report->customer_phone,
            'service_type' => $report->service_type,
            'payment_method' => $report->payment_method,
            'note' => $report->note ?? '',
            'extracted_text' => $report->extracted_text ?? '',
            'total_amount' => (float) $report->total_amount,
            'created_at' => optional($report->created_at)->format('Y-m-d H:i:s'),
        ];

        if ($summary) {
            $data['lines_count'] = $report->lines_count ?? $report->lines->count();
            $data['photos_count'] = $report->photos_count ?? $report->photos->count();
        }

        $data['lines'] = $summary ? [] : $report->lines->map(fn ($line) => [
            'id' => $line->id,
            'product_name' => $line->product_name,
            'sku' => $line->sku,
            'imei' => $line->imei ?? '',
            'imei2' => $line->imei2 ?? '',
            'serial_number' => $line->serial_number ?? '',
            'model_number' => $line->model_number ?? '',
            'color' => $line->color,
            'storage' => $line->storage,
            'qty' => (int) $line->qty,
            'unit_price' => (float) $line->unit_price,
        ])->values();

        $data['photos'] = $summary ? [] : $report->photos->map(fn ($photo) => [
            'id' => $photo->id,
            'photo_path' => $photo->photo_path,
            'photo_url' => Storage::disk('public')->url($photo->photo_path),
        ])->values();

        return $data;
    }
}
