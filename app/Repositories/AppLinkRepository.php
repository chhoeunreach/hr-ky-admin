<?php

namespace App\Repositories;

use App\Models\AppLink;
use App\Traits\ImageService;

class AppLinkRepository
{
    use ImageService;

    public function getAllLinksPaginated($filterParameters = [], $select = ['*'])
    {
        return AppLink::select($select)
            ->when(!empty($filterParameters['link_type']), function ($query) use ($filterParameters) {
                $query->where('link_type', $filterParameters['link_type']);
            })
            ->when(isset($filterParameters['status']) && $filterParameters['status'] !== '', function ($query) use ($filterParameters) {
                $query->where('status', (bool)$filterParameters['status']);
            })
            ->when(!empty($filterParameters['search']), function ($query) use ($filterParameters) {
                $query->where(function ($q) use ($filterParameters) {
                    $q->where('name', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filterParameters['search'] . '%')
                      ->orWhere('url', 'like', '%' . $filterParameters['search'] . '%');
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
        if (isset($validatedData['image'])) {
            $validatedData['image'] = $this->storeImage($validatedData['image'], AppLink::UPLOAD_PATH, 500, 500);
        }
        return AppLink::create($validatedData)->fresh();
    }

    public function update($link, $validatedData)
    {
        if (isset($validatedData['image'])) {
            if ($link->image) {
                $this->removeImage(AppLink::UPLOAD_PATH, $link->image);
            }
            $validatedData['image'] = $this->storeImage($validatedData['image'], AppLink::UPLOAD_PATH, 500, 500);
        }
        return $link->update($validatedData);
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
