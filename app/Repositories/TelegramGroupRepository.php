<?php

namespace App\Repositories;

use App\Models\TelegramGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TelegramGroupRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = TelegramGroup::with(['branch:id,name', 'department:id,dept_name'])
            ->when(!empty($filters['name']), function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            })
            ->when(!empty($filters['action_key']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('action_key', $filters['action_key'])
                        ->orWhereJsonContains('action_keys', $filters['action_key']);
                });
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['is_active']);
            })
            ->latest();

        $perPage = $filters['per_page'] ?? TelegramGroup::RECORDS_PER_PAGE;
        if ($perPage === 'all') {
            $perPage = max((clone $query)->count(), 1);
        }

        return $query->paginate((int) $perPage);
    }

    public function find(int $id): ?TelegramGroup
    {
        return TelegramGroup::with(['branch:id,name', 'department:id,dept_name'])->find($id);
    }

    public function store(array $data): TelegramGroup
    {
        return TelegramGroup::create($this->prepareData($data))->fresh();
    }

    public function update(TelegramGroup $telegramGroup, array $data): bool
    {
        return $telegramGroup->update($this->prepareData($data));
    }

    public function delete(TelegramGroup $telegramGroup): ?bool
    {
        return $telegramGroup->delete();
    }

    public function toggleStatus(int $id): bool
    {
        $telegramGroup = TelegramGroup::findOrFail($id);

        return $telegramGroup->update([
            'is_active' => ! $telegramGroup->is_active,
        ]);
    }

    public function activeForAction(string $actionKey): Collection
    {
        return TelegramGroup::query()
            ->where('is_active', true)
            ->where(function ($query) use ($actionKey) {
                $query->where('action_key', $actionKey)
                    ->orWhere('action_key', TelegramGroup::ACTION_GENERAL)
                    ->orWhereJsonContains('action_keys', $actionKey)
                    ->orWhereJsonContains('action_keys', TelegramGroup::ACTION_GENERAL);
            })
            ->get();
    }

    private function prepareData(array $data): array
    {
        $data['send_for_all'] = (bool) ($data['send_for_all'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['chat_ids'] = array_values(array_unique(array_filter($data['chat_ids'] ?? [])));
        $data['action_keys'] = array_values(array_unique(array_filter($data['action_keys'] ?? [])));
        $data['event_keys'] = array_values(array_unique(array_filter($data['event_keys'] ?? [])));
        $data['branch_ids'] = array_values(array_unique(array_filter($data['branch_ids'] ?? [])));
        $data['department_ids'] = array_values(array_unique(array_filter($data['department_ids'] ?? [])));
        $data['chat_id'] = $data['chat_ids'][0] ?? $data['chat_id'] ?? null;
        $data['action_key'] = $data['action_keys'][0] ?? $data['action_key'] ?? TelegramGroup::ACTION_GENERAL;
        $data['branch_id'] = $data['branch_ids'][0] ?? null;
        $data['department_id'] = $data['department_ids'][0] ?? null;

        return $data;
    }
}
