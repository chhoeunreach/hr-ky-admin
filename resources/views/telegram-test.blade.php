<!doctype html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telegram Receipt</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 24px; background: #f3f4f6; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h2 { margin: 0 0 4px; font-size: 22px; }
        .sub { color: #6b7280; font-size: 13px; margin: 0 0 20px; }
        label { display: block; margin-top: 14px; font-weight: 600; font-size: 14px; color: #374151; }
        input, textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        input:focus, textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .row { display: flex; gap: 12px; }
        .row > * { flex: 1; }
        button { margin-top: 20px; padding: 12px 20px; border-radius: 10px; border: 0; background: #111827; color: #fff; cursor: pointer; font-size: 15px; width: 100%; }
        button:hover { background: #1f2937; }
        .ok { padding: 10px 14px; border-radius: 10px; background: #ecfdf5; color: #065f46; margin-bottom: 16px; font-size: 14px; }
        .err { padding: 10px 14px; border-radius: 10px; background: #fef2f2; color: #991b1b; margin-bottom: 16px; font-size: 14px; }
        .hint { margin-top: 16px; color: #6b7280; font-size: 13px; text-align: center; }
        .preview { margin-top: 12px; display: none; }
        .preview img { max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🛒 ផ្ញើវិក្កយបត្រ → Telegram</h2>
        <p class="sub">បំពេញព័ត៌មានវិក្កយបត្រ រួចចុចផ្ញើ</p>

        @if (session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="err">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('telegram.notify') }}" enctype="multipart/form-data">
            @csrf

            <label>វិក្កយបត្រ (Invoice No.)</label>
            <input name="invoice_no" value="{{ old('invoice_no', 'SO-20260627-0178') }}" placeholder="e.g. SO-20260627-XXXX">

            <div class="row">
                <div>
                    <label>ទំនិញ (Product)</label>
                    <input name="product_name" value="{{ old('product_name', 'iPhone 12') }}" placeholder="Product name">
                </div>
                <div>
                    <label>ចំនួន (Qty)</label>
                    <input name="quantity" type="number" value="{{ old('quantity', 1) }}" min="1">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>តម្លៃ (Price $)</label>
                    <input name="price" type="number" step="0.01" value="{{ old('price', 220.00) }}" placeholder="0.00">
                </div>
                <div>
                    <label>SN (Serial No.)</label>
                    <input name="serial_number" value="{{ old('serial_number', 'FFXH439G0DXQ') }}" placeholder="Serial number">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>អ្នកលក់ (Seller)</label>
                    <input name="seller_name" value="{{ old('seller_name', '88 ណាលីន') }}" placeholder="Seller name">
                </div>
                <div>
                    <label>សាខា (Branch)</label>
                    <input name="branch_name" value="{{ old('branch_name', 'កម្ពុជាក្រោម') }}" placeholder="Branch name">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>ID អ្នកលក់ (User ID)</label>
                    <input name="user_id" id="user_id" value="{{ old('user_id', '88') }}" placeholder="Seller ID" oninput="updateNote()">
                </div>
                <div>
                    <label>ទូរស័ព្ទ (Phone)</label>
                    <input name="contact" id="contact" value="{{ old('contact', '090 821 168') }}" placeholder="Phone number" oninput="updateNote()">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>សម្គាល់ (Note)</label>
                    <input name="note" id="note" value="{{ old('note', '88-1168') }}" readonly style="background:#f9fafb; color:#6b7280;">
                </div>
                <div></div>
            </div>

            <label>រូបភាពវិក្កយបត្រ និងទំនិញ (Photos)</label>
            <input name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" onchange="previewFiles(this)">

            <div class="preview" id="photoPreview"></div>

            <button type="submit">ផ្ញើទៅ Telegram</button>
        </form>

        <p class="hint">ប្រើ <code>actionKey=sell_out_sale</code> ដើម្បីផ្ញើទៅកាន់ Telegram</p>
    </div>

    <script>
        function updateNote() {
            const userId = document.getElementById('user_id').value.replace(/\D/g, '');
            const phone = document.getElementById('contact').value.replace(/\D/g, '');
            const last4 = phone.slice(-4);
            document.getElementById('note').value = userId && last4 ? userId + '-' + last4 : userId || last4;
        }

        function previewFiles(input) {
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = '';
            if (input.files && input.files.length > 0) {
                preview.style.display = 'block';
                for (const file of input.files) {
                    const img = document.createElement('img');
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '160px';
                    img.style.borderRadius = '8px';
                    img.style.border = '1px solid #e5e7eb';
                    img.style.marginTop = '8px';
                    const reader = new FileReader();
                    reader.onload = function (e) { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    preview.appendChild(img);
                }
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
