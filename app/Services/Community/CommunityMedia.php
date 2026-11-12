<?php

namespace App\Services\Community;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommunityMedia
{
    public const TYPES = ['text', 'image', 'video', 'document', 'youtube', 'facebook', 'link'];

    public function content(Request $request): array
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:10000'],
            'location_name' => ['nullable', 'string', 'max:180'],
            'media_type' => ['required', Rule::in(self::TYPES)],
            'media_url' => ['nullable', 'string', 'max:4096'],
            'media' => ['nullable', 'file', 'max:102400', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'thumbnail' => ['nullable', 'file', 'image', 'max:8192'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'aspect_ratio' => ['nullable', 'numeric', 'min:0.2', 'max:4'],
        ]);
        $type = $data['media_type'];
        $url = trim($data['media_url'] ?? '');
        $body = trim($data['body'] ?? '');
        if ($type === 'text' && $body === '' && !$request->hasFile('media_files') && !$request->filled('poll_question')) {
            throw ValidationException::withMessages(['body' => 'Write a message or choose media.']);
        }
        $thumbnailUrl = null;
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $isImage = str_starts_with($file->getMimeType(), 'image/');
            $isVideo = str_starts_with($file->getMimeType(), 'video/');
            $isDocument = !$isImage && !$isVideo;
            if (!(($isImage && $type === 'image') || ($isVideo && $type === 'video') || ($isDocument && $type === 'document'))) {
                throw ValidationException::withMessages(['media' => 'The uploaded file must match the selected media type.']);
            }
            if ($isImage && $file->getSize() > 10 * 1024 * 1024) {
                throw ValidationException::withMessages(['media' => 'Images must be 10 MB or smaller.']);
            }
            if ($isDocument && $file->getSize() > 25 * 1024 * 1024) {
                throw ValidationException::withMessages(['media' => 'Documents must be 25 MB or smaller.']);
            }
        } elseif ($type !== 'text' && !$request->hasFile('media_files')) {
            if ($url === '') {
                throw ValidationException::withMessages(['media_url' => 'Enter a media URL or upload a file for the selected content type.']);
            }
            $url = $this->normalize($type, $url);
        }
        // Store only after every validation check succeeds.
        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('community', 'public');
            $url = url(Storage::disk('public')->url($path));
            if ($type === 'video') app(AdaptiveVideo::class)->enqueue($path);
            if ($type === 'video' && $request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('community/thumbnails', 'public');
                $thumbnailUrl = url(Storage::disk('public')->url($thumbnailPath));
            }
        }
        return [
            'body' => $body,
            'location_name' => trim((string) ($data['location_name'] ?? '')) ?: null,
            'media_type' => $type,
            'media_url' => $type === 'text' ? null : $url,
            'thumbnail_url' => $type === 'video' ? $thumbnailUrl : null,
            'duration_seconds' => $type === 'video' ? ($data['duration_seconds'] ?? null) : null,
            'aspect_ratio' => $type === 'video' ? ($data['aspect_ratio'] ?? null) : null,
        ];
    }

    /** Store optional additional photo/video/document attachments for a post. */
    public function storeAdditional(Request $request, \App\Models\SocialPost $post): void
    {
        $files = $request->file('media_files', []);
        if (!$files) return;
        if (!is_array($files)) $files = [$files];
        Validator::make(['media_files' => $files], [
            'media_files' => ['array', 'max:10'],
            'media_files.*' => ['file', 'max:102400', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ])->validate();
        foreach ($files as $position => $file) {
            $mime = (string) $file->getMimeType();
            $type = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');
            $path = $file->store('community', 'public');
            if ($type === 'video') app(AdaptiveVideo::class)->enqueue($path);
            $post->media()->create([
                'media_type' => $type,
                'media_url' => url(Storage::disk('public')->url($path)),
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'file_size' => $file->getSize(),
                'position' => $position,
            ]);
        }
    }

    public static function removeUnused(?string $value, ?int $ignorePostId = null): void
    {
        if (!$value) return;
        $path = parse_url($value, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/storage/(community/[a-zA-Z0-9._/-]+)$~', $path, $match)) return;
        $relative = $match[1];
        if (str_contains($relative, '..') || $value !== url(Storage::disk('public')->url($relative))) return;
        $suffix = '%/storage/'.$relative;
        if (\App\Models\FlashView::where('media_url', 'like', $suffix)->exists()
            || (Schema::hasTable('home_highlights') && \App\Models\HomeHighlight::where('image_url', 'like', $suffix)->exists())
            || \App\Models\SocialPost::withTrashed()
                ->where('media_url', 'like', $suffix)
                ->when($ignorePostId, fn ($query) => $query->where('id', '!=', $ignorePostId))
                ->exists()
            || \App\Models\SocialPostMedia::where('media_url', 'like', $suffix)->exists()) return;
        rescue(function () use ($relative) {
            Storage::disk('public')->delete($relative);
            app(AdaptiveVideo::class)->remove($relative);
        }, report: true);
    }

    public function normalize(string $type, string $value): string
    {
        // Accept a provider iframe pasted by admins, but discard all its HTML.
        if (preg_match('/<iframe\b[^>]*\bsrc=["\']([^"\']+)["\']/i', $value, $match)) {
            $value = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
        }
        $value = trim($value);
        // Only one media URL is supported. Reject pasted lists and stray spaces
        // so a malformed value can never reach the mobile renderer.
        if ($value === ''
            || preg_match('/\s/', $value)
            || preg_match_all('~https?://~i', $value) > 1) {
            throw ValidationException::withMessages(['media_url' => 'Enter a single valid HTTPS media URL. Multiple URLs are not supported.']);
        }
        $parts = parse_url($value);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = $parts['path'] ?? '/';
        $trustedHosts = array_filter([
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
            strtolower((string) request()->getHost()),
        ]);
        $isAppUpload = $scheme === 'http'
            && in_array($host, $trustedHosts, true)
            && preg_match('~^/storage/community/[a-zA-Z0-9._/-]+$~', $path)
            && !str_contains($path, '..');

        if (!$parts || (($scheme !== 'https' && !$isAppUpload)) || !$host || isset($parts['user']) || isset($parts['pass'])) {
            throw ValidationException::withMessages(['media_url' => 'Use a valid HTTPS URL.']);
        }
        parse_str($parts['query'] ?? '', $query);
        if ($type === 'youtube') {
            $id = null;
            if ($host === 'youtu.be') $id = trim($path, '/');
            if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com'], true)) {
                $id = $query['v'] ?? null;
                if (preg_match('~^/(?:embed|shorts|live)/([\w-]+)~', $path, $match)) $id = $match[1];
            }
            if (!is_string($id) || !preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
                throw ValidationException::withMessages(['media_url' => 'Paste a valid YouTube video URL or iframe.']);
            }
            return 'https://www.youtube.com/embed/'.$id.'?playsinline=1';
        }
        if ($type === 'facebook') {
            if (!in_array($host, ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com'], true)) {
                throw ValidationException::withMessages(['media_url' => 'Use a public Facebook video URL.']);
            }
            if ($path === '/plugins/video.php') {
                return $this->normalize('facebook', (string) ($query['href'] ?? ''));
            }
            if (!preg_match('~/(?:videos|reel)/[0-9]+~', $path) && !($path === '/watch/' && isset($query['v']) && ctype_digit((string) $query['v']))) {
                throw ValidationException::withMessages(['media_url' => 'Paste the full public Facebook video URL (not a share shortcut).']);
            }
            return 'https://www.facebook.com/plugins/video.php?href='.rawurlencode($value).'&show_text=false&width=560';
        }
        return trim($value);
    }
}
