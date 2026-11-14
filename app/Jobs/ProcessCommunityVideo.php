<?php

namespace App\Jobs;

use App\Services\Community\AdaptiveVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Encodes an uploaded feed/story/Flash video into HLS off the request thread.
 *
 * The pending marker written by AdaptiveVideo::enqueue() stays in place until
 * the job succeeds so the scheduled command can still pick the video up if a
 * worker is not running or the job is exhausted.
 */
class ProcessCommunityVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(public string $path)
    {
        $this->onQueue((string) config('community_video.queue_name', 'media'));
    }

    public function handle(AdaptiveVideo $video): void
    {
        if (!config('community_video.enabled', true) || !$video->validPath($this->path)) return;
        $video->encode($this->path);
        Storage::disk('local')->delete('video-pending/'.hash('sha256', $this->path).'.json');
    }
}
