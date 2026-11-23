<?php

namespace App\Repositories;

use App\Models\StorePhoneNumber;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StorePhoneNumberRepository
{
    public function getAllStorePhoneNumbersPaginated(array $filterParameters = [], $perPage = 25): LengthAwarePaginator
    {
        $pageSize = ($perPage === 'all' || (is_numeric($perPage) && (int) $perPage > 2000)) ? 5000 : (intval($perPage) ?: 25);

        $query = StorePhoneNumber::query()
            ->with(['asset:id,name,asset_code', 'branch:id,name', 'employee:id,name,email,avatar', 'creator:id,name'])
            ->filter($filterParameters);

        $sort = $filterParameters['sort'] ?? null;
        $direction = strtolower($filterParameters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort && in_array($sort, ['phone_number', 'operator', 'sim_slot', 'purpose', 'status', 'assigned_date', 'id'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest('id');
        }

        return $query->paginate($pageSize);
    }

    public function getActiveStorePhoneNumbers(array $filterParameters = []): Collection
    {
        return StorePhoneNumber::query()
            ->active()
            ->with(['asset:id,name,asset_code', 'branch:id,name', 'employee:id,name,email,avatar'])
            ->filter($filterParameters)
            ->orderBy('branch_id')
            ->orderBy('purpose')
            ->get();
    }

    public function findById(int $id): ?StorePhoneNumber
    {
        return StorePhoneNumber::query()
            ->with(['asset', 'branch', 'employee'])
            ->find($id);
    }

    public function store(array $validatedData): StorePhoneNumber
    {
        $validatedData['created_by'] = auth()->id();
        return StorePhoneNumber::create($validatedData);
    }

    public function update(StorePhoneNumber $storePhoneNumber, array $validatedData): bool
    {
        $validatedData['updated_by'] = auth()->id();
        return $storePhoneNumber->update($validatedData);
    }

    public function delete(StorePhoneNumber $storePhoneNumber): ?bool
    {
        return $storePhoneNumber->delete();
    }
}
