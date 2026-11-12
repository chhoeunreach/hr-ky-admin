<?php
namespace App\Console\Commands;

use App\Services\Community\AdaptiveVideo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeCommunityVideos extends Command
{
    protected $signature = 'community:optimize-videos {--limit=2 : Maximum videos this run} {--backfill : Queue existing uploads}';
    protected $description = 'Create adaptive HLS streams for uploaded feed, story and Flash View videos';

    public function handle(AdaptiveVideo $video): int
    {
        if (!config('community_video.enabled', true)) return self::SUCCESS;
        $local = Storage::disk('local');
        $local->makeDirectory('video-pending');
        // OS lock survives cache clears and prevents cron/manual encoders from racing.
        $lock = fopen($local->path('video-pending/worker.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { if ($lock) fclose($lock); return self::SUCCESS; }
        $failed = false;
        try {
            if ($this->option('backfill')) {
                foreach (['community', 'stories'] as $folder) {
                    foreach (Storage::disk('public')->files($folder) as $path) {
                        if ($video->validPath($path)) $video->enqueue($path);
                    }
                }
            }
            $count = 0;
            foreach ($local->files('video-pending') as $file) {
                if (!str_ends_with($file, '.json')) continue;
                if ($count++ >= max(1, (int) $this->option('limit'))) break;
                $data = json_decode($local->get($file), true);
                try {
                    $video->encode((string) ($data['path'] ?? ''));
                    $local->delete($file);
                    $this->info('Optimized '.($data['path'] ?? 'video'));
                } catch (\Throwable $e) {
                    $failed = true;
                    $data['attempts'] = (int) ($data['attempts'] ?? 0) + 1;
                    $local->put($file, json_encode($data));
                    if ($data['attempts'] >= 3) $local->move($file, $file.'.failed');
                    report($e);
                    $this->error('Video optimization failed; original retained: '.($data['path'] ?? 'unknown'));
                }
            }
        } finally { flock($lock, LOCK_UN); fclose($lock); }
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
