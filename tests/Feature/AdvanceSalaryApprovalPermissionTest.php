<?php

namespace Tests\Feature;

use App\Models\AdvanceSalary;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdvanceSalaryApprovalPermissionTest extends TestCase
{
    use WithFaker;

    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            $dbPath = (string) env('DB_DATABASE');
            if ($dbPath !== '' && $dbPath !== ':memory:' && !file_exists($dbPath)) {
                @touch($dbPath);
            }

            // Run only the minimal set of migrations required for these tests (SQLite-friendly).
            Artisan::call('migrate:fresh', [
                '--path' => 'database/migrations/testing',
                '--force' => true,
            ]);

            self::$migrated = true;
        }

        // Clean tables for isolation between tests.
        DB::table('permission_roles')->delete();
        DB::table('permissions')->delete();
        DB::table('advance_salaries')->delete();
        DB::table('users')->delete();
        DB::table('roles')->delete();
        DB::table('app_settings')->delete();

        DB::table('app_settings')->insert([
            [
                'name' => 'BS Date',
                'slug' => 'bs',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '24 Hour Format',
                'slug' => '24-hour-format',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function makeRole(string $slug = 'employee'): Role
    {
        return Role::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => 1,
        ]);
    }

    private function grantAdvanceSalaryApprove(Role $role): void
    {
        $permission = Permission::create([
            'name' => 'Advance Salary Approval Permission',
            'permission_key' => 'advance-salary-approve',
            'permission_groups_id' => null,
        ]);

        PermissionRole::create([
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
    }

    private function makeUser(Role $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'username' => uniqid('u_', true),
        ], $overrides));
    }

    private function makeAdvanceSalary(User $requester, array $overrides = []): AdvanceSalary
    {
        Passport::actingAs($requester);
        return AdvanceSalary::create(array_merge([
            'employee_id' => $requester->id,
            'requested_amount' => 100,
            'released_amount' => null,
            'advance_requested_date' => now(),
            'amount_granted_date' => null,
            'description' => 'Need money',
            'is_settled' => 0,
            'status' => 'pending',
            'remark' => null,
            'verified_by' => null,
            'created_by' => $requester->id,
        ], $overrides));
    }

    public function test_approve_requires_permission(): void
    {
        $role = $this->makeRole('employee');
        $approver = $this->makeUser($role);
        $requester = $this->makeUser($role);
        $advanceSalary = $this->makeAdvanceSalary($requester);

        Passport::actingAs($approver);

        $response = $this->postJson("/api/employee/advance-salaries/{$advanceSalary->id}/approve", [
            'released_amount' => 50,
            'remark' => 'ok',
        ]);

        $response->assertStatus(403);
    }

    public function test_approve_only_when_pending(): void
    {
        $role = $this->makeRole('finance');
        $this->grantAdvanceSalaryApprove($role);
        $approver = $this->makeUser($role);
        $requester = $this->makeUser($role);
        $advanceSalary = $this->makeAdvanceSalary($requester, ['status' => 'processing']);

        Passport::actingAs($approver);

        $response = $this->postJson("/api/employee/advance-salaries/{$advanceSalary->id}/approve", [
            'released_amount' => 50,
            'remark' => 'ok',
        ]);

        $response->assertStatus(409);
    }

    public function test_can_approve_pending_request_with_permission(): void
    {
        $role = $this->makeRole('finance');
        $this->grantAdvanceSalaryApprove($role);
        $approver = $this->makeUser($role);
        $requester = $this->makeUser($role);
        $advanceSalary = $this->makeAdvanceSalary($requester);

        Passport::actingAs($approver);

        $response = $this->postJson("/api/employee/advance-salaries/{$advanceSalary->id}/approve", [
            'released_amount' => 80,
            'remark' => 'approved',
        ]);

        $response->assertStatus(200);

        $advanceSalary->refresh();
        $this->assertSame('approved', $advanceSalary->status);
        $this->assertSame($approver->id, $advanceSalary->verified_by);
        $this->assertSame(80.0, (float) $advanceSalary->released_amount);
        $this->assertNotNull($advanceSalary->amount_granted_date);
        $this->assertSame('approved', $advanceSalary->remark);
        $this->assertFalse((bool) $advanceSalary->is_settled);
    }

    public function test_can_reject_pending_request_with_permission(): void
    {
        $role = $this->makeRole('hr');
        $this->grantAdvanceSalaryApprove($role);
        $approver = $this->makeUser($role);
        $requester = $this->makeUser($role);
        $advanceSalary = $this->makeAdvanceSalary($requester);

        Passport::actingAs($approver);

        $response = $this->postJson("/api/employee/advance-salaries/{$advanceSalary->id}/reject", [
            'remark' => 'reject',
        ]);

        $response->assertStatus(200);

        $advanceSalary->refresh();
        $this->assertSame('rejected', $advanceSalary->status);
        $this->assertSame($approver->id, $advanceSalary->verified_by);
        $this->assertSame('reject', $advanceSalary->remark);
        $this->assertTrue((bool) $advanceSalary->is_settled);
        $this->assertNull($advanceSalary->released_amount);
        $this->assertNull($advanceSalary->amount_granted_date);
    }

    // Dashboard feature flag is covered by integration testing in the app environment,
    // since the dashboard endpoint depends on many other tables/services.
}
