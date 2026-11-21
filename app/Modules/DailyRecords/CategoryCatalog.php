<?php

namespace App\Modules\DailyRecords;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class CategoryCatalog
{
    public function forEmployee(User $actor, string $kind): array
    {
        return $this->forDepartment($actor->department_id, $kind);
    }

    public function forDepartment(?int $department, string $kind): array
    {
        $rows = $this->available($kind, $department)->orderBy('position')->orderBy('id')->get();
        $children = $rows->whereNotNull('parent_id')->groupBy('parent_id');
        return $rows->whereNull('parent_id')->values()->map(fn ($node) => $this->item($node) + [
            'children' => ($children[$node->id] ?? collect())->map(fn ($child) => $this->item($child))->values(),
        ])->all();
    }

    public function selection(string $kind, ?int $department, array $data, ?object $existing = null): array
    {
        $categoryId = $data['category_id'];
        $subcategoryId = $data['subcategory_id'] ?? null;
        $retained = $existing && (int) $existing->category_id === (int) $categoryId
            && (int) $existing->subcategory_id === (int) $subcategoryId;
        if ($retained && $existing->classification) {
            // Retain the labels and field schema that applied when classified.
            $snapshot = json_decode($existing->classification, true, flags: JSON_THROW_ON_ERROR);
            $values = $this->validateValues($snapshot['fields'], $data['fields'] ?? []);
            return [$snapshot, $values, (bool) $existing->sensitive, null];
        }

        $nodes = $this->available($kind, $department)->whereIn('id', array_filter([$categoryId, $subcategoryId]))
            ->lockForUpdate()->get()->keyBy('id');
        $parent = $nodes[$categoryId] ?? null;
        $child = $nodes[$subcategoryId] ?? null;
        if (!$parent || $parent->parent_id || ($subcategoryId && (!$child || (int) $child->parent_id !== (int) $parent->id))
            || ($kind === 'incident' && !$child)) {
            throw ValidationException::withMessages(['category_id' => 'Choose an active category and a matching subcategory.']);
        }
        $fields = collect([...$this->decode($parent->fields), ...$this->decode($child?->fields)])->keyBy('key')->values()->all();
        $snapshot = [
            'category' => ['en' => $parent->name_en, 'km' => $parent->name_km],
            'subcategory' => $child ? ['en' => $child->name_en, 'km' => $child->name_km] : null,
            'fields' => $fields,
        ];
        return [
            $snapshot,
            $this->validateValues($fields, $data['fields'] ?? []),
            (bool) ($existing?->sensitive || $parent->sensitive || $child?->sensitive),
            $child?->follow_up_days ?? $parent->follow_up_days,
        ];
    }

    public function save(?int $id, array $data): int
    {
        return DB::transaction(function () use ($id, $data) {
            $table = DB::table('daily_record_categories');
            $old = $id ? (clone $table)->where('id', $id)->lockForUpdate()->first() : null;
            abort_if($id && !$old, 404);
            $parentId = $data['parent_id'] ?? null;
            $parent = $parentId ? (clone $table)->where('id', $parentId)->lockForUpdate()->first() : null;
            abort_if($parentId && (!$parent || $parent->parent_id || $parent->kind !== $data['kind']), 422, 'Invalid parent category.');
            abort_if($old && ($old->kind !== $data['kind'] || (int) $old->parent_id !== (int) $parentId),
                422, 'Create a new classification to change its type or parent.');
            $row = [
                'parent_id' => $parentId, 'kind' => $data['kind'], 'name_en' => $data['name_en'],
                'name_km' => $data['name_km'], 'icon' => $data['icon'] ?? null,
                'department_id' => $data['department_id'] ?? null, 'active' => $data['active'],
                'sensitive' => $data['sensitive'] || ($old?->sensitive ?? false),
                'position' => $data['position'], 'fields' => json_encode($data['fields'] ?? [], JSON_THROW_ON_ERROR),
                'tags' => json_encode($data['tags'] ?? [], JSON_THROW_ON_ERROR),
                'workflow' => json_encode($data['workflow'] ?? [], JSON_THROW_ON_ERROR), 'follow_up_days' => $data['follow_up_days'] ?? null,
            ];
            if ($id) {
                $table->where('id', $id)->update($row);
                return $id;
            }
            return $table->insertGetId($row);
        }, 3);
    }

    private function available(string $kind, ?int $department): Builder
    {
        return DB::table('daily_record_categories')->where('kind', $kind)->where('active', true)
            ->where(fn (Builder $q) => $q->whereNull('department_id')->orWhere('department_id', $department));
    }

    private function item(object $node): array
    {
        return [
            'id' => $node->id, 'name_en' => $node->name_en, 'name_km' => $node->name_km, 'icon' => $node->icon,
            'fields' => $this->decode($node->fields), 'tags' => $this->decode($node->tags), 'sensitive' => (bool) $node->sensitive,
        ];
    }

    private function validateValues(array $fields, array $values): array
    {
        $rules = [];
        foreach ($fields as $field) {
            $key = 'fields.'.$field['key'];
            $rules[$key] = [$field['required'] ? 'required' : 'nullable'];
            $rules[$key] = array_merge($rules[$key], match ($field['type']) {
                'number' => ['numeric', 'between:-1000000000,1000000000'],
                'date' => ['date_format:Y-m-d'],
                'datetime' => ['date_format:Y-m-d\TH:i'],
                'select' => ['string', Rule::in($field['options'])],
                'multi' => ['array', 'max:20'],
                'multiline' => ['string', 'max:3000'],
                default => ['string', 'max:240'],
            });
            if ($field['type'] === 'multi') {
                $rules["$key.*"] = ['string', 'distinct', Rule::in($field['options'])];
            }
        }
        if (array_diff_key($values, array_flip(array_column($fields, 'key')))) {
            throw ValidationException::withMessages(['fields' => 'An unconfigured field was supplied.']);
        }
        return Validator::make(['fields' => $values], $rules)->validate()['fields'] ?? [];
    }

    private function decode(?string $json): array
    {
        return json_decode($json ?? '[]', true, flags: JSON_THROW_ON_ERROR);
    }
}
