<?php

namespace Tests\Feature;

use App\Models\CompanyDocument;
use App\Models\CompanyDocumentCategory;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CompanyDocumentApiTest extends TestCase
{
    protected function setUp(): void
    {
        $this->prepareTestDatabaseFile();
        parent::setUp();

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--force' => true,
        ]);

        $this->createCompanyDocumentSchema();
    }

    protected function tearDown(): void
    {
        // Clean up any test uploads
        $testUploadDir = public_path(CompanyDocument::UPLOAD_PATH);
        if (File::isDirectory($testUploadDir)) {
            File::deleteDirectory($testUploadDir);
        }
        parent::tearDown();
    }

    public function test_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/company-documents')->assertStatus(401);
        $this->getJson('/api/v1/company-documents/meta')->assertStatus(401);
        $this->getJson('/api/v1/company-documents/alerts')->assertStatus(401);
    }

    public function test_meta_endpoint_returns_categories_and_sub_types(): void
    {
        $user = $this->createAuthenticatedUser();
        Passport::actingAs($user);

        $response = $this->getJson('/api/v1/company-documents/meta');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.currencies.0', 'USD')
            ->assertJsonPath('data.currencies.1', 'KHR')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'categories',
                    'sub_types',
                    'branches',
                    'currencies',
                    'confidentiality_levels',
                ],
            ]);
    }

    public function test_can_upload_and_create_company_document_via_api(): void
    {
        $user = $this->createAuthenticatedUser();
        Passport::actingAs($user);

        $fakeFile = UploadedFile::fake()->create('sample_tax.pdf', 120, 'application/pdf');

        $payload = [
            'category_code' => 'tax',
            'title' => 'Annual Patent Tax 2026',
            'doc_number' => 'TAX-2026-001',
            'holder_name' => 'Kneayerng Digital',
            'issuing_authority' => 'General Department of Taxation',
            'issue_date' => '2026-01-15',
            'expiry_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
            'alert_before_days' => 30,
            'cost_amount' => 150.00,
            'currency' => 'USD',
            'receipt_number' => 'RCP-88992',
            'confidentiality_level' => 'internal',
            'description' => 'Tax certificate paid at branch HQ',
            'file' => $fakeFile,
        ];

        $response = $this->postJson('/api/v1/company-documents', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.title', 'Annual Patent Tax 2026')
            ->assertJsonPath('data.category_code', 'tax')
            ->assertJsonPath('data.doc_number', 'TAX-2026-001')
            ->assertJsonPath('data.calculated_status', 'expiring_soon')
            ->assertJsonPath('data.is_pdf', true);

        $documentId = $response->json('data.id');
        $this->assertDatabaseHas('company_documents', [
            'id' => $documentId,
            'title' => 'Annual Patent Tax 2026',
            'category_code' => 'tax',
        ]);
    }

    public function test_can_list_and_filter_documents_and_metrics(): void
    {
        $user = $this->createAuthenticatedUser();
        Passport::actingAs($user);

        // Seed 2 documents
        CompanyDocument::create([
            'category_code' => 'tax',
            'title' => 'Tax Monthly Return',
            'currency' => 'USD',
            'cost_amount' => 50,
            'file_path' => 'uploads/test/tax.pdf',
            'expiry_date' => Carbon::today()->addDays(15)->format('Y-m-d'),
            'alert_before_days' => 30,
        ]);

        CompanyDocument::create([
            'category_code' => 'utility',
            'doc_sub_type' => 'electricity',
            'title' => 'EDC Power Bill November',
            'currency' => 'KHR',
            'cost_amount' => 200000,
            'file_path' => 'uploads/test/edc.jpg',
            'expiry_date' => Carbon::today()->subDays(5)->format('Y-m-d'),
            'alert_before_days' => 7,
        ]);

        $response = $this->getJson('/api/v1/company-documents');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('summary.total_count', 2)
            ->assertJsonPath('summary.tax_count', 1)
            ->assertJsonPath('summary.utility_count', 1)
            ->assertJsonPath('summary.expired_count', 1)
            ->assertJsonPath('summary.expiring_soon_count', 1);

        // Filter by category
        $filterResponse = $this->getJson('/api/v1/company-documents?category=utility');
        $filterResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.doc_sub_type', 'electricity');
    }

    public function test_alerts_endpoint_returns_expiring_and_expired(): void
    {
        $user = $this->createAuthenticatedUser();
        Passport::actingAs($user);

        CompanyDocument::create([
            'category_code' => 'contract',
            'title' => 'HQ Office Lease',
            'currency' => 'USD',
            'file_path' => 'uploads/test/lease.pdf',
            'expiry_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            'alert_before_days' => 30,
        ]);

        CompanyDocument::create([
            'category_code' => 'business_license',
            'title' => 'Commercial License',
            'currency' => 'USD',
            'file_path' => 'uploads/test/license.pdf',
            'expiry_date' => Carbon::today()->subDays(2)->format('Y-m-d'),
            'alert_before_days' => 30,
        ]);

        $response = $this->getJson('/api/v1/company-documents/alerts');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.expiring_soon_count', 1)
            ->assertJsonPath('data.expired_count', 1)
            ->assertJsonPath('data.expiring_soon.0.title', 'HQ Office Lease')
            ->assertJsonPath('data.expired.0.title', 'Commercial License');
    }

    public function test_can_show_update_and_delete_document(): void
    {
        $user = $this->createAuthenticatedUser();
        Passport::actingAs($user);

        $doc = CompanyDocument::create([
            'category_code' => 'tax',
            'title' => 'Original Title',
            'currency' => 'USD',
            'file_path' => 'uploads/test/doc.pdf',
        ]);

        // Show
        $showRes = $this->getJson("/api/v1/company-documents/{$doc->id}");
        $showRes->assertOk()->assertJsonPath('data.title', 'Original Title');

        // Update
        $updateRes = $this->postJson("/api/v1/company-documents/{$doc->id}", [
            'title' => 'Updated Title',
            'cost_amount' => 99.50,
            'currency' => 'USD',
        ]);
        $updateRes->assertOk()->assertJsonPath('data.title', 'Updated Title');
        $this->assertDatabaseHas('company_documents', ['id' => $doc->id, 'title' => 'Updated Title']);

        // Delete
        $delRes = $this->deleteJson("/api/v1/company-documents/{$doc->id}");
        $delRes->assertOk();
        $this->assertDatabaseMissing('company_documents', ['id' => $doc->id]);
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'is_active' => 1,
        ]);

        return User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'role_id' => $role->id,
        ]);
    }

    private function createCompanyDocumentSchema(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Test Company');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('HQ Branch');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_document_categories')) {
            Schema::create('company_document_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name_km', 255);
                $table->string('name_en', 255);
                $table->string('icon', 50)->default('file-text');
                $table->string('color', 20)->default('#0f766e');
                $table->integer('order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('company_document_categories')->insert([
                ['code' => 'master', 'name_km' => 'ឯកសារមេ', 'name_en' => 'Master Identity', 'icon' => 'award', 'color' => '#1d4ed8', 'order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'tax', 'name_km' => 'ពន្ធដារ', 'name_en' => 'Taxation', 'icon' => 'dollar-sign', 'color' => '#b45309', 'order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'business_license', 'name_km' => 'អាជ្ញាបណ្ណអាជីវកម្ម', 'name_en' => 'Business Licenses', 'icon' => 'briefcase', 'color' => '#0f766e', 'order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'nssf', 'name_km' => 'ប.ស.ស & ការងារ', 'name_en' => 'NSSF & Labor', 'icon' => 'shield', 'color' => '#dc2626', 'order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'contract', 'name_km' => 'កិច្ចសន្យា', 'name_en' => 'Contracts', 'icon' => 'file-text', 'color' => '#7c3aed', 'order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'utility', 'name_km' => 'ថ្លៃជួល & ទឹកភ្លើង', 'name_en' => 'Utilities & Rent', 'icon' => 'zap', 'color' => '#059669', 'order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('company_documents')) {
            Schema::create('company_documents', function (Blueprint $table) {
                $table->id();
                $table->string('category_code', 50)->index();
                $table->string('doc_sub_type', 50)->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('doc_number', 100)->nullable();
                $table->string('title', 255);
                $table->string('holder_name', 255)->nullable();
                $table->string('issuing_authority', 255)->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->integer('alert_before_days')->default(30);
                $table->decimal('cost_amount', 12, 2)->nullable();
                $table->string('currency', 10)->default('USD');
                $table->string('receipt_number', 100)->nullable();
                $table->string('file_path', 500)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->string('file_type', 50)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('confidentiality_level', 50)->default('internal');
                $table->string('status', 30)->default('valid');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
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
}
