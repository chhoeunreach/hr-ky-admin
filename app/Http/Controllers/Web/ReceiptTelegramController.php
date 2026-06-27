<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TelegramGroup;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptTelegramController extends Controller
{
    public function __construct(private TelegramService $telegramService)
    {
    }

    public function form()
    {
        return view('receipt-telegram');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_no' => ['nullable', 'string', 'max:255'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'seller_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        try {
            $branchName = $validated['branch_name'] ?? '';
            $sellerName = $validated['seller_name'] ?? '';

            $message = "🛒 <b>វិក្កយបត្រ (Receipt)</b>\n";
            if (!empty($validated['invoice_no'])) {
                $message .= "Invoice: {$validated['invoice_no']}\n";
            }
            if (!empty($validated['product_name'])) {
                $qty = $validated['quantity'] ?? 1;
                $price = isset($validated['price']) ? '$' . number_format((float) $validated['price'], 2) : '';
                $message .= "ទំនិញ: {$validated['product_name']} ចំនួន{$qty} តម្លៃ: {$price}\n";
            }
            if (!empty($validated['serial_number'])) {
                $message .= "SN: {$validated['serial_number']}\n";
            }
            if ($sellerName !== '') {
                $message .= "អ្នកលក់: {$sellerName}";
                if ($branchName !== '') {
                    $message .= " (សាខា៖ {$branchName})";
                }
                $message .= "\n";
            }
            if (!empty($validated['reference'])) {
                $message .= "សម្គាល់: {$validated['reference']}\n";
            }
            if (!empty($validated['contact'])) {
                $message .= "ទំនាក់ទំនង: {$validated['contact']}\n";
            }

            $chatIds = $this->telegramService->chatIdsForAction(
                TelegramGroup::EVENT_SELL_OUT_SALE,
                $branchName,
                ''
            );

            if (empty($chatIds)) {
                return back()->withErrors(['telegram' => 'No active Telegram group found for routing.']);
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('receipt-telegram', 'public');
            }

            foreach ($chatIds as $chatId) {
                if ($photoPath !== null) {
                    $fullPath = Storage::disk('public')->path($photoPath);
                    $this->telegramService->sendPhoto($chatId, $fullPath, $message);
                } else {
                    $this->telegramService->sendMessage($chatId, $message, 'HTML');
                }
            }

            return back()->with('status', 'Receipt sent to Telegram successfully.');
        } catch (\Throwable $e) {
            Log::error('Receipt Telegram send failed.', ['error' => $e->getMessage()]);
            return back()->withErrors(['telegram' => 'Failed to send receipt. Check logs.']);
        }
    }
}
