<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Imports\RepairCatalogImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class RepairCatalogTransferController extends Controller
{
    private function catalog(string $entity): array
    {
        abort_unless(isset(RepairCatalogImport::CATALOGS[$entity]), 404);
        $catalog = RepairCatalogImport::CATALOGS[$entity];
        abort_unless(Gate::allows('repair_price.view') && Gate::allows($catalog[1]), 403);
        return $catalog;
    }

    public function import(Request $request, string $entity)
    {
        $this->catalog($entity);
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', 'update_existing' => 'nullable|boolean']);
        $import = new RepairCatalogImport($entity, $request->boolean('update_existing'));
        try {
            DB::transaction(function () use ($import, $request) {
                Excel::import($import, $request->file('file'));
                if ($import->inserted + $import->updated + $import->skipped === 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['file' => __('repair.catalog_empty_file')]);
                }
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.repair-price.index', ['active_tab' => $entity])->with('danger', implode(' ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('admin.repair-price.index', ['active_tab' => $entity])->with('danger', __('repair.catalog_import_failed'));
        }
        return redirect()->route('admin.repair-price.index', ['active_tab' => $entity])->with('success', __('repair.catalog_import_success', [
            'inserted' => $import->inserted, 'updated' => $import->updated, 'skipped' => $import->skipped,
        ]));
    }

    public function export(Request $request, string $entity)
    {
        [$model, , $columns] = $this->catalog($entity);
        $template = $request->boolean('template');
        return response()->streamDownload(function () use ($model, $columns, $entity, $template) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);
            if (!$template) {
                $relations = match ($entity) {
                    'devices' => ['brand', 'deviceType', 'series', 'categories'],
                    'series' => ['brand', 'deviceType'], 'services' => ['category'], default => [],
                };
                foreach ($model::with($relations)->orderBy('id')->lazyById(500) as $record) {
                    $row = [];
                    foreach ($columns as $column) {
                        $value = match ($column) {
                            'brand_slug' => $record->brand?->slug,
                            'type_slug' => $record->deviceType?->slug,
                            'series_slug' => $record->series?->slug,
                            'category_slug' => $record->category?->slug,
                            'category_slugs' => $record->categories->pluck('slug')->sort()->implode('|'),
                            'status' => $record->status ? '1' : '0', default => $record->$column,
                        };
                        $value = (string) $value;
                        if (preg_match('/^[=+\-@\t\r]/', ltrim($value, ' '))) $value = "'".$value;
                        $row[] = $value;
                    }
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        }, 'repair-'.$entity.($template ? '-template' : '').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
