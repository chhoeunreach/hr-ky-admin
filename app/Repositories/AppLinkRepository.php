<?php

namespace App\Repositories;

use App\Models\AppLink;
use App\Traits\ImageService;

class AppLinkRepository
{
    use ImageService;

    public function getAllLinksPaginated($filterParameters = [], $select = ['*'])
    {
        return AppLink::select($select)->with(['createdBy:id,name', 'managers:id,name'])
            ->when(!empty($filterParameters['link_type']), function ($query) use ($filterParameters) {
                $query->where('link_type', $filterParameters['link_type']);
            })
            ->when(!empty($filterParameters['account_type']), fn ($query) => $query->where('account_type', $filterParameters['account_type']))
            ->when(isset($filterParameters['status']) && $filterParameters['status'] !== '', function ($query) use ($filterParameters) {
                $query->where('status', (bool)$filterParameters['status']);
            })
            ->when(!empty($filterParameters['search']), function ($query) use ($filterParameters) {
                $query->where(function ($q) use ($filterParameters) {
                    $q->where('name', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('url', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('username', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('phone_number', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filterParameters['search'] . '%');
                });
            })
            ->orderBy('order', 'asc')
            ->latest('id')
            ->paginate(AppLink::RECORDS_PER_PAGE);
    }

    public function getActiveLinks($select = ['*'])
    {
        return AppLink::select($select)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest('id')
            ->get();
    }

    public function findById($id, $select = ['*'])
    {
        return AppLink::select($select)->find($id);
    }

    public function store($validatedData)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($validatedData) {
            $managerIds = $validatedData['manager_ids'] ?? [];
            unset($validatedData['manager_ids']);
            unset($validatedData['clear_password']);
            if (isset($validatedData['image'])) {
                $validatedData['image'] = $this->storeImage($validatedData['image'], AppLink::UPLOAD_PATH, 500, 500);
            }
            $link = AppLink::create($validatedData);
            $link->managers()->sync($managerIds);
            return $link->fresh();
        });
    }

    public function update($link, $validatedData)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($link, $validatedData) {
            $managerIds = $validatedData['manager_ids'] ?? [];
            unset($validatedData['manager_ids']);
            if (!empty($validatedData['clear_password'])) {
                $validatedData['password'] = null;
            } elseif (!isset($validatedData['password']) || $validatedData['password'] === '') {
                unset($validatedData['password']);
            }
            unset($validatedData['clear_password']);
            if (isset($validatedData['image'])) {
                if ($link->image) {
                    $this->removeImage(AppLink::UPLOAD_PATH, $link->image);
                }
                $validatedData['image'] = $this->storeImage($validatedData['image'], AppLink::UPLOAD_PATH, 500, 500);
            } else {
                unset($validatedData['image']);
            }
            $updated = $link->update($validatedData);
            $link->managers()->sync($managerIds);
            return $updated;
        });
    }

    public function delete($link)
    {
        if ($link->image) {
            $this->removeImage(AppLink::UPLOAD_PATH, $link->image);
        }
        return $link->delete();
    }

    public function toggleStatus($link)
    {
        return $link->update(['status' => !$link->status]);
    }
}
