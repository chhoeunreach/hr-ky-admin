<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\TelegramService::class);

// Simulate controller buildReceiptMessage
$data = [
    'invoice_no' => 'SO-20260627-0178',
    'product_name' => 'iPhone 12',
    'quantity' => 1,
    'price' => 220.00,
    'serial_number' => 'FFXH439G0DXQ',
    'user_id' => '88',
    'seller_name' => 'ណាលីន',
    'branch_name' => 'កម្ពុជាក្រោម',
    'contact' => '090 821 168',
];

$msg = "🛒 <b>វិក្កយបត្រ</b>\n";
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
if (!empty($data['user_id'])) {
    $msg .= "ID: {$data['user_id']}";
    if (!empty($data['seller_name'])) {
        $msg .= " {$data['seller_name']}";
    }
    if (!empty($data['branch_name'])) {
        $msg .= " (សាខា៖ {$data['branch_name']})";
    }
    $msg .= "\n";
} elseif (!empty($data['seller_name'])) {
    $msg .= "អ្នកលក់: {$data['seller_name']}";
    if (!empty($data['branch_name'])) {
        $msg .= " (សាខា៖ {$data['branch_name']})";
    }
    $msg .= "\n";
}
if (!empty($data['contact'])) {
    $msg .= "ទំនាក់ទំនង: {$data['contact']}\n";
}
$userDigits = ltrim(preg_replace('/\D/', '', $data['user_id'] ?? ''), '0');
$phoneDigits = preg_replace('/\D/', '', $data['contact'] ?? '');
$note = trim($userDigits . '-' . substr($phoneDigits, -4), '-');
if ($note !== '') {
    $msg .= "សម្គាល់: {$note}\n";
}

echo "Message:\n$msg\n\n";

echo "Sending...\n";
$result = $service->sendToAction('sell_out_sale', $msg, 'HTML', 'កម្ពុជាក្រោម', '');
echo $result ? "✅ Sent!\n" : "❌ Failed.\n";
