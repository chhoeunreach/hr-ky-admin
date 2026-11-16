<?php

return [
    /*
     * The feed response cache is intentionally opt-in. It stores a short-lived
     * per-user copy of the paginated feed (cursor included) so bursts of app
     * refreshes do not repeatedly hit the database. Set
     * SOCIAL_FEED_CACHE_TTL=0 to disable it. A Redis cache store is recommended
     * when this is enabled.
     */
    'cache_ttl' => (int) env('SOCIAL_FEED_CACHE_TTL', 0),

    'gzip' => (bool) env('SOCIAL_FEED_GZIP', true),
    'gzip_min_bytes' => (int) env('SOCIAL_FEED_GZIP_MIN_BYTES', 2048),
];
