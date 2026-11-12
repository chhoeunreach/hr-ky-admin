<?php
return [
    'enabled' => env('COMMUNITY_VIDEO_OPTIMIZE', true),
    'ffmpeg' => env('COMMUNITY_FFMPEG', is_executable(storage_path('app/video-tools/ffmpeg'))
        ? storage_path('app/video-tools/ffmpeg') : 'ffmpeg'),
    'timeout' => 600,
];
