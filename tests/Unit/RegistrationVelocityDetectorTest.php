<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\RegistrationVelocityDetector;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegistrationVelocityDetectorTest extends TestCase
{
    public function test_it_emits_a_review_signal_only_after_the_configured_threshold(): void
    {
        config([
            'app_security.registration_velocity_window_seconds' => 900,
            'app_security.registration_velocity_threshold' => 3,
        ]);
        Cache::shouldReceive('add')->times(3)->andReturn(true, false, false);
        Cache::shouldReceive('increment')->twice()->andReturn(2, 3);

        $detector = app(RegistrationVelocityDetector::class);
        $this->assertNull($detector->observe('198.51.100.18'));
        $this->assertNull($detector->observe('198.51.100.18'));
        $this->assertSame([
            'count' => 3,
            'threshold' => 3,
            'window_seconds' => 900,
        ], $detector->observe('198.51.100.18'));
    }

    public function test_cache_key_contains_a_keyed_hash_instead_of_the_raw_ip(): void
    {
        $ip = '198.51.100.27';
        $expectedHash = hash_hmac('sha256', $ip, (string) config('app.key'));
        Cache::shouldReceive('add')->once()->withArgs(function (string $key, int $value, int $ttl) use ($ip, $expectedHash): bool {
            $this->assertStringNotContainsString($ip, $key);
            $this->assertStringContainsString($expectedHash, $key);
            $this->assertSame(1, $value);
            $this->assertGreaterThan(0, $ttl);
            return true;
        })->andReturn(true);

        $this->assertNull(app(RegistrationVelocityDetector::class)->observe($ip));
    }

    public function test_missing_source_address_does_not_touch_cache_or_raise_a_signal(): void
    {
        Cache::shouldReceive('add')->never();
        Cache::shouldReceive('increment')->never();

        $this->assertNull(app(RegistrationVelocityDetector::class)->observe(null));
        $this->assertNull(app(RegistrationVelocityDetector::class)->observe('  '));
    }
}
