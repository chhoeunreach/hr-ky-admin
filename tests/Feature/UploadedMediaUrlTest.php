<?php
namespace Tests\Feature;

use App\Services\Community\UploadedMediaUrl;
use App\Services\Community\AdaptiveVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedMediaUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->app->instance('request', Request::create('http://192.168.0.120:8000/api/social-feed'));
        config(['app.url' => 'https://hr.example.test']);
        Storage::disk('public')->put('community/clip.mp4', 'original');
        Storage::disk('public')->put('community/thumbnails/clip.jpg', 'thumbnail');
    }

    public function test_stale_lan_uploads_use_the_current_request_server_and_original_database_value_is_kept(): void
    {
        $stored = 'http://192.168.0.118:8000/storage/community/clip.mp4';
        $model = new \App\Models\SocialPost(['media_type' => 'video', 'media_url' => $stored]);
        $service = app(UploadedMediaUrl::class);
        $this->assertSame('http://192.168.0.120:8000/storage/community/clip.mp4', $service->resolve($stored));
        $this->assertSame($stored, $model->media_url);
        Storage::disk('public')->put(app(AdaptiveVideo::class)->directory('community/clip.mp4').'/master.m3u8', '#EXTM3U');
        $this->assertStringStartsWith('http://192.168.0.120:8000/storage/video-streams/', $model->playback_url);
    }

    public function test_nested_attachments_and_thumbnails_are_normalized(): void
    {
        $data = app(UploadedMediaUrl::class)->payload(['media' => [[
            'media_url' => 'http://127.0.0.1:8000/storage/community/clip.mp4',
            'thumbnail_url' => 'http://192.168.0.118:8000/storage/community/thumbnails/clip.jpg',
        ]]]);
        $this->assertStringStartsWith('http://192.168.0.120:8000/', $data['media'][0]['media_url']);
        $this->assertStringStartsWith('http://192.168.0.120:8000/', $data['media'][0]['thumbnail_url']);
    }

    public function test_external_signed_missing_and_traversal_urls_are_not_rewritten(): void
    {
        foreach ([
            'https://external.test/storage/community/clip.mp4',
            'https://hr.example.test/storage/community/clip.mp4?signature=abc',
            'http://192.168.0.118:8000/storage/community/missing.mp4',
            'http://192.168.0.118:8000/storage/community/../clip.mp4',
            'https://www.youtube.com/embed/abcdefghijk',
        ] as $url) $this->assertSame($url, app(UploadedMediaUrl::class)->resolve($url));
    }
}
