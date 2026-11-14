<?php
namespace App\Models\Concerns;
trait HasAdaptiveVideo
{
    public function getPlaybackUrlAttribute(): ?string
    {
        return app(\App\Services\Community\AdaptiveVideo::class)->playbackUrl(app(\App\Services\Community\UploadedMediaUrl::class)->resolve($this->media_url), $this->media_type);
    }

    public function getAdaptiveThumbnailUrlAttribute(): ?string
    {
        return app(\App\Services\Community\AdaptiveVideo::class)->thumbnailUrl(app(\App\Services\Community\UploadedMediaUrl::class)->resolve($this->media_url), $this->media_type);
    }
}
