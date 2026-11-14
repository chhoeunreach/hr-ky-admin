<?php
namespace App\Services\Community;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class AdaptiveVideo
{
    public function directory(string $path): string
    {
        // A new immutable URL prevents stale cached playlists after encoder changes.
        return 'video-streams/v2/'.hash('sha256', $path);
    }

    public function enqueue(string $path): void
    {
        if (!config('community_video.enabled', true) || !$this->validPath($path)) return;
        $disk = Storage::disk('local');
        $key = 'video-pending/'.hash('sha256', $path).'.json';
        if (!$disk->exists($key)) $disk->put($key, json_encode(['path' => $path, 'attempts' => 0], JSON_THROW_ON_ERROR));
        // Prefer the queue when a real worker is configured; the marker above
        // remains the fallback for hosts that only run the scheduled command.
        if (config('community_video.queue', false) && config('queue.default') !== 'sync') {
            \App\Jobs\ProcessCommunityVideo::dispatch($path)->onQueue(config('community_video.queue_name', 'media'));
        }
    }

    public function playbackUrl(?string $url, ?string $type): ?string
    {
        if ($type !== 'video' || !$url || !config('community_video.enabled', true)) return null;
        $location = $this->streamLocation($url);
        if ($location === null) return null;
        [$path, $origin] = $location;
        $manifest = $this->directory($path).'/master.m3u8';
        if (!Storage::disk('public')->exists($manifest)) return null;
        return $origin.'/storage/'.$manifest;
    }

    public function thumbnailUrl(?string $url, ?string $type): ?string
    {
        if ($type !== 'video' || !$url || !config('community_video.enabled', true)) return null;
        $location = $this->streamLocation($url);
        if ($location === null) return null;
        [$path, $origin] = $location;
        $poster = $this->directory($path).'/poster.jpg';
        if (!Storage::disk('public')->exists($poster)) return null;
        return $origin.'/storage/'.$poster;
    }

    /** Resolves an own-storage media URL to [relative path, request origin]. */
    private function streamLocation(?string $url): ?array
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~^/storage/((?:community|stories)/[a-zA-Z0-9._-]+)$~', $path, $m)) return null;
        // Preserve the request's media hostname; only inspect our own local storage path.
        if (!$this->validPath($m[1])) return null;
        $parts = parse_url((string) $url);
        if (!empty($parts['query']) || !empty($parts['user']) || !empty($parts['pass'])) return null;
        // A remote URL with a coincidentally matching path must never use a local rendition.
        $hosts = array_filter([parse_url(config('app.url'), PHP_URL_HOST), request()->getHost()]);
        if (!in_array($parts['host'] ?? '', $hosts, true)) return null;
        $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
        return [$m[1], $origin];
    }

    public function validPath(string $path): bool
    {
        return (bool) preg_match('~^(?:community|stories)/[a-zA-Z0-9_-]+\.(?:mp4|mov|webm|m4v|qt)$~i', $path);
    }

    public function encode(string $path): void
    {
        if (!$this->validPath($path)) throw new \InvalidArgumentException('Invalid local video path.');
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) return;
        $target = $this->directory($path);
        if ($disk->exists($target.'/master.m3u8')) {
            // Backfill posters for streams encoded before thumbnails existed.
            if (!$disk->exists($target.'/poster.jpg')) $this->poster($disk->path($path), $disk->path($target).'/poster.jpg');
            return;
        }
        $work = $target.'-'.bin2hex(random_bytes(6));
        $disk->makeDirectory($work);
        $workPath = $disk->path($work);
        try {
            $variants = [];
            // Width AND height bounds preserve landscape/portrait ratios; never upscale.
            foreach ([[256, 144, 120, 32], [426, 240, 300, 48], [640, 360, 650, 64], [1280, 720, 1800, 64]] as [$width, $height, $bitrate, $audioBitrate]) {
                $name = (string) $height;
                $filter = "scale=w='min(iw,$width)':h='min(ih,$height)':force_original_aspect_ratio=decrease:force_divisible_by=2,setsar=1";
                $command = [config('community_video.ffmpeg', 'ffmpeg'), '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
                    // No remote protocols: uploads must not fetch nested URLs during decoding.
                    '-protocol_whitelist', 'file,pipe,crypto,data', '-i', $disk->path($path),
                    '-map', '0:v:0', '-map', '0:a:0?', '-sn', '-dn', '-vf', $filter,
                    '-c:v', 'libx264', '-preset', 'veryfast', '-pix_fmt', 'yuv420p', '-threads', '2',
                    '-r', '30', '-b:v', $bitrate.'k', '-maxrate', $bitrate.'k', '-bufsize', ($bitrate * 2).'k',
                    '-g', '60', '-keyint_min', '60', '-sc_threshold', '0',
                    '-force_key_frames', 'expr:gte(t,n_forced*2)',
                    '-c:a', 'aac', '-b:a', $audioBitrate.'k', '-ac', '2',
                    '-f', 'hls', '-hls_time', '2', '-hls_playlist_type', 'vod', '-hls_list_size', '0',
                    '-hls_flags', 'independent_segments',
                    '-hls_segment_filename', $workPath.'/'.$name.'_%05d.ts', $workPath.'/'.$name.'.m3u8'];
                $process = new Process($command);
                $process->setTimeout((float) config('community_video.timeout', 600));
                $process->mustRun();
                $variants[] = '#EXT-X-STREAM-INF:BANDWIDTH='.(int) ceil(($bitrate + $audioBitrate) * 1250)."\n".$name.'.m3u8';
            }
            // Publish only after all segments and playlists are complete. Lowest bitrate first.
            $disk->put($work.'/master.m3u8', "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-INDEPENDENT-SEGMENTS\n".implode("\n", $variants)."\n");
            // Poster frames let the feed paint instantly and skip decoding the first frames.
            $this->poster($disk->path($path), $workPath.'/poster.jpg');
            if (!$disk->exists($path)) return;
            if ($disk->exists($target.'/master.m3u8')) return;
            $disk->move($work, $target);
        } finally {
            if ($disk->exists($work)) $disk->deleteDirectory($work);
        }
    }

    /** Best-effort JPEG poster; a broken thumbnail never blocks playback. */
    private function poster(string $source, string $destination): void
    {
        if (!is_file($source)) return;
        try {
            if (!is_dir(dirname($destination))) @mkdir(dirname($destination), 0775, true);
            foreach ([1, 0] as $seek) {
                $command = [config('community_video.ffmpeg', 'ffmpeg'), '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
                    '-protocol_whitelist', 'file,pipe,crypto,data', '-ss', (string) $seek, '-i', $source,
                    '-frames:v', '1', '-vf', "scale=w='min(iw,640)':h='min(ih,640)':force_original_aspect_ratio=decrease:force_divisible_by=2",
                    '-q:v', '4', $destination];
                $process = new Process($command);
                $process->setTimeout(60);
                try { $process->mustRun(); } catch (\Throwable $e) { report($e); }
                if (is_file($destination) && filesize($destination) > 0) return;
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function remove(string $path): void
    {
        if (!$this->validPath($path)) return;
        Storage::disk('public')->deleteDirectory($this->directory($path));
        Storage::disk('public')->deleteDirectory('video-streams/'.hash('sha256', $path));
        Storage::disk('local')->delete('video-pending/'.hash('sha256', $path).'.json');
    }
}
