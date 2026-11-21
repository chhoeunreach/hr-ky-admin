<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\CrossAccountKeyReuseDetector;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrossAccountKeyReuseDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('installation_uuid');
            $table->string('public_key_fingerprint');
        });
    }

    public function test_same_public_key_across_accounts_is_detected_but_installation_uuid_alone_is_not(): void
    {
        DB::table('app_registered_devices')->insert([
            ['user_id' => 10, 'installation_uuid' => 'shared-installation', 'public_key_fingerprint' => 'key-A'],
            ['user_id' => 20, 'installation_uuid' => 'shared-installation', 'public_key_fingerprint' => 'key-B'],
        ]);
        $detector = app(CrossAccountKeyReuseDetector::class);

        $this->assertSame(['detected' => false, 'linked_account_count' => 0], $detector->inspect('key-A', 10));
        $this->assertSame(['detected' => false, 'linked_account_count' => 0], $detector->inspect('key-B', 20));
        $this->assertSame(['detected' => true, 'linked_account_count' => 1], $detector->inspect('key-A', 20));
    }

    public function test_linked_account_count_does_not_duplicate_rows_from_one_account(): void
    {
        DB::table('app_registered_devices')->insert([
            ['user_id' => 10, 'installation_uuid' => 'install-1', 'public_key_fingerprint' => 'key-A'],
            ['user_id' => 10, 'installation_uuid' => 'install-2', 'public_key_fingerprint' => 'key-A'],
            ['user_id' => 20, 'installation_uuid' => 'install-3', 'public_key_fingerprint' => 'key-A'],
        ]);

        $this->assertSame(['detected' => true, 'linked_account_count' => 2], app(CrossAccountKeyReuseDetector::class)->inspect('key-A', 30));
    }
}
