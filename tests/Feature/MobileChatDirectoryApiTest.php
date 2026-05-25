<?php

namespace Tests\Feature;

use App\Http\Middleware\SPAuthGateMW;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MobileChatDirectoryApiTest extends TestCase
{
    protected function setUp(): void
    {
        $this->prepareTestDatabaseFile();
        parent::setUp();
        $this->withoutExceptionHandling();

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--force' => true,
        ]);

        $this->createMobileChatSupportTables();
        $this->extendUsersTableForMobileChat();

        DB::table('permission_roles')->delete();
        DB::table('permissions')->delete();
        DB::table('admins')->delete();
        DB::table('general_settings')->delete();
        DB::table('users')->delete();
        DB::table('roles')->delete();
        DB::table('posts')->delete();
        DB::table('departments')->delete();
        DB::table('branches')->delete();
        DB::table('companies')->delete();
    }

    private function prepareTestDatabaseFile(): void
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
    }

    public function test_team_sheet_includes_employees_and_admins_with_admin_identity_fields(): void
    {
        $role = $this->makeRole('employee');
        $this->grantPermission($role, 'list_team_sheet');

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Main Company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branchId = DB::table('branches')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $departmentId = DB::table('departments')->insertGetId([
            'branch_id' => $branchId,
            'dept_name' => 'HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = DB::table('posts')->insertGetId([
            'post_name' => 'Officer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requester = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
        ]);

        $employee = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'username' => 'normal.user',
        ]);

        DB::table('admins')->insert([
            'name' => 'Admin User',
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->withMiddleware([SPAuthGateMW::class])
            ->getJson('/api/users/company/team-sheet');

        $response->assertOk();
        $employees = $response->json('data.companyDetail.employee');

        $this->assertIsArray($employees);
        $this->assertCount(3, $employees);

        $normalEntry = collect($employees)->firstWhere('username', 'normal.user');
        $adminEntry = collect($employees)->firstWhere('username', 'admin.user');

        $this->assertNotNull($normalEntry);
        $this->assertSame('0', $normalEntry['is_admin']);
        $this->assertSame('employee', $normalEntry['role']);
        $this->assertSame('employee', $normalEntry['user_type']);
        $this->assertSame('1', $normalEntry['online_status']);

        $this->assertNotNull($adminEntry);
        $this->assertSame('1', $adminEntry['is_admin']);
        $this->assertSame('1', $adminEntry['admin']);
        $this->assertSame('admin', $adminEntry['role']);
        $this->assertSame('admin', $adminEntry['user_type']);
        $this->assertSame('Administration', $adminEntry['department']);
        $this->assertSame('Admin', $adminEntry['post']);
        $this->assertNotEmpty($adminEntry['conversation_id']);
    }

    public function test_chat_contacts_returns_all_employees_plus_admins(): void
    {
        $role = $this->makeRole('employee');

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Main Company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branchId = DB::table('branches')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $departmentId = DB::table('departments')->insertGetId([
            'branch_id' => $branchId,
            'dept_name' => 'HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = DB::table('posts')->insertGetId([
            'post_name' => 'Officer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('general_settings')->insert([
            'key' => 'mobile_chat_scope',
            'name' => 'Mobile Chat Scope',
            'type' => 'configuration',
            'value' => 'all_employees',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requester = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'username' => 'requester.user',
        ]);

        $onlineEmployee = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'name' => 'Online User',
            'email' => 'online@example.com',
            'username' => 'online.user',
        ]);

        DB::table('admins')->insert([
            'name' => 'Admin User',
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admins')->insert([
            'name' => 'Admin User 2',
            'username' => 'admin.user2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/contacts');

        $response->assertOk();
        $contacts = $response->json('data.contacts');

        $this->assertIsArray($contacts);
        $this->assertCount(3, $contacts);

        $employeeEntry = collect($contacts)->firstWhere('username', 'online.user');
        $adminEntry = collect($contacts)->firstWhere('username', 'admin.user');
        $secondAdminEntry = collect($contacts)->firstWhere('username', 'admin.user2');

        $this->assertNotNull($employeeEntry);
        $this->assertSame('0', $employeeEntry['is_admin']);
        $this->assertSame('1', $employeeEntry['online_status']);
        $this->assertSame('1', $employeeEntry['online']);
        $this->assertTrue($employeeEntry['is_online']);

        $this->assertNotNull($adminEntry);
        $this->assertSame('1', $adminEntry['is_admin']);
        $this->assertSame('admin', $adminEntry['role']);
        $this->assertSame('admin', $adminEntry['user_type']);
        $this->assertSame('admin', $adminEntry['directory_type']);
        $this->assertSame('0', $adminEntry['online']);
        $this->assertFalse($adminEntry['is_online']);
        $this->assertNotEmpty($adminEntry['conversation_id']);

        $this->assertNotNull($secondAdminEntry);
        $this->assertNotEmpty($secondAdminEntry['conversation_id']);
        $this->assertNotSame($adminEntry['conversation_id'], $secondAdminEntry['conversation_id']);

        $onlineContacts = $response->json('data.online_contacts');
        $this->assertIsArray($onlineContacts);
        $this->assertCount(1, $onlineContacts);
        $this->assertSame('online.user', $onlineContacts[0]['username']);
    }

    public function test_admin_messages_are_filtered_by_external_conversation_id(): void
    {
        $role = $this->makeRole('employee');

        $requester = $this->makeUser($role, [
            'status' => 'verified',
            'is_active' => 1,
            'username' => 'employee.15',
        ]);

        $adminOneId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer KY',
            'username' => 'admin.ky',
            'email' => 'admin.ky@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminTwoId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer VIP',
            'username' => 'admin.vip',
            'email' => 'admin.vip@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conversationOneId = DB::table('chat_conversations')->insertGetId([
            'user_id' => $requester->id,
            'admin_id' => $adminOneId,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conversationTwoId = DB::table('chat_conversations')->insertGetId([
            'user_id' => $requester->id,
            'admin_id' => $adminTwoId,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('chat_messages')->insert([
            [
                'conversation_id' => $conversationOneId,
                'sender_type' => 'admin',
                'sender_id' => $adminOneId,
                'message_type' => 'text',
                'message' => 'hello from ky',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conversation_id' => $conversationTwoId,
                'sender_type' => 'admin',
                'sender_id' => $adminTwoId,
                'message_type' => 'text',
                'message' => 'hello from vip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/admin/messages?conversation_id=employee_admin_' . $requester->id . '_' . $adminOneId . '&admin_id=' . $adminOneId);

        $response->assertOk();
        $messages = $response->json('data.messages');

        $this->assertCount(1, $messages);
        $this->assertSame('employee_admin_' . $requester->id . '_' . $adminOneId, $messages[0]['conversation_id']);
        $this->assertSame((string) $conversationOneId, $messages[0]['internal_conversation_id']);
        $this->assertSame($adminOneId, $messages[0]['admin_id']);
        $this->assertSame('admin.ky', $messages[0]['admin_username']);
        $this->assertSame('admin', $messages[0]['sender']);
        $this->assertSame('hello from ky', $messages[0]['message']);
        $this->assertSame('text', $messages[0]['message_type']);
        $this->assertNotEmpty($messages[0]['created_at']);
    }

    public function test_admin_message_post_accepts_public_and_internal_conversation_identifiers(): void
    {
        $role = $this->makeRole('employee');

        $requester = $this->makeUser($role, [
            'status' => 'verified',
            'is_active' => 1,
            'username' => 'employee.15',
        ]);

        $adminId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer KY',
            'username' => 'admin.ky',
            'email' => 'admin.ky@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conversationId = DB::table('chat_conversations')->insertGetId([
            'user_id' => $requester->id,
            'admin_id' => $adminId,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->postJson('/api/employee/chat/admin/messages', [
            'message' => 'hello from mobile',
            'conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminId,
            'internal_conversation_id' => (string) $conversationId,
            'admin_id' => $adminId,
            'admin_username' => 'admin.ky',
            'message_type' => 'text',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.conversation_id', 'employee_admin_' . $requester->id . '_' . $adminId);
        $response->assertJsonPath('data.internal_conversation_id', (string) $conversationId);
        $response->assertJsonPath('data.admin_id', $adminId);
        $response->assertJsonPath('data.admin_username', 'admin.ky');

        $messages = $response->json('data.messages');
        $this->assertNotEmpty($messages);
        $this->assertSame('employee_admin_' . $requester->id . '_' . $adminId, $messages[0]['conversation_id']);
        $this->assertSame((string) $conversationId, $messages[0]['internal_conversation_id']);
        $this->assertSame($adminId, $messages[0]['admin_id']);
        $this->assertSame('admin.ky', $messages[0]['admin_username']);
        $this->assertSame('user', $messages[0]['sender']);
        $this->assertSame('hello from mobile', $messages[0]['message']);
        $this->assertSame('text', $messages[0]['message_type']);
        $this->assertNotEmpty($messages[0]['created_at']);

        $storedMessage = DB::table('chat_messages')
            ->where('conversation_id', $conversationId)
            ->latest('id')
            ->first();

        $this->assertNotNull($storedMessage);
        $this->assertSame('hello from mobile', $storedMessage->message);
        $this->assertSame('text', $storedMessage->message_type);
    }

    public function test_admin_media_message_returns_resolved_media_fields(): void
    {
        $role = $this->makeRole('employee');

        $requester = $this->makeUser($role, [
            'status' => 'verified',
            'is_active' => 1,
            'username' => 'employee.media',
        ]);

        $adminId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer KY',
            'username' => 'admin.media',
            'email' => 'admin.media@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->postJson('/api/employee/chat/admin/messages', [
            'conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminId,
            'admin_id' => $adminId,
            'admin_username' => 'admin.media',
            'message_type' => 'voice',
            'media_type' => 'voice',
            'media_path' => 'chat/voice/test-message.m4a',
            'duration_seconds' => 12,
            'file_name' => 'test-message.m4a',
        ]);

        $response->assertOk();
        $message = $response->json('data.messages.0');

        $this->assertSame('voice', $message['message_type']);
        $this->assertSame('chat/voice/test-message.m4a', $message['media_path']);
        $this->assertStringContainsString('/storage/chat/voice/test-message.m4a', $message['media_url']);
        $this->assertSame(12, $message['duration_seconds']);
        $this->assertSame('test-message.m4a', $message['file_name']);
    }

    public function test_admin_voice_message_repairs_missing_media_url_from_media_path(): void
    {
        [$requester, $adminId, $conversationId] = $this->createAdminConversationFixture('employee.voice.path', 'admin.voice.path');

        DB::table('chat_messages')->insert([
            'conversation_id' => $conversationId,
            'sender_type' => 'user',
            'sender_id' => $requester->id,
            'message_type' => 'voice',
            'message' => null,
            'media_url' => null,
            'meta' => json_encode([
                'media_path' => 'chat/voice/legacy-path-only.m4a',
                'duration_seconds' => 8,
                'file_name' => 'legacy-path-only.m4a',
                'admin_id' => $adminId,
                'admin_username' => 'admin.voice.path',
                'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminId,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/admin/messages?conversation_id=employee_admin_' . $requester->id . '_' . $adminId . '&admin_id=' . $adminId);

        $response->assertOk();
        $message = $response->json('data.messages.0');

        $this->assertSame('voice', $message['message_type']);
        $this->assertSame('chat/voice/legacy-path-only.m4a', $message['media_path']);
        $this->assertSame('/storage/chat/voice/legacy-path-only.m4a', $message['media_url']);
        $this->assertSame(8, $message['duration_seconds']);
        $this->assertSame('legacy-path-only.m4a', $message['file_name']);
    }

    public function test_admin_voice_message_repairs_broken_media_url_using_media_path(): void
    {
        [$requester, $adminId, $conversationId] = $this->createAdminConversationFixture('employee.voice.broken', 'admin.voice.broken');

        DB::table('chat_messages')->insert([
            'conversation_id' => $conversationId,
            'sender_type' => 'admin',
            'sender_id' => $adminId,
            'message_type' => 'voice',
            'message' => null,
            'media_url' => '/chat/voice/bad-root-url.m4a',
            'meta' => json_encode([
                'media_path' => 'chat/voice/bad-root-url.m4a',
                'duration_seconds' => 15,
                'file_name' => 'bad-root-url.m4a',
                'admin_id' => $adminId,
                'admin_username' => 'admin.voice.broken',
                'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminId,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/admin/messages?conversation_id=employee_admin_' . $requester->id . '_' . $adminId . '&admin_id=' . $adminId);

        $response->assertOk();
        $message = $response->json('data.messages.0');

        $this->assertSame('voice', $message['message_type']);
        $this->assertSame('chat/voice/bad-root-url.m4a', $message['media_path']);
        $this->assertSame('/storage/chat/voice/bad-root-url.m4a', $message['media_url']);
    }

    public function test_admin_image_and_file_messages_normalize_legacy_media_urls(): void
    {
        [$requester, $adminId, $conversationId] = $this->createAdminConversationFixture('employee.media.legacy', 'admin.media.legacy');

        DB::table('chat_messages')->insert([
            [
                'conversation_id' => $conversationId,
                'sender_type' => 'admin',
                'sender_id' => $adminId,
                'message_type' => 'image',
                'message' => null,
                'media_url' => 'https://example.test/chat/images/legacy-image.png',
                'meta' => json_encode([
                    'media_path' => 'chat/images/legacy-image.png',
                    'admin_id' => $adminId,
                    'admin_username' => 'admin.media.legacy',
                    'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminId,
                ]),
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'conversation_id' => $conversationId,
                'sender_type' => 'user',
                'sender_id' => $requester->id,
                'message_type' => 'file',
                'message' => null,
                'media_url' => '/chat/files/legacy-doc.pdf',
                'meta' => json_encode([
                    'media_path' => 'chat/files/legacy-doc.pdf',
                    'file_name' => 'legacy-doc.pdf',
                    'admin_id' => $adminId,
                    'admin_username' => 'admin.media.legacy',
                    'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/admin/messages?conversation_id=employee_admin_' . $requester->id . '_' . $adminId . '&admin_id=' . $adminId);

        $response->assertOk();
        $messages = collect($response->json('data.messages'))->keyBy('message_type');

        $this->assertSame('chat/images/legacy-image.png', $messages['image']['media_path']);
        $this->assertSame('/storage/chat/images/legacy-image.png', $messages['image']['media_url']);
        $this->assertSame('chat/files/legacy-doc.pdf', $messages['file']['media_path']);
        $this->assertSame('/storage/chat/files/legacy-doc.pdf', $messages['file']['media_url']);
        $this->assertSame('legacy-doc.pdf', $messages['file']['file_name']);
    }

    public function test_chat_media_upload_returns_canonical_storage_path_and_url(): void
    {
        Storage::fake('public');

        $role = $this->makeRole('employee');
        $requester = $this->makeUser($role, [
            'status' => 'verified',
            'is_active' => 1,
            'username' => 'employee.upload.media',
        ]);

        Passport::actingAs($requester);

        $voiceResponse = $this->postJson('/api/employee/chat/media-upload', [
            'type' => 'voice',
            'file' => UploadedFile::fake()->create('voice-note.m4a', 64, 'audio/mp4'),
        ]);

        $voiceResponse->assertOk();
        $voicePath = $voiceResponse->json('data.path');
        $voiceUrl = $voiceResponse->json('data.url');

        $this->assertStringStartsWith('chat/voice/', $voicePath);
        $this->assertStringEndsWith('.m4a', $voicePath);
        $this->assertSame('/storage/' . $voicePath, $voiceUrl);

        $imageResponse = $this->postJson('/api/employee/chat/media-upload', [
            'type' => 'image',
            'file' => UploadedFile::fake()->image('chat-image.png', 120, 90),
        ]);

        $imageResponse->assertOk();
        $imagePath = $imageResponse->json('data.path');
        $imageUrl = $imageResponse->json('data.url');

        $this->assertStringStartsWith('chat/images/', $imagePath);
        $this->assertSame('/storage/' . $imagePath, $imageUrl);
    }

    public function test_admin_messages_stay_separate_even_when_messages_share_one_internal_conversation(): void
    {
        $role = $this->makeRole('employee');

        $requester = $this->makeUser($role, [
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'username' => 'employee.shared',
        ]);

        $adminOneId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer KY',
            'username' => 'admin.ky',
            'email' => 'admin.ky.shared@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminTwoId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer VIP',
            'username' => 'admin.vip',
            'email' => 'admin.vip.shared@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sharedConversationId = DB::table('chat_conversations')->insertGetId([
            'user_id' => $requester->id,
            'admin_id' => null,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('chat_messages')->insert([
            [
                'conversation_id' => $sharedConversationId,
                'sender_type' => 'admin',
                'sender_id' => $adminOneId,
                'message_type' => 'text',
                'message' => 'hello from ky',
                'meta' => json_encode([
                    'admin_id' => $adminOneId,
                    'admin_username' => 'admin.ky',
                    'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminOneId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conversation_id' => $sharedConversationId,
                'sender_type' => 'user',
                'sender_id' => $requester->id,
                'message_type' => 'text',
                'message' => 'reply to ky',
                'meta' => json_encode([
                    'admin_id' => $adminOneId,
                    'admin_username' => 'admin.ky',
                    'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminOneId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conversation_id' => $sharedConversationId,
                'sender_type' => 'admin',
                'sender_id' => $adminTwoId,
                'message_type' => 'text',
                'message' => 'hello from vip',
                'meta' => json_encode([
                    'admin_id' => $adminTwoId,
                    'admin_username' => 'admin.vip',
                    'external_conversation_id' => 'employee_admin_' . $requester->id . '_' . $adminTwoId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/admin/messages?conversation_id=employee_admin_' . $requester->id . '_' . $adminOneId . '&admin_id=' . $adminOneId . '&internal_conversation_id=' . $sharedConversationId);

        $response->assertOk();
        $messages = $response->json('data.messages');

        $this->assertCount(2, $messages);
        $this->assertSame('employee_admin_' . $requester->id . '_' . $adminOneId, $messages[0]['conversation_id']);
        $this->assertSame((string) $sharedConversationId, $messages[0]['internal_conversation_id']);
        $this->assertSame($adminOneId, $messages[0]['admin_id']);
        $this->assertSame('admin.ky', $messages[0]['admin_username']);
        $this->assertSame('hello from ky', $messages[0]['message']);
        $this->assertSame('reply to ky', $messages[1]['message']);
    }

    private function makeRole(string $slug): Role
    {
        return Role::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => 1,
        ]);
    }

    private function grantPermission(Role $role, string $permissionKey): void
    {
        $permission = Permission::create([
            'name' => ucfirst(str_replace('_', ' ', $permissionKey)),
            'permission_key' => $permissionKey,
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
            'phone' => '012345678',
            'status' => 'verified',
            'is_active' => 1,
            'user_type' => 'employee',
            'online_status' => 0,
        ], $overrides));
    }

    private function createAdminConversationFixture(string $employeeUsername, string $adminUsername): array
    {
        $role = $this->makeRole('employee');
        $requester = $this->makeUser($role, [
            'status' => 'verified',
            'is_active' => 1,
            'username' => $employeeUsername,
        ]);

        $adminId = DB::table('admins')->insertGetId([
            'name' => 'Administration Officer',
            'username' => $adminUsername,
            'email' => $adminUsername . '@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conversationId = DB::table('chat_conversations')->insertGetId([
            'user_id' => $requester->id,
            'admin_id' => $adminId,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$requester, $adminId, $conversationId];
    }

    private function createMobileChatSupportTables(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('dept_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('post_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('avatar')->nullable();
                $table->boolean('is_active')->default(1);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('general_settings')) {
            Schema::create('general_settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('key')->unique();
                $table->string('type')->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'admin_id'], 'chat_conversations_user_admin_unique');
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->string('sender_type', 20);
                $table->unsignedBigInteger('sender_id');
                $table->string('message_type', 20)->default('text');
                $table->text('message')->nullable();
                $table->string('media_url')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('map_url')->nullable();
                $table->json('meta')->nullable();
                $table->boolean('is_read_by_admin')->default(false);
                $table->boolean('is_read_by_user')->default(false);
                $table->timestamps();
            });
        }
    }

    private function extendUsersTableForMobileChat(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'post_id')) {
                $table->unsignedBigInteger('post_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(1);
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->nullable();
            }
            if (!Schema::hasColumn('users', 'online_status')) {
                $table->boolean('online_status')->default(0);
            }
        });
    }
}
