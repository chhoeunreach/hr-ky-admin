<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileQuoteController extends Controller
{
    private const SETTING_SLUG = 'mobile-quotes';

    public function index()
    {
        $this->authorize('quote.manage');

        return view('admin.mobile_tool.quotes', [
            'quotes' => $this->quotes(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('quote.manage');
        $data = $this->validateQuote($request);

        $quotes = $this->quotes();
        $quotes[] = [
            'id' => 'quote_'.bin2hex(random_bytes(8)),
            'text' => $data['text'],
            'author' => $data['author'] ?? null,
            'enabled' => (bool) ($data['enabled'] ?? true),
            'style' => $this->sanitizeStyle($request->input('style')),
        ];
        $this->save($quotes);

        return redirect()->route('admin.mobile-tools.quotes')->with('success', 'Quote added.');
    }

    public function update(Request $request, string $quoteId)
    {
        $this->authorize('quote.manage');
        $data = $this->validateQuote($request);

        $quotes = $this->quotes();
        $index = $this->indexOf($quotes, $quoteId);
        if ($index === null) {
            return redirect()->route('admin.mobile-tools.quotes')->with('danger', 'Quote not found.');
        }

        $quotes[$index]['text'] = $data['text'];
        $quotes[$index]['author'] = $data['author'] ?? null;
        $quotes[$index]['enabled'] = (bool) ($data['enabled'] ?? $quotes[$index]['enabled']);
        if ($request->has('style')) {
            $quotes[$index]['style'] = $this->sanitizeStyle($request->input('style'));
        }
        $this->save($quotes);

        return redirect()->route('admin.mobile-tools.quotes')->with('success', 'Quote updated.');
    }

    public function toggle(string $quoteId)
    {
        $this->authorize('quote.manage');

        $quotes = $this->quotes();
        $index = $this->indexOf($quotes, $quoteId);
        if ($index === null) {
            return redirect()->route('admin.mobile-tools.quotes')->with('danger', 'Quote not found.');
        }

        $quotes[$index]['enabled'] = !$quotes[$index]['enabled'];
        $this->save($quotes);

        return back()->with('success', $quotes[$index]['enabled'] ? 'Quote enabled.' : 'Quote disabled.');
    }

    public function destroy(string $quoteId)
    {
        $this->authorize('quote.manage');

        $quotes = $this->quotes();
        $index = $this->indexOf($quotes, $quoteId);
        if ($index === null) {
            return redirect()->route('admin.mobile-tools.quotes')->with('danger', 'Quote not found.');
        }

        array_splice($quotes, $index, 1);
        $this->save($quotes);

        return redirect()->route('admin.mobile-tools.quotes')->with('success', 'Quote deleted.');
    }

    private function validateQuote(Request $request): array
    {
        return $request->validate([
            'text' => [
                'required', 'string', 'max:500',
                fn ($attribute, $value, $fail) => trim($value) === '' ? $fail('Enter a quote.') : null,
            ],
            'author' => ['nullable', 'string', 'max:120'],
            'enabled' => ['sometimes', 'boolean'],
            'style' => ['nullable'],
        ]);
    }

    private function sanitizeStyle(mixed $raw): ?array
    {
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (!is_array($raw)) {
            return null;
        }

        $fontFamily = isset($raw['fontFamily']) ? substr(trim((string) $raw['fontFamily']), 0, 80) : 'Kantumruy Pro';
        $fontSize = isset($raw['fontSize']) && is_numeric($raw['fontSize']) ? max(8, min(72, (float) $raw['fontSize'])) : 14;
        $fontWeight = isset($raw['fontWeight']) ? substr(trim((string) $raw['fontWeight']), 0, 20) : '800';
        $fontStyle = isset($raw['fontStyle']) && in_array($raw['fontStyle'], ['normal', 'italic'], true) ? $raw['fontStyle'] : 'normal';
        $textAlign = isset($raw['textAlign']) && in_array($raw['textAlign'], ['left', 'center', 'right'], true) ? $raw['textAlign'] : 'right';
        $color = isset($raw['color']) ? substr(trim((string) $raw['color']), 0, 32) : '#FFF7ED';
        $opacity = isset($raw['opacity']) && is_numeric($raw['opacity']) ? max(0.05, min(1.0, (float) $raw['opacity'])) : 1.0;
        $rotation = isset($raw['rotation']) && is_numeric($raw['rotation']) ? max(-180, min(180, (float) $raw['rotation'])) : 0;
        $posX = isset($raw['posX']) && is_numeric($raw['posX']) ? max(0, min(100, (float) $raw['posX'])) : 65.0;
        $posY = isset($raw['posY']) && is_numeric($raw['posY']) ? max(0, min(100, (float) $raw['posY'])) : 25.0;
        $strokeWidth = isset($raw['strokeWidth']) && is_numeric($raw['strokeWidth']) ? max(0, min(10, (float) $raw['strokeWidth'])) : 0;
        $strokeColor = isset($raw['strokeColor']) ? substr(trim((string) $raw['strokeColor']), 0, 32) : '#000000';
        $gradientEnabled = !empty($raw['gradientEnabled']);
        $gradientStart = isset($raw['gradientStart']) ? substr(trim((string) $raw['gradientStart']), 0, 32) : '#FFE082';
        $gradientEnd = isset($raw['gradientEnd']) ? substr(trim((string) $raw['gradientEnd']), 0, 32) : '#FF8F00';
        $gradientAngle = isset($raw['gradientAngle']) && is_numeric($raw['gradientAngle']) ? (int) $raw['gradientAngle'] : 90;
        $shadowBlur = isset($raw['shadowBlur']) && is_numeric($raw['shadowBlur']) ? max(0, min(40, (float) $raw['shadowBlur'])) : 5;
        $shadowColor = isset($raw['shadowColor']) ? substr(trim((string) $raw['shadowColor']), 0, 48) : 'rgba(0,0,0,0.7)';
        $shadowX = isset($raw['shadowX']) && is_numeric($raw['shadowX']) ? max(-30, min(30, (float) $raw['shadowX'])) : 0;
        $shadowY = isset($raw['shadowY']) && is_numeric($raw['shadowY']) ? max(-30, min(30, (float) $raw['shadowY'])) : 2;
        $bannerImage = isset($raw['bannerImage']) ? substr(trim((string) $raw['bannerImage']), 0, 2048) : '/assets/images/angkor_wat_banner.jpg';
        $bannerOverlay = isset($raw['bannerOverlay']) && is_numeric($raw['bannerOverlay']) ? max(0.0, min(0.9, (float) $raw['bannerOverlay'])) : 0.25;

        return [
            'fontFamily' => $fontFamily ?: 'Kantumruy Pro',
            'fontSize' => $fontSize,
            'fontWeight' => $fontWeight ?: '800',
            'fontStyle' => $fontStyle,
            'textAlign' => $textAlign,
            'color' => $color ?: '#FFF7ED',
            'opacity' => $opacity,
            'rotation' => $rotation,
            'posX' => $posX,
            'posY' => $posY,
            'strokeWidth' => $strokeWidth,
            'strokeColor' => $strokeColor,
            'gradientEnabled' => $gradientEnabled,
            'gradientStart' => $gradientStart,
            'gradientEnd' => $gradientEnd,
            'gradientAngle' => $gradientAngle,
            'shadowBlur' => $shadowBlur,
            'shadowColor' => $shadowColor,
            'shadowX' => $shadowX,
            'shadowY' => $shadowY,
            'bannerImage' => $bannerImage,
            'bannerOverlay' => $bannerOverlay,
        ];
    }

    private function quotes(): array
    {
        $value = AppSetting::query()->where('slug', self::SETTING_SLUG)->value('value');
        $config = is_string($value) ? json_decode($value, true) : null;

        if (!is_array($config) || !is_array($config['quotes'] ?? null)) {
            return [];
        }

        return array_values(array_filter($config['quotes'], fn ($quote) => is_array($quote) && isset($quote['id'], $quote['text'])));
    }

    private function save(array $quotes): void
    {
        DB::transaction(function () use ($quotes): void {
            AppSetting::query()->updateOrCreate(
                ['slug' => self::SETTING_SLUG],
                ['name' => 'Mobile Quotes', 'value' => json_encode(['quotes' => $quotes], JSON_THROW_ON_ERROR), 'status' => 1]
            );
        });
    }

    private function indexOf(array $quotes, string $quoteId): ?int
    {
        foreach ($quotes as $index => $quote) {
            if (hash_equals((string) $quote['id'], $quoteId)) {
                return $index;
            }
        }

        return null;
    }
}
