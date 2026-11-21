<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\AuditLogCsvExportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogCsvExportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->string('actor_id')->nullable();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->text('previous_state')->nullable();
            $table->text('new_state')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('occurred_at');
        });
    }

    public function test_csv_escapes_formula_cells_and_encodes_state_json(): void
    {
        DB::table('app_security_audit_logs')->insert([
            'admin_id' => 7, 'actor_type' => 'admin', 'actor_id' => '7', 'action' => '=HYPERLINK("x")',
            'target_type' => 'device', 'target_id' => '2', 'previous_state' => '{"ok":true}',
            'new_state' => '{"ok":false}', 'reason' => '+SUM(1,1)', 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'occurred_at' => '2026-01-01 00:00:00',
        ]);

        $stream = fopen('php://temp', 'w+b');
        $written = app(AuditLogCsvExportService::class)->write(DB::table('app_security_audit_logs'), $stream);
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream)) !== false) $rows[] = $row;
        fclose($stream);

        $this->assertSame(1, $written);
        $this->assertSame('id', $rows[0][0]);
        $this->assertSame("'=HYPERLINK(\"x\")", $rows[1][4]);
        $this->assertSame("'+SUM(1,1)", $rows[1][9]);
        $this->assertSame('{"ok":true}', $rows[1][7]);
    }

    public function test_csv_export_obeys_requested_row_limit(): void
    {
        foreach (['one', 'two', 'three'] as $action) {
            DB::table('app_security_audit_logs')->insert(['action' => $action, 'occurred_at' => '2026-01-01 00:00:00']);
        }
        $stream = fopen('php://temp', 'w+b');
        $written = app(AuditLogCsvExportService::class)->write(DB::table('app_security_audit_logs'), $stream, 2);
        rewind($stream);
        $lines = 0;
        while (fgetcsv($stream) !== false) $lines++;
        fclose($stream);

        $this->assertSame(2, $written);
        $this->assertSame(3, $lines); // header plus two records
    }
}
