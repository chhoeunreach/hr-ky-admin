<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'sell_out_report_lines_primary_identifier_unique',
            'sell_out_report_lines_imei_unique',
            'sell_out_report_lines_serial_number_unique',
        ] as $indexName) {
            if ($this->indexExists('sell_out_report_lines', $indexName)) {
                Schema::table('sell_out_report_lines', function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            }
        }

        DB::table('sell_out_report_lines')
            ->whereNull('product_name')
            ->update(['product_name' => '']);

        DB::table('sell_out_report_lines')
            ->whereNull('unit_price')
            ->update(['unit_price' => 0]);

        DB::statement('update sell_out_report_lines set subtotal = qty * unit_price where subtotal is null');

        Schema::table('sell_out_report_lines', function (Blueprint $table) {
            $table->string('product_name')->nullable(false)->change();
            $table->integer('qty')->default(1)->change();
            $table->decimal('unit_price', 12, 2)->nullable(false)->change();
            $table->decimal('subtotal', 12, 2)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sell_out_report_lines', function (Blueprint $table) {
            $table->string('product_name')->nullable()->change();
            $table->decimal('qty', 12, 2)->default(1)->change();
            $table->decimal('unit_price', 15, 2)->nullable()->change();
            $table->decimal('subtotal', 15, 2)->nullable()->change();
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();

            return array_key_exists($index, $schemaManager->listTableIndexes($table));
        } catch (Throwable) {
            return false;
        }
    }
};
