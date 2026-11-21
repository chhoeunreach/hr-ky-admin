<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Jobs\SendAppSecurityUserNotification;
use App\Modules\AppSecurity\Services\AppSecurityMobileNotificationSender;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class SendAppSecurityUserNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('company_id')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('notifications', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->text('description'); $table->string('type')->default('general');
            $table->timestamp('notification_publish_date'); $table->unsignedBigInteger('notification_for_id')->nullable();
            $table->string('security_event_key', 191)->nullable()->unique(); $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('company_id'); $table->unsignedBigInteger('created_by'); $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('user_notifications', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('notification_id'); $table->unsignedBigInteger('user_id');
            $table->boolean('is_seen')->default(false); $table->timestamps();
        });
        DB::table('users')->insert(['id' => 12, 'company_id' => 4, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_persists_targeted_inbox_notification_idempotently_and_sends_push(): void
    {
        $sender = Mockery::mock(AppSecurityMobileNotificationSender::class);
        $sender->shouldReceive('send')->twice()->with(12, 1, 'event-1', 'Access restored', 'Open the app.');
        $this->instance(AppSecurityMobileNotificationSender::class, $sender);
        $job = new SendAppSecurityUserNotification(12, 'event-1', 'Access restored', 'Open the app.');

        $job->handle($sender);
        $job->handle($sender); // Queue retries reuse one persistent notification and one event key.

        $this->assertSame(1, DB::table('notifications')->count());
        $this->assertSame(1, DB::table('user_notifications')->count());
        $this->assertDatabaseHas('notifications', ['security_event_key' => 'event-1', 'company_id' => 4, 'created_by' => 12]);
        $this->assertDatabaseHas('user_notifications', ['notification_id' => 1, 'user_id' => 12, 'is_seen' => 0]);
    }
}
