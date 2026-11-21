<?php

namespace App\Modules\DailyRecords;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

final class ReportSpreadsheet extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder
{
    public function __construct(private Builder $rows, private bool $grouped) {}

    public function query(): Builder
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return self::columns($this->grouped);
    }

    public function map($row): array
    {
        return self::values($row, $this->grouped);
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public static function columns(bool $grouped): array
    {
        return $grouped
            ? ['Group', 'Assigned', 'Approved', 'On-time approval rate', 'Overdue', 'Returned', 'Awaiting review', 'Incidents resolved']
            : ['Type', 'Employee', 'Department', 'Location', 'Title', 'Status', 'Progress %', 'Deadline (ICT)', 'Created (ICT)', 'Updated (ICT)', 'Details', 'Solution', 'Occurred or reporting day (ICT)'];
    }

    public static function values(object $row, bool $grouped): array
    {
        if ($grouped) {
            return [$row->label ?? 'Unspecified', (int) $row->new_assignments, (int) $row->approved,
                $row->approved ? number_format($row->on_time * 100 / $row->approved, 1).'%' : '—',
                (int) $row->overdue, (int) $row->returned, (int) $row->submitted, (int) $row->resolved];
        }
        return [ucfirst($row->kind), $row->employee, $row->department, $row->location, $row->title, $row->status, $row->progress,
            RecordTime::display($row->due_at), RecordTime::display($row->created_at), RecordTime::display($row->updated_at), $row->body, $row->solution, RecordTime::display($row->occurred_at)];
    }
}
