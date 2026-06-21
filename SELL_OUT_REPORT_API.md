# Sell Out Report API

Authenticated routes:

- `GET /api/sell-out-reports`
- `POST /api/sell-out-reports`
- `GET /api/sell-out-reports/{id}`
- `DELETE /api/sell-out-reports/{id}`

## Environment

```dotenv
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```

`config/services.php` reads `TELEGRAM_CHAT_ID` and falls back to `TELEGRAM_DEFAULT_CHAT_ID` if needed.

## Storage

```bash
php artisan storage:link
```

Uploaded files are stored on the public disk under:

```text
storage/app/public/sell_out_reports
```

## Multipart Fields

Header fields:

```text
seller_name
branch_name
customer_name
payment_method
note
extracted_text
```

Product line fields:

```text
lines[0][product_name]
lines[0][sku]
lines[0][imei]
lines[0][imei2]
lines[0][serial_number]
lines[0][model_number]
lines[0][color]
lines[0][storage]
lines[0][qty]
lines[0][unit_price]
```

Photos:

```text
photos[]
```

## Identifier Rule

For every product line:

1. If `imei` exists, `primary_identifier = imei` and `identifier_type = imei`.
2. Else if `serial_number` exists, `primary_identifier = serial_number` and `identifier_type = serial`.
3. Else `primary_identifier = sku` and `identifier_type = sku`.

If `imei` and `serial_number` are both empty, `sku` is required.

## curl Test

```bash
curl -X POST "https://your-domain.test/api/sell-out-reports" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -F "seller_name=Reach" \
  -F "branch_name=Main Branch" \
  -F "customer_name=Sokha" \
  -F "payment_method=Cash" \
  -F "note=Delivered in store" \
  -F "extracted_text=OCR text from Flutter invoice" \
  -F "lines[0][product_name]=iPhone 15 Pro" \
  -F "lines[0][sku]=IPH15PRO-256-BLK" \
  -F "lines[0][imei]=356789123456789" \
  -F "lines[0][imei2]=356789123456780" \
  -F "lines[0][serial_number]=" \
  -F "lines[0][model_number]=A3101" \
  -F "lines[0][color]=Black Titanium" \
  -F "lines[0][storage]=256GB" \
  -F "lines[0][qty]=1" \
  -F "lines[0][unit_price]=999" \
  -F "lines[1][product_name]=AirPods Pro" \
  -F "lines[1][sku]=APP2-USB-C" \
  -F "lines[1][serial_number]=SN1234567890" \
  -F "lines[1][qty]=1" \
  -F "lines[1][unit_price]=249" \
  -F "photos[]=@/absolute/path/invoice-front.jpg" \
  -F "photos[]=@/absolute/path/invoice-back.png"
```

## Flutter Multipart Example

```dart
import 'package:http/http.dart' as http;

Future<http.StreamedResponse> submitSellOutReport({
  required Uri endpoint,
  required String token,
  required List<String> photoPaths,
}) async {
  final request = http.MultipartRequest('POST', endpoint);

  request.headers.addAll({
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  });

  request.fields.addAll({
    'seller_name': 'Reach',
    'branch_name': 'Main Branch',
    'customer_name': 'Sokha',
    'payment_method': 'Cash',
    'note': 'Delivered in store',
    'extracted_text': 'OCR text from Flutter invoice',
    'lines[0][product_name]': 'iPhone 15 Pro',
    'lines[0][sku]': 'IPH15PRO-256-BLK',
    'lines[0][imei]': '356789123456789',
    'lines[0][imei2]': '356789123456780',
    'lines[0][serial_number]': '',
    'lines[0][model_number]': 'A3101',
    'lines[0][color]': 'Black Titanium',
    'lines[0][storage]': '256GB',
    'lines[0][qty]': '1',
    'lines[0][unit_price]': '999',
    'lines[1][product_name]': 'AirPods Pro',
    'lines[1][sku]': 'APP2-USB-C',
    'lines[1][serial_number]': 'SN1234567890',
    'lines[1][qty]': '1',
    'lines[1][unit_price]': '249',
  });

  for (final path in photoPaths) {
    request.files.add(await http.MultipartFile.fromPath('photos[]', path));
  }

  return request.send();
}
```

## Validation Response Shape

```json
{
  "status": false,
  "message": "Validation failed.",
  "status_code": 422,
  "data": {
    "lines.0.imei": [
      "This IMEI has already been sold."
    ]
  }
}
```
