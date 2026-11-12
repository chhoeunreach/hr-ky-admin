<?php

namespace App\Console\Commands;

use App\Models\FlashView;
use App\Models\SocialPost;
use App\Models\SocialPostMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class PruneCommunityMedia extends Command
{
    protected $signature = 'community:prune-media {--delete : Delete unreferenced uploads older than 24 hours}';
    protected $description = 'List or remove orphaned community uploads; never removes referenced media';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $count = 0;
        foreach ($disk->allFiles('community') as $path) {
            if ($disk->lastModified($path) > now()->subDay()->timestamp) continue;
            $suffix = '%/storage/'.$path;
            $matches = fn ($query) => $query->where('media_url', 'like', $suffix);
            // Compare storage paths so primary/backup host changes cannot orphan live media.
            // Retain media belonging to soft-deleted posts for administrative recovery.
            if (FlashView::where($matches)->exists() || SocialPost::withTrashed()->where($matches)->exists() || SocialPostMedia::where($matches)->exists()
                || (Schema::hasTable('home_highlights') && \App\Models\HomeHighlight::where('image_url', 'like', $suffix)->exists())) continue;
            $this->line($path);
            if ($this->option('delete')) {
                $disk->delete($path);
                app(\App\Services\Community\AdaptiveVideo::class)->remove($path);
            }
            $count++;
        }
        $this->info($count.' orphaned upload(s) '.($this->option('delete') ? 'removed.' : 'found. Use --delete to remove them.'));
        return self::SUCCESS;
    }
}
