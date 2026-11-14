<?php
return [
    'enabled' => env('COMMUNITY_VIDEO_OPTIMIZE', true),
    // Queue jobs run only when enabled and a non-sync queue is configured, so
    // test suites never shell out to FFmpeg. The scheduled command still works
    // as a fallback when no worker is running.
    'queue' => env('COMMUNITY_VIDEO_QUEUE', false),
    'queue_name' => env('COMMUNITY_VIDEO_QUEUE_NAME', 'media'),
    'ffmpeg' => env('COMMUNITY_FFMPEG', is_executable(storage_path('app/video-tools/ffmpeg'))
        ? storage_path('app/video-tools/ffmpeg') : 'ffmpeg'),
    'timeout' => 600,
];
