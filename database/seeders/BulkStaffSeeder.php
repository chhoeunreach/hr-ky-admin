<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkStaffSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = env('STAFF_SEED_CSV', database_path('seeders/data/staff_seed.csv'));

        if (!file_exists($csvPath)) {
            $this->command?->error("CSV not found: {$csvPath}");
            $this->command?->line('Copy template: database/seeders/data/staff_seed_template.csv');
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->command?->error("Unable to open CSV: {$csvPath}");
            return;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->command?->error('CSV is empty.');
            return;
        }

        $header = array_map(fn($item) => trim((string) $item), $header);
        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF\xFEFF");
            $header[0] = str_replace("\u{FEFF}", '', $header[0]);
        }
        $companyId = (int) (env('STAFF_SEED_COMPANY_ID') ?: (DB::table('companies')->value('id') ?? 1));
        $defaultBranchId = (int) (env('STAFF_SEED_BRANCH_ID') ?: (DB::table('branches')->where('company_id', $companyId)->value('id') ?? DB::table('branches')->value('id') ?? 1));
        $defaultPassword = env('STAFF_SEED_DEFAULT_PASSWORD', '12345678');
        $systemUserId = $this->getSystemUserId();
        $defaultRoleId = $this->resolveDefaultRoleId($systemUserId);

        $insertedOrUpdated = 0;
        $invalidRows = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $record = [];
            foreach ($header as $index => $column) {
                $record[$column] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }
            $recordLower = [];
            foreach ($record as $k => $v) {
                $recordLower[strtolower((string) $k)] = $v;
            }

            if (empty($record['name']) || empty($record['email'])) {
                $invalidRows++;
                continue;
            }

            $email = strtolower((string) $record['email']);
            $username = $record['username'] ?: Str::before($email, '@') . '_' . Str::random(4);
            $existingUser = DB::table('users')->where('email', $email)->first();

            $existingBranchId = $existingUser?->branch_id ? (int) $existingUser->branch_id : null;
            $existingCompanyId = $existingUser?->company_id ? (int) $existingUser->company_id : null;
            $existingDepartmentId = $existingUser?->department_id ? (int) $existingUser->department_id : null;
            $existingPostId = $existingUser?->post_id ? (int) $existingUser->post_id : null;
            $existingRoleId = $existingUser?->role_id ? (int) $existingUser->role_id : null;
            $existingOfficeTimeId = $existingUser?->office_time_id ? (int) $existingUser->office_time_id : null;
            $existingSupervisorId = $existingUser?->supervisor_id ? (int) $existingUser->supervisor_id : null;
            $existingDob = $existingUser?->dob ?: null;

            $branchId = $this->resolveBranchId($record['branch_id'] ?? null, $existingBranchId ?: $defaultBranchId);
            $rowCompanyId = $this->resolveCompanyId($record['company_id'] ?? null, $existingCompanyId ?: $companyId);
            $departmentId = $this->resolveDepartmentId($record, $rowCompanyId, $branchId, $systemUserId);
            if (!$departmentId && $existingDepartmentId) {
                $departmentId = $existingDepartmentId;
            }
            $roleId = $this->resolveRoleId($record['role_id'] ?? $defaultRoleId, $systemUserId);
            if (!$roleId) {
                $roleId = $existingRoleId ?: $defaultRoleId;
            }
            $postId = $this->resolvePostId($record['post_id'] ?? null, $departmentId, $branchId);
            if (!$postId && $existingPostId) {
                $postId = $existingPostId;
            }
            $officeTimeId = $this->resolveOfficeTimeId($record['office_time_id'] ?? null, $rowCompanyId, $branchId, $systemUserId);
            if (!$officeTimeId && $existingOfficeTimeId) {
                $officeTimeId = $existingOfficeTimeId;
            }
            $supervisorId = $this->resolveSupervisorId($record['supervisor_id'] ?? null);
            if (!$supervisorId && $existingSupervisorId) {
                $supervisorId = $existingSupervisorId;
            }
            $dob = $this->normalizeDate(
                $recordLower['dob'] ?? $recordLower['date_of_birth'] ?? $recordLower['birth_date'] ?? null
            );
            if (!$dob && $existingDob) {
                $dob = $existingDob;
            }

            $userData = [
                'name' => $record['name'],
                'email' => $email,
                'username' => $username,
                'dob' => $dob,
                'phone' => $this->normalizePhone($record['phone'] ?? null),
                'gender' => $this->normalizeGender($record['gender'] ?? null),
                'marital_status' => $this->normalizeMaritalStatus($record['marital_status'] ?? null),
                'employment_type' => $this->normalizeEmploymentType($record['employment_type'] ?? null),
                'joining_date' => $this->normalizeDate($record['joining_date'] ?? null),
                'department_id' => $departmentId,
                'branch_id' => $branchId,
                'post_id' => $postId,
                'supervisor_id' => $supervisorId,
                'office_time_id' => $officeTimeId,
                'employee_code' => $record['employee_code'] ?: null,
                'role_id' => $roleId,
                'company_id' => $rowCompanyId,
                'status' => $this->normalizeStatus($record['status'] ?? null),
                'is_active' => isset($record['is_active']) && $record['is_active'] !== '' ? (int) $record['is_active'] : 1,
                'allow_holiday_check_in' => isset($record['allow_holiday_check_in']) && $record['allow_holiday_check_in'] !== '' ? (int) $record['allow_holiday_check_in'] : 0,
                'updated_at' => now(),
            ];

            if ($existingUser) {
                if (!empty($record['password'])) {
                    $userData['password'] = Hash::make($record['password']);
                }
                DB::table('users')->where('id', $existingUser->id)->update($userData);
                $userId = (int) $existingUser->id;
            } else {
                $userData['password'] = Hash::make($record['password'] ?: $defaultPassword);
                $userData['created_at'] = now();
                $userId = (int) DB::table('users')->insertGetId($userData);
            }

            DB::table('employee_accounts')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'bank_name' => $record['bank_name'] ?: null,
                    'bank_account_no' => $record['bank_account_no'] ?: null,
                    'bank_account_type' => $record['bank_account_type'] ?: null,
                    'account_holder' => $record['account_holder'] ?: $record['name'],
                ]
            );

            $insertedOrUpdated++;
        }

        fclose($handle);

        $this->command?->info("Staff import complete. Inserted/Updated: {$insertedOrUpdated}, Invalid rows skipped: {$invalidRows}");
    }

    private function resolveCompanyId(mixed $value, int $defaultCompanyId): int
    {
        if (is_numeric($value) && DB::table('companies')->where('id', (int) $value)->exists()) {
            return (int) $value;
        }

        $name = trim((string) ($value ?? ''));
        if ($name !== '') {
            $companyId = DB::table('companies')->where('name', $name)->value('id');
            if ($companyId) {
                return (int) $companyId;
            }
        }

        return $defaultCompanyId;
    }

    private function resolveBranchId(mixed $value, int $defaultBranchId): int
    {
        if (is_numeric($value) && DB::table('branches')->where('id', (int) $value)->exists()) {
            return (int) $value;
        }

        $name = trim((string) ($value ?? ''));
        if ($name !== '') {
            $branchId = DB::table('branches')->where('name', $name)->value('id');
            if ($branchId) {
                return (int) $branchId;
            }
        }

        return $defaultBranchId;
    }

    private function getSystemUserId(): ?int
    {
        $existingUserId = DB::table('users')->value('id');
        if ($existingUserId) {
            return (int) $existingUserId;
        }

        return null;
    }

    private function resolveDepartmentId(array $record, int $companyId, int $branchId, ?int $systemUserId): ?int
    {
        if (!empty($record['department_id']) && is_numeric($record['department_id'])) {
            $departmentId = (int) $record['department_id'];
            if (DB::table('departments')->where('id', $departmentId)->exists()) {
                return $departmentId;
            }
        }

        $departmentName = trim((string) ($record['department_name'] ?? ''));
        if ($departmentName === '') {
            $departmentName = trim((string) ($record['department_id'] ?? ''));
        }
        if ($departmentName === '') {
            return null;
        }

        $existingDepartment = DB::table('departments')
            ->where('dept_name', $departmentName)
            ->where('branch_id', $branchId)
            ->first();

        if ($existingDepartment) {
            return (int) $existingDepartment->id;
        }

        return (int) DB::table('departments')->insertGetId([
            'dept_name' => $departmentName,
            'slug' => 'dept-' . substr(md5($departmentName . '|' . $branchId), 0, 12),
            'address' => '-',
            'phone' => null,
            'is_active' => 1,
            'dept_head_id' => null,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'created_by' => $systemUserId,
            'updated_by' => $systemUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveRoleId(mixed $value, ?int $systemUserId): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $name = trim((string) $value);
            $existingByName = DB::table('roles')->where('name', $name)->value('id');
            if ($existingByName) {
                return (int) $existingByName;
            }
            $existingBySlug = DB::table('roles')->where('slug', Str::slug($name))->value('id');
            if ($existingBySlug) {
                return (int) $existingBySlug;
            }
            return null;
        }

        $roleId = (int) $value;
        if (DB::table('roles')->where('id', $roleId)->exists()) {
            return $roleId;
        }

        DB::table('roles')->insert([
            'id' => $roleId,
            'name' => $roleId === 1 ? 'admin' : "role_{$roleId}",
            'slug' => $roleId === 1 ? 'admin' : "role-{$roleId}",
            'is_active' => 1,
            'backend_login_authorize' => $roleId === 1 ? 1 : 0,
            'created_by' => $systemUserId,
            'updated_by' => $systemUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $roleId;
    }

    private function resolveDefaultRoleId(?int $systemUserId): ?int
    {
        $envRoleId = env('STAFF_SEED_ROLE_ID');
        if (is_numeric($envRoleId) && DB::table('roles')->where('id', (int) $envRoleId)->exists()) {
            return (int) $envRoleId;
        }

        $existingRoleId = DB::table('roles')
            ->whereIn('name', ['employee', 'staff', 'user'])
            ->value('id');
        if ($existingRoleId) {
            return (int) $existingRoleId;
        }

        $roleId = DB::table('roles')->max('id');
        $roleId = $roleId ? ((int) $roleId + 1) : 2;

        DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'employee',
            'slug' => 'employee',
            'is_active' => 1,
            'backend_login_authorize' => 0,
            'created_by' => $systemUserId,
            'updated_by' => $systemUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $roleId;
    }

    private function resolvePostId(mixed $value, ?int $departmentId, int $branchId): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $postName = trim((string) $value);
            if ($postName === '') {
                return null;
            }

            $query = DB::table('posts')
                ->where('post_name', $postName)
                ->where('branch_id', $branchId);
            if ($departmentId) {
                $query->where('dept_id', $departmentId);
            }
            $existingPostId = $query->value('id');
            if ($existingPostId) {
                return (int) $existingPostId;
            }
            return null;
        }

        $postId = (int) $value;
        if (DB::table('posts')->where('id', $postId)->exists()) {
            return $postId;
        }

        if (!$departmentId) {
            $departmentId = DB::table('departments')->where('branch_id', $branchId)->value('id');
        }
        if (!$departmentId) {
            return null;
        }

        DB::table('posts')->insert([
            'id' => $postId,
            'post_name' => "Post {$postId}",
            'is_active' => 1,
            'dept_id' => $departmentId,
            'branch_id' => $branchId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $postId;
    }

    private function resolveOfficeTimeId(mixed $value, int $companyId, int $branchId, ?int $systemUserId): ?int
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }

        $officeTimeId = (int) $value;
        if (DB::table('office_times')->where('id', $officeTimeId)->exists()) {
            return $officeTimeId;
        }

        DB::table('office_times')->insert([
            'id' => $officeTimeId,
            'opening_time' => '08:00:00',
            'closing_time' => '17:00:00',
            'shift' => "Shift {$officeTimeId}",
            'category' => 'general',
            'holiday_count' => 1,
            'description' => null,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'is_active' => 1,
            'created_by' => $systemUserId,
            'updated_by' => $systemUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $officeTimeId;
    }

    private function resolveSupervisorId(mixed $value): ?int
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $userId = (int) $raw;
            if (DB::table('users')->where('id', $userId)->exists()) {
                return $userId;
            }
        }

        $byCode = DB::table('users')->where('employee_code', $raw)->value('id');
        if ($byCode) {
            return (int) $byCode;
        }

        $byUsername = DB::table('users')->where('username', $raw)->value('id');
        if ($byUsername) {
            return (int) $byUsername;
        }

        return null;
    }

    private function normalizePhone(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($value ?? ''));
        return $digits !== '' ? substr($digits, 0, 20) : null;
    }

    private function normalizeGender(mixed $value): string
    {
        $v = strtolower(trim((string) ($value ?? '')));
        if (in_array($v, ['ប្រុស', 'male', 'm'], true)) {
            return 'male';
        }
        if (in_array($v, ['ស្រី', 'female', 'f'], true)) {
            return 'female';
        }
        return in_array($v, ['male', 'female', 'others'], true) ? $v : 'male';
    }

    private function normalizeMaritalStatus(mixed $value): string
    {
        $v = strtolower(trim((string) ($value ?? '')));
        return in_array($v, ['single', 'married'], true) ? $v : 'single';
    }

    private function normalizeEmploymentType(mixed $value): string
    {
        $v = strtolower(trim((string) ($value ?? '')));
        return in_array($v, ['contract', 'permanent', 'temporary'], true) ? $v : 'temporary';
    }

    private function normalizeStatus(mixed $value): string
    {
        $v = strtolower(trim((string) ($value ?? '')));
        if (in_array($v, ['verified', 'pending', 'rejected', 'suspended'], true)) {
            return $v;
        }
        if (in_array($v, ['1', 'true', 'active'], true)) {
            return 'verified';
        }
        if (in_array($v, ['0', 'false'], true)) {
            return 'pending';
        }
        return 'verified';
    }

    private function normalizeDate(mixed $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        $formats = [
            'Y-m-d',
            'm/d/Y',
            'm/d/y',
            'd/m/Y',
            'd-M-y',
            'j-M-y',
            'd-M-Y',
            'j-M-Y',
        ];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $raw);
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }
}
