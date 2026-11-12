<?php
namespace App\Services\Community;

use Illuminate\Support\Facades\Storage;

class UploadedMediaUrl
{
    public function resolve(?string $value): ?string
    {
        if (!$value) return $value;
        $parts = parse_url($value);
        if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || isset($parts['user'], $parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) return $value;
        if (isset($parts['user']) || isset($parts['pass'])) return $value;
        $path = $parts['path'] ?? '';
        if (!preg_match('~^/storage/((?:community|stories)/(?:thumbnails/)?[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+)$~', $path, $match)) return $value;
        $host = strtolower($parts['host'] ?? '');
        $known = array_filter(array_map(fn ($url) => strtolower((string) parse_url((string) $url, PHP_URL_HOST)), [
            config('app.url'), config('app.mobile_app_url'), config('app.fallback_url'),
            config('app.mobile_app_fallback_url'), request()->getSchemeAndHttpHost(),
        ]));
        $private = $host === 'localhost' || (filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
        if ((!$private && !in_array($host, $known, true)) || !Storage::disk('public')->exists($match[1])) return $value;
        return request()->getSchemeAndHttpHost().request()->getBasePath().'/storage/'.$match[1];
    }

    public function payload(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) $data[$key] = $this->payload($value);
            elseif (in_array($key, ['media_url', 'thumbnail_url'], true) && is_string($value)) $data[$key] = $this->resolve($value);
        }
        return $data;
    }
}
