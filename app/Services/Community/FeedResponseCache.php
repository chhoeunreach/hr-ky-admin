<?php

namespace App\Services\Community;

use App\Models\SocialComment;
use App\Models\SocialPollVote;
use App\Models\SocialPost;
use App\Models\SocialPostPreference;
use App\Models\SocialPostSave;
use App\Models\SocialReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeedResponseCache
{
    private const VERSION_PREFIX = 'social_feed:version:';

    public static function enabled(): bool
    {
        return (int) config('social_feed.cache_ttl', 0) > 0;
    }

    public static function remember(Request $request, int $companyId, callable $callback): array
    {
        if (!self::enabled()) return $callback();

        try {
            $ttl = max(1, (int) config('social_feed.cache_ttl', 0));
            return Cache::remember(self::key($request, $companyId), now()->addSeconds($ttl), $callback);
        } catch (\Throwable $e) {
            // A cache outage must never take the feed down.
            return $callback();
        }
    }

    public static function bump(int $companyId): void
    {
        if ($companyId <= 0 || !self::enabled()) return;

        try {
            Cache::forever(self::VERSION_PREFIX.$companyId, (string) microtime(true));
        } catch (\Throwable $e) {
            // Best-effort invalidation; the short TTL still bounds staleness.
        }
    }

    public static function registerInvalidation(): void
    {
        SocialPost::saved(function (SocialPost $post) {
            self::bump((int) $post->company_id);
        });
        SocialPost::deleted(function (SocialPost $post) {
            self::bump((int) $post->company_id);
        });

        foreach ([SocialReaction::class, SocialComment::class, SocialPostSave::class, SocialPostPreference::class] as $model) {
            $model::saved(fn ($row) => self::bumpForPost($row->social_post_id ?? null));
            $model::deleted(fn ($row) => self::bumpForPost($row->social_post_id ?? null));
        }

        SocialPollVote::saved(fn ($vote) => self::bumpForPoll($vote->social_poll_id ?? null));
        SocialPollVote::deleted(fn ($vote) => self::bumpForPoll($vote->social_poll_id ?? null));
    }

    private static function key(Request $request, int $companyId): string
    {
        $version = (string) Cache::get(self::VERSION_PREFIX.$companyId, '0');
        $query = http_build_query($request->query());
        return 'social_feed:v1:'.$companyId.':'.$version.':'.$request->user()->id.':'.sha1($query);
    }

    private static function bumpForPost(?int $postId): void
    {
        if (!$postId || !self::enabled()) return;
        $companyId = DB::table('social_posts')->where('id', $postId)->value('company_id');
        if ($companyId) self::bump((int) $companyId);
    }

    private static function bumpForPoll(?int $pollId): void
    {
        if (!$pollId || !self::enabled()) return;
        $postId = DB::table('social_polls')->where('id', $pollId)->value('social_post_id');
        self::bumpForPost($postId === null ? null : (int) $postId);
    }
}
