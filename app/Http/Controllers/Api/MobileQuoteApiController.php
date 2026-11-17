<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class MobileQuoteApiController extends Controller
{
    private const SETTING_SLUG = 'mobile-quotes';

    public function index(): JsonResponse
    {
        $value = AppSetting::query()->where('slug', self::SETTING_SLUG)->value('value');
        $config = is_string($value) ? json_decode($value, true) : null;
        $quotes = is_array($config) && is_array($config['quotes'] ?? null) ? $config['quotes'] : [];

        $active = array_values(array_filter($quotes, function ($quote): bool {
            return is_array($quote)
                && !empty($quote['id'])
                && !empty($quote['text'])
                && !empty($quote['enabled']);
        }));

        foreach ($active as $index => $quote) {
            $active[$index] = [
                'id' => (string) $quote['id'],
                'text' => (string) $quote['text'],
                'author' => isset($quote['author']) && $quote['author'] !== '' ? (string) $quote['author'] : null,
                'style' => isset($quote['style']) && is_array($quote['style']) ? $quote['style'] : null,
            ];
        }

        return response()->json(['data' => ['quotes' => $active]]);
    }
}
