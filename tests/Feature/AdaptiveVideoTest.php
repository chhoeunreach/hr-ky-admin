<?php
namespace Tests\Feature;

use App\Services\Community\AdaptiveVideo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class AdaptiveVideoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); Storage::fake('local');
        config(['community_video.enabled' => true, 'app.url' => 'https://example.test']);
    }

    public function test_original_remains_until_manifest_exists_and_remote_urls_are_ignored(): void
    {
        $service = app(AdaptiveVideo::class);
        $url = 'https://example.test/storage/community/example.mp4';
        $this->assertNull($service->playbackUrl($url, 'video'));
        Storage::disk('public')->put($service->directory('community/example.mp4').'/master.m3u8', '#EXTM3U');
        $this->assertStringEndsWith('/master.m3u8', $service->playbackUrl($url, 'video'));
        $this->assertNull($service->playbackUrl(str_replace('example.test', 'remote.test', $url), 'video'));
        $this->assertNull($service->playbackUrl($url, 'image'));
        $this->assertNull($service->playbackUrl($url.'?signed=1', 'video'));
    }

    public function test_only_local_video_paths_are_queued_once(): void
    {
        $service = app(AdaptiveVideo::class);
        foreach (['../secret.mp4', 'https://evil.test/movie.mp4', 'community/a.jpg', 'community/../movie.mp4'] as $path) $service->enqueue($path);
        $this->assertCount(0, Storage::disk('local')->allFiles('video-pending'));
        $service->enqueue('community/clip.mp4'); $service->enqueue('community/clip.mp4');
        $this->assertCount(1, Storage::disk('local')->allFiles('video-pending'));
    }

    public function test_model_keeps_original_media_url_and_exposes_optional_playback_url(): void
    {
        $service = app(AdaptiveVideo::class);
        foreach ([\App\Models\SocialPost::class, \App\Models\SocialPostMedia::class,
            \App\Models\SocialStory::class, \App\Models\FlashView::class] as $class) {
            $model = new $class(['media_type' => 'video', 'media_url' => 'https://example.test/storage/stories/clip.mp4']);
            Storage::disk('public')->put($service->directory('stories/clip.mp4').'/master.m3u8', '#EXTM3U');
            $this->assertSame('https://example.test/storage/stories/clip.mp4', $model->media_url);
            $this->assertStringEndsWith('/master.m3u8', $model->playback_url);
        }
    }

    public function test_cleanup_removes_derived_segments_and_pending_marker_but_preserves_original(): void
    {
        $service = app(AdaptiveVideo::class); $path = 'community/clip.mp4';
        Storage::disk('public')->put($path, 'original');
        Storage::disk('public')->put($service->directory($path).'/segment.ts', 'segment');
        $service->enqueue($path); $service->remove($path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing($service->directory($path).'/segment.ts');
        $this->assertCount(0, Storage::disk('local')->allFiles('video-pending'));
    }

    public function test_encoding_failure_never_publishes_a_partial_stream(): void
    {
        config(['community_video.ffmpeg' => '/nonexistent/ffmpeg']);
        $service = app(AdaptiveVideo::class);
        Storage::disk('public')->put('community/clip.mp4', 'invalid');
        try { $service->encode('community/clip.mp4'); $this->fail('Expected encoder failure'); }
        catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            Storage::disk('public')->assertMissing($service->directory('community/clip.mp4').'/master.m3u8');
            Storage::disk('public')->assertExists('community/clip.mp4');
            $this->assertCount(1, Storage::disk('public')->allFiles());
        }
    }

    public function test_real_encoder_builds_playable_variants_for_landscape_audio_and_silent_portrait(): void
    {
        $binary = config('community_video.ffmpeg', 'ffmpeg');
        $available = new Process([$binary, '-version']); $available->run();
        if (!$available->isSuccessful()) $this->markTestSkipped('FFmpeg is required for the encoding integration test.');
        foreach ([['640x360', true], ['180x320', false]] as $index => [$size, $audio]) {
            $path = 'community/test'.$index.'.mp4';
            $command = [$binary, '-y', '-f', 'lavfi', '-i', 'testsrc2=size='.$size.':rate=30'];
            if ($audio) array_push($command, '-f', 'lavfi', '-i', 'sine=frequency=1000:sample_rate=44100');
            array_push($command, '-t', '5', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-threads', '2');
            if ($audio) array_push($command, '-c:a', 'aac');
            $command[] = Storage::disk('public')->path($path);
            Storage::disk('public')->makeDirectory('community');
            (new Process($command))->mustRun();
            $service = app(AdaptiveVideo::class); $service->encode($path);
            $directory = $service->directory($path);
            $master = Storage::disk('public')->get($directory.'/master.m3u8');
            $this->assertStringContainsString('144.m3u8', $master);
            $this->assertLessThan(strpos($master, '240.m3u8'), strpos($master, '144.m3u8'));
            $this->assertStringContainsString('BANDWIDTH=190000', $master);
            $this->assertLessThan(strpos($master, '720.m3u8'), strpos($master, '240.m3u8'));
            foreach (['144', '240', '360', '720'] as $quality) {
                $playlist = Storage::disk('public')->path($directory.'/'.$quality.'.m3u8');
                (new Process([$binary, '-v', 'error', '-i', $playlist, '-f', 'null', '-']))->mustRun();
                $this->assertStringContainsString('#EXT-X-ENDLIST', file_get_contents($playlist));
                $this->assertStringContainsString('#EXT-X-TARGETDURATION:2', file_get_contents($playlist));
                $this->assertSame(3, substr_count(file_get_contents($playlist), '#EXTINF:'));
            }
            Storage::disk('public')->assertExists($path);
        }
    }
}
