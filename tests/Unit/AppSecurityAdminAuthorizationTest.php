<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\AppSecurity\Services\AdminSecurityAuthorization;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityAdminAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('permission_key')->unique();
        });
        Schema::connection('sqlite')->create('permission_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        foreach (['app_security_view', 'app_security_device_approval'] as $key) {
            $id = DB::table('permissions')->insertGetId(['permission_key' => $key]);
            DB::table('permission_roles')->insert(['permission_id' => $id, 'role_id' => 7]);
        }
        $user = new User();
        $user->id = 8;
        $user->role_id = 7;
        $user->setRelation('role', (object) ['slug' => 'hr-admin']);
        Schema::connection('sqlite')->create('app_security_admin_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id')->unique();
            $table->string('security_role');
        });
        DB::table('app_security_admin_roles')->insert(['admin_id' => 8, 'security_role' => 'hr_admin']);
        Auth::guard('admin')->setUser($user);
    }

    public function test_hr_approval_grant_does_not_become_full_device_or_release_access(): void
    {
        $authorization = app(AdminSecurityAuthorization::class);
        $this->assertTrue($authorization->allowsAny('app_security_view'));
        $this->assertTrue($authorization->allowsAny('app_security_device_approval'));
        $this->assertFalse($authorization->allowsAny('app_security_risk_review'));
        $this->assertFalse($authorization->allowsAny('app_security_devices'));
        $this->assertFalse($authorization->allowsAny('app_security_releases'));
        $this->assertFalse($authorization->allowsAny('app_security_audit_export'));

        DB::table('app_security_admin_roles')->where('admin_id', 8)->update(['security_role' => 'customer_support']);
        $supportAuthorization = app(AdminSecurityAuthorization::class);
        $this->assertTrue($supportAuthorization->allowsAny('app_security_view'));
        $this->assertTrue($supportAuthorization->allowsAny('app_security_customer_support'));
        $this->assertFalse($supportAuthorization->allowsAny('app_security_devices'));
        $this->assertFalse($supportAuthorization->allowsAny('app_security_audit'));
        $this->assertFalse($supportAuthorization->allowsAny('app_security_audit_export'));
        $this->assertFalse($supportAuthorization->allowsAny('app_security_risk_review'));
        $this->assertFalse($supportAuthorization->allowsAny('app_security_releases'));
    }

    public function test_general_manager_and_it_admin_can_review_risk_signals_but_auditor_is_read_only(): void
    {
        foreach (['general_manager', 'it_admin'] as $role) {
            DB::table('app_security_admin_roles')->where('admin_id', 8)->update(['security_role' => $role]);
            $this->assertTrue(app(AdminSecurityAuthorization::class)->allowsAny('app_security_risk_review'));
        }
        DB::table('app_security_admin_roles')->where('admin_id', 8)->update(['security_role' => 'auditor']);
        $this->assertFalse(app(AdminSecurityAuthorization::class)->allowsAny('app_security_risk_review'));
    }

    public function test_auditor_and_it_admin_can_export_audit_logs(): void
    {
        foreach (['auditor', 'it_admin'] as $role) {
            DB::table('app_security_admin_roles')->where('admin_id', 8)->update(['security_role' => $role]);
            $this->assertTrue(app(AdminSecurityAuthorization::class)->allowsAny('app_security_audit_export'));
        }
    }

}
