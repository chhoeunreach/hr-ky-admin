<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TelegramGroup;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramNotificationController extends Controller
{
    public function send(Request $request, TelegramService $telegramService): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'actionKey' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'branchName' => ['nullable', 'string'],
            'departmentName' => ['nullable', 'string'],
            'messageText' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'invoice_no' => ['nullable', 'string', 'max:255'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'seller_name' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $messageText = $validated['messageText'] ?? '';

        if ($messageText === '' && !empty($validated['invoice_no'] ?? $validated['product_name'] ?? '')) {
            $messageText = $this->buildReceiptMessage($validated);
        }

        if ($messageText === '') {
            return back()->withErrors(['messageText' => 'Message text or receipt fields required.']);
        }

        $branchName = $validated['branchName'] ?? $validated['branch_name'] ?? '';
        $actionKey = (string) ($validated['actionKey'] ?? TelegramGroup::EVENT_SELL_OUT_SALE);

        $fullPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('receipt-telegram', 'public');
            $fullPath = Storage::disk('public')->path($photoPath);
        }

        if ($fullPath !== null) {
            $chatIds = $telegramService->chatIdsForAction($actionKey, $branchName, '');
            if (empty($chatIds)) {
                return back()->withErrors(['telegram' => 'No active Telegram group found.']);
            }
            $allOk = true;
            foreach ($chatIds as $chatId) {
                $allOk = $telegramService->sendMessage($chatId, $messageText, 'HTML') && $allOk;
                $allOk = $telegramService->sendPhoto($chatId, $fullPath) !== null && $allOk;
            }
            $ok = $allOk;
        } else {
            $ok = $telegramService->sendToAction(
                $actionKey,
                $messageText,
                'HTML',
                $branchName,
                (string) ($validated['departmentName'] ?? ''),
                array_key_exists('latitude', $validated) ? (float) $validated['latitude'] : null,
                array_key_exists('longitude', $validated) ? (float) $validated['longitude'] : null,
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $ok,
                'message' => $ok ? 'Telegram notification sent.' : 'Telegram notification failed. Check server logs.',
            ], $ok ? 200 : 500);
        }

        if (! $ok) {
            return back()->withErrors(['telegram' => 'Telegram notification failed. Check server logs.']);
        }

        return back()->with('status', 'Telegram notification sent.');
    }

    private function buildReceiptMessage(array $data): string
    {
        $msg = "🛒 <b>វិក្កយបត្រ (Receipt)</b>\n";
        if (!empty($data['invoice_no'])) {
            $msg .= "Invoice: {$data['invoice_no']}\n";
        }
        if (!empty($data['product_name'])) {
            $qty = $data['quantity'] ?? 1;
            $price = isset($data['price']) ? '$' . number_format((float) $data['price'], 2) : '';
            $msg .= "ទំនិញ: {$data['product_name']} ចំនួន{$qty} តម្លៃ: {$price}\n";
        }
        if (!empty($data['serial_number'])) {
            $msg .= "SN: {$data['serial_number']}\n";
        }
        if (!empty($data['seller_name'])) {
            $msg .= "អ្នកលក់: {$data['seller_name']}";
            if (!empty($data['branch_name'] ?? $data['branchName'] ?? '')) {
                $msg .= " (សាខា៖ " . ($data['branch_name'] ?? $data['branchName']) . ")";
            }
            $msg .= "\n";
        }
        if (!empty($data['reference'])) {
            $msg .= "សម្គាល់: {$data['reference']}\n";
        }
        if (!empty($data['contact'])) {
            $msg .= "ទំនាក់ទំនង: {$data['contact']}\n";
        }
        return $msg;
    }
}
