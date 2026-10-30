<?php

use App\Models\MonthlyPayrollRecord;
use App\Services\Payroll\MonthlyPayrollLoanService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $loans = app(MonthlyPayrollLoanService::class);
        MonthlyPayrollRecord::query()->chunkById(100, function ($records) use ($loans) {
            foreach ($records as $record) DB::transaction(function () use ($record, $loans) {
                $record = MonthlyPayrollRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $options = $record->options;
                if (($options['loan_amount_source'] ?? null) === MonthlyPayrollLoanService::SOURCE) return;
                $summary = $record->summary;
                $options['loan_amount_correction'] = [
                    'values' => array_map(fn ($row) => [
                        'exists' => array_key_exists('advance_salary', $row['values']),
                        'value' => $row['values']['advance_salary'] ?? null,
                    ], $record->rows),
                    'summary_exists' => array_key_exists('advance_salary', $summary),
                    'summary_value' => $summary['advance_salary'] ?? null,
                    'source_exists' => array_key_exists('loan_amount_source', $options),
                    'source_value' => $options['loan_amount_source'] ?? null,
                ];
                $rows = $loans->correctedRows($record->month, $record->rows);
                $summary['advance_salary'] = $loans->totalRows($rows);
                $options['loan_amount_source'] = MonthlyPayrollLoanService::SOURCE;
                $record->update(['rows' => $rows, 'summary' => $summary, 'options' => $options]);
            });
        });
    }

    public function down(): void
    {
        MonthlyPayrollRecord::query()->chunkById(100, function ($records) {
            foreach ($records as $record) DB::transaction(function () use ($record) {
                $record = MonthlyPayrollRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $options = $record->options;
                $before = $options['loan_amount_correction'] ?? null;
                if (!$before) return;
                $rows = $record->rows;
                foreach ($before['values'] as $index => $value) {
                    if ($value['exists']) $rows[$index]['values']['advance_salary'] = $value['value'];
                    else unset($rows[$index]['values']['advance_salary']);
                }
                $summary = $record->summary;
                if ($before['summary_exists']) $summary['advance_salary'] = $before['summary_value'];
                else unset($summary['advance_salary']);
                if ($before['source_exists']) $options['loan_amount_source'] = $before['source_value'];
                else unset($options['loan_amount_source']);
                unset($options['loan_amount_correction']);
                $record->update(['rows' => $rows, 'summary' => $summary, 'options' => $options]);
            });
        });
    }
};
