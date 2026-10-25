<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetType;
use App\Models\User;
use App\Models\Project;
use App\Traits\ImageService;

class AssetRepository
{
    use ImageService;

    public function getAllAssetsPaginated($filterParameters,$select=['*'],$with=[])
    {
        $query = Asset::select($select)->with($with)
            ->when(isset($filterParameters['type_id']), function($query) use ($filterParameters){
                $query->where('type_id', $filterParameters['type_id']);
            })
             ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->when(isset($filterParameters['is_working']), function ($query) use ($filterParameters) {
                $query->where('is_working', 'like', '%' . $filterParameters['is_working'] . '%');
            })
            ->when(isset($filterParameters['is_available']), function ($query) use ($filterParameters) {
                $query->where('is_available', $filterParameters['is_available']);
            })
            ->when(isset($filterParameters['purchased_from']), function($query) use ($filterParameters){
                $query->whereDate('purchased_date','>=',date('Y-m-d',strtotime($filterParameters['purchased_from'])));
            })
            ->when(isset($filterParameters['purchased_to']), function($query) use ($filterParameters){
                $query->whereDate('purchased_date','<=',date('Y-m-d',strtotime($filterParameters['purchased_to'])));
            })
            ->when(isset($filterParameters['q']) && $filterParameters['q'] !== '', function ($query) use ($filterParameters) {
                $query->where(function ($search) use ($filterParameters) {
                    foreach (['name', 'asset_code', 'asset_serial_no', 'phone_numbers', 'icloud', 'note'] as $column) {
                        $search->orWhere($column, 'like', '%' . $filterParameters['q'] . '%');
                    }
                });
            });

        $sort = $filterParameters['sort'] ?? null;
        $direction = ($filterParameters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        if ($sort && in_array($sort, Asset::SORTABLE_COLUMNS, true)) {
            if (in_array($sort, ['employee', 'assigned_to'], true)) {
                $latestEmployee = AssetAssignment::selectRaw('CASE WHEN returned_date IS NULL THEN user_id ELSE NULL END')
                    ->whereColumn('asset_id', 'assets.id')->orderByDesc('assigned_date')->orderByDesc('id')->limit(1);
                $query->orderBy(User::select('name')->where('users.id', $latestEmployee)->limit(1), $direction);
            } elseif ($sort === 'type') {
                $query->orderBy(AssetType::select('name')->whereColumn('asset_types.id', 'assets.type_id')->limit(1), $direction);
            } else {
                $column = ['number' => 'id', 'description' => 'note'][$sort] ?? $sort;
                $query->orderBy('assets.' . $column, $direction);
            }
        } else {
            $query->latest('assets.created_at');
        }
        $query->orderBy('assets.id', $sort === 'number' ? $direction : 'desc');

        return !empty($filterParameters['exporting']) || ($filterParameters['per_page'] ?? null) === 'all'
            ? $query->get()
            : $query->paginate($filterParameters['per_page'] ?? getRecordPerPage())->withQueryString();
    }

    public function getReturnAssetsPaginated($select=['*'],$with=[])
    {
        $branchId = null;
        if(!auth('admin')->check() && auth()->check()){
            $branchId = auth()->user()->branch_id;
        }
        return Asset::select($select)->with($with)
             ->when(!is_null($branchId), function($query) use ($branchId){
                $query->where('branch_id', $branchId);
            })
            ->whereHas('assignment', function ($query) {
                $query->whereNotNull('returned_date');
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function findAssetById($id,$select=['*'],$with=[])
    {
        return Asset::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        unset($validatedData['clear_asset_password'], $validatedData['clear_screen_time_passcode']);
        $files = $this->saveImages($validatedData);
        unset($validatedData['remove_images']);
        $validatedData['image'] = $files[0] ?? null;
        $validatedData['images'] = $files;
        try {
            return Asset::create($validatedData)->fresh();
        } catch (\Throwable $exception) {
            foreach ($files as $file) $this->removeImage(Asset::UPLOAD_PATH, $file);
            throw $exception;
        }
    }

    public function update($assetDetail, $validatedData)
    {
        foreach (['asset_password', 'screen_time_passcode'] as $field) {
            if (!empty($validatedData['clear_'.$field])) $validatedData[$field] = null;
            elseif (!isset($validatedData[$field]) || $validatedData[$field] === '') unset($validatedData[$field]);
            unset($validatedData['clear_'.$field]);
        }
        if (!isset($validatedData['image']) && empty($validatedData['images']) && empty($validatedData['remove_images'])) {
            unset($validatedData['image'], $validatedData['images'], $validatedData['remove_images']);
            return $assetDetail->update($validatedData);
        }
        $existing = $assetDetail->imageFiles();
        $remove = $validatedData['remove_images'] ?? [];
        // A legacy single-image upload replaces the cover; gallery uploads append.
        if (isset($validatedData['image']) && $assetDetail->image) $remove[] = $assetDetail->image;
        $keep = array_values(array_diff($existing, $remove));
        $count = count($keep) + count($validatedData['images'] ?? []) + (isset($validatedData['image']) ? 1 : 0);
        if ($count > 10) {
            throw \Illuminate\Validation\ValidationException::withMessages(['images' => __('index.asset_images_limit')]);
        }
        $added = $this->saveImages($validatedData);
        $files = array_merge($keep, $added);
        unset($validatedData['remove_images']);
        $validatedData['image'] = $files[0] ?? null;
        $validatedData['images'] = $files;
        try {
            $result = $assetDetail->update($validatedData);
        } catch (\Throwable $exception) {
            foreach ($added as $file) $this->removeImage(Asset::UPLOAD_PATH, $file);
            throw $exception;
        }
        \Illuminate\Support\Facades\DB::afterCommit(function () use ($existing, $files) {
            foreach (array_diff($existing, $files) as $file) $this->removeImage(Asset::UPLOAD_PATH, $file);
        });
        return $result;
    }

    private function saveImages(array $data): array
    {
        $uploads = $data['images'] ?? [];
        if (isset($data['image'])) array_unshift($uploads, $data['image']);
        if (count($uploads) > 10) {
            throw \Illuminate\Validation\ValidationException::withMessages(['images' => __('index.asset_images_limit')]);
        }
        $files = [];
        try {
            \Illuminate\Support\Facades\File::ensureDirectoryExists(public_path(Asset::UPLOAD_PATH));
            foreach ($uploads as $upload) {
                $filename = \Illuminate\Support\Str::uuid().'.'.$upload->extension();
                $upload->move(public_path(Asset::UPLOAD_PATH), $filename);
                $files[] = $filename;
            }
        } catch (\Throwable $exception) {
            foreach ($files as $file) $this->removeImage(Asset::UPLOAD_PATH, $file);
            throw $exception;
        }
        return $files;
    }

    public function delete($assetDetail)
    {
        $files = $assetDetail->imageFiles();
        $result = $assetDetail->delete();
        \Illuminate\Support\Facades\DB::afterCommit(function () use ($files) {
            foreach ($files as $file) $this->removeImage(Asset::UPLOAD_PATH, $file);
        });
        return $result;
    }
}
