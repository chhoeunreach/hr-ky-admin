<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompressApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!config('social_feed.gzip', true)
            || !function_exists('gzencode')
            || $request->isMethod('HEAD')
            || $response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse
            || $response->headers->has('Content-Encoding')
            || !$this->acceptsGzip($request->headers->get('Accept-Encoding', ''))) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'json') === false) return $response;

        $content = $response->getContent();
        $minimum = max(512, (int) config('social_feed.gzip_min_bytes', 2048));
        if (!is_string($content) || strlen($content) < $minimum) return $response;

        $compressed = gzencode($content, 6);
        if ($compressed === false) return $response;

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', (string) strlen($compressed));
        $response->headers->set('Vary', trim($response->headers->get('Vary', '').', Accept-Encoding', ', '));

        return $response;
    }

    private function acceptsGzip(string $header): bool
    {
        if (stripos($header, 'gzip') === false) return false;
        return !preg_match('/gzip\s*;\s*q=0(?:\.0+)?(?:,|$)/i', $header);
    }
}
