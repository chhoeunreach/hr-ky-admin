<?php

namespace App\Imports;

use App\Models\RepairPrice\{RepairBrand, RepairCategory, RepairDevice, RepairDeviceSeries, RepairDeviceType, RepairService};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\{SkipsEmptyRows, ToCollection, WithHeadingRow};

class RepairCatalogImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public const CATALOGS = [
        'brands' => [RepairBrand::class, 'repair_price.manage_brands', ['name', 'slug', 'logo', 'status']],
        'categories' => [RepairCategory::class, 'repair_price.manage_categories', ['name', 'name_kh', 'slug', 'icon', 'sort_order', 'status']],
        'devices' => [RepairDevice::class, 'repair_price.manage_devices', ['name', 'brand_slug', 'type_slug', 'series_slug', 'model_number', 'image', 'category_slugs', 'status']],
        'services' => [RepairService::class, 'repair_price.manage_services', ['name', 'category_slug', 'name_kh', 'description', 'status']],
        'types' => [RepairDeviceType::class, 'repair_price.manage_devices', ['name', 'slug', 'icon', 'status']],
        'series' => [RepairDeviceSeries::class, 'repair_price.manage_devices', ['name', 'slug', 'brand_slug', 'type_slug', 'status']],
    ];

    public int $inserted = 0;
    public int $updated = 0;
    public int $skipped = 0;

    public function __construct(public string $entity, private bool $updateExisting = false) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                $this->saveRow(collect($row));
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(['file' => __('repair.catalog_row_error', [
                    'row' => $index + 2, 'error' => implode(' ', $e->validator->errors()->all()),
                ])]);
            }
        }
    }

    private function saveRow(Collection $row): void
    {
        [$model, , $columns] = self::CATALOGS[$this->entity];
        $data = $row->only($columns)->map(function ($value) {
            $value = $value === null ? null : trim((string) $value);
            // Undo the spreadsheet formula protection used by our CSV exporter.
            if ($value && preg_match("/^'[=+\\-@]/", $value)) $value = substr($value, 1);
            return $value === '' ? null : $value;
        })->all();
        $rules = [];
        foreach ($columns as $column) $rules[$column] = 'nullable|string|max:255';
        $rules['name'] = 'required|string|max:255';
        $rules['description'] = 'nullable|string|max:10000';
        $rules['status'] = 'nullable|boolean';
        $rules['sort_order'] = 'nullable|integer|min:0';
        $rules['category_slugs'] = 'nullable|string|max:10000';
        foreach (['brand_slug', 'type_slug', 'category_slug'] as $column) {
            if (in_array($column, $columns, true)) $rules[$column] = 'required|string|max:255';
        }
        Validator::make($data, $rules)->validate();
        $data['status'] = isset($data['status']) ? (bool) $data['status'] : true;
        if (in_array('slug', $columns, true)) {
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
            Validator::make($data, ['slug' => 'required|string|max:255'])->validate();
        }
        if ($this->entity === 'categories') $data['sort_order'] = $data['sort_order'] ?? 0;
        $key = isset($data['slug']) ? ['slug' => $data['slug']] : ['name' => $data['name']];
        foreach (['brand_slug' => [RepairBrand::class, 'repair_brand_id'], 'type_slug' => [RepairDeviceType::class, 'repair_device_type_id'],
            'category_slug' => [RepairCategory::class, 'repair_category_id']] as $field => [$related, $foreignKey]) {
            if (!isset($data[$field])) continue;
            $id = $related::where('slug', $data[$field])->value('id');
            if (!$id) $this->invalid($field, __('repair.catalog_reference_missing', ['field' => $field, 'value' => $data[$field]]));
            $data[$foreignKey] = $id;
            unset($data[$field]);
            if (in_array($this->entity, ['devices', 'services', 'series'], true) && $foreignKey !== 'repair_device_type_id') $key[$foreignKey] = $id;
        }
        if ($this->entity === 'series') $key['repair_device_type_id'] = $data['repair_device_type_id'];
        if ($this->entity === 'devices') {
            $series = empty($data['series_slug']) ? null : RepairDeviceSeries::where('slug', $data['series_slug'])
                ->where('repair_brand_id', $data['repair_brand_id'])->where('repair_device_type_id', $data['repair_device_type_id'])->first();
            if (!empty($data['series_slug']) && !$series) $this->invalid('series_slug', __('repair.catalog_series_mismatch'));
            $data['repair_device_series_id'] = $series?->id;
            unset($data['series_slug']);
        }
        $categoryIds = null;
        if ($this->entity === 'devices' && array_key_exists('category_slugs', $data)) {
            $slugs = array_values(array_unique(array_filter(array_map('trim', explode('|', $data['category_slugs'] ?? '')))));
            $categoryIds = RepairCategory::whereIn('slug', $slugs)->pluck('id')->all();
            if (count($slugs) !== count($categoryIds)) $this->invalid('category_slugs', __('repair.catalog_categories_missing'));
            unset($data['category_slugs']);
        }
        $record = $model::where($key)->first();
        if ($record && !$this->updateExisting) { $this->skipped++; return; }
        if ($record) { $record->update($data); $this->updated++; }
        else { $record = $model::create($data); $this->inserted++; }
        if ($categoryIds !== null) $record->categories()->sync($categoryIds);
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
