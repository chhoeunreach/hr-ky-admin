<?php

namespace App\Modules\AppSecurity\Services;

use Illuminate\Database\Query\Builder;
use InvalidArgumentException;
use RuntimeException;

/** Streams a bounded, spreadsheet-safe CSV without loading the full audit set. */
class AuditLogCsvExportService
{
    public const MAX_ROWS = 100000;
    private const CHUNK_SIZE = 1000;
    private const COLUMNS = [
        'id', 'admin_id', 'actor_type', 'actor_id', 'action', 'target_type', 'target_id',
        'previous_state', 'new_state', 'reason', 'ip_address', 'user_agent', 'occurred_at',
    ];

    /** @param resource $stream */
    public function write(Builder $query, $stream, int $maxRows = self::MAX_ROWS): int
    {
        if (!is_resource($stream)) throw new InvalidArgumentException('A writable CSV stream is required.');
        $maxRows = min(self::MAX_ROWS, max(1, $maxRows));
        if (fputcsv($stream, self::COLUMNS, ',', '"', '\\') === false) {
            throw new RuntimeException('Could not write CSV header.');
        }

        $lastId = 0;
        $written = 0;
        while ($written < $maxRows) {
            $rows = (clone $query)->where('id', '>', $lastId)
                ->orderBy('id')->limit(min(self::CHUNK_SIZE, $maxRows - $written))->get();
            if ($rows->isEmpty()) break;

            foreach ($rows as $row) {
                $values = [];
                foreach (self::COLUMNS as $column) {
                    $value = $row->{$column} ?? '';
                    if (in_array($column, ['previous_state', 'new_state'], true) && $value !== '') {
                        $decoded = json_decode((string) $value, true);
                        $value = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                    }
                    $values[] = $this->spreadsheetSafe((string) $value);
                }
                if (fputcsv($stream, $values, ',', '"', '\\') === false) {
                    throw new RuntimeException('Could not write audit CSV row.');
                }
                $lastId = (int) $row->id;
                $written++;
            }
        }

        return $written;
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[=+@\\-\\t\\r]/', $value) === 1 ? "'".$value : $value;
    }
}
