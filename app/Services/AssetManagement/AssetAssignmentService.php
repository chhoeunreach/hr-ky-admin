<?php

namespace App\Services\AssetManagement;

use App\Repositories\AssetAssignmentRepository;
use Exception;

class AssetAssignmentService
{
    public function __construct(
        protected AssetAssignmentRepository $assignmentRepository
    ){}

    public function getAssignmentsPaginated($assetId,$select= ['*'],$with=[])
    {

        return $this->assignmentRepository->getAllAssignmentPaginated($assetId,$select,$with);
    }

    public function getEmployeeAssignmentsPaginated($employeeId,$select= ['*'],$with=[])
    {

        return $this->assignmentRepository->getEmployeeAssignment($employeeId,$select,$with);
    }

    public function getReturnAssetsPaginated($select= ['*'],$with=[])
    {

        return $this->assignmentRepository->getAssignmentReturnPaginated($select,$with);
    }
    public function getMaintenanceAssetsPaginated($select= ['*'],$with=[])
    {

        return $this->assignmentRepository->getAssignmentMaintenancePaginated($select,$with);
    }
    /**
     * @throws Exception
     */
    public function findAssignmentById($id, $select=['*'], $with=[])
    {
        return $this->assignmentRepository->find($id,$select,$with)
            ?? throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
    }

    public function saveDetail($validatedData)
    {
        return $this->assignmentRepository->store($validatedData);
    }

    /**
     * @throws Exception
     */
    public function updateDetail($id, $validatedData)
    {
        $assignmentDetail = $this->findAssignmentById($id);
        // Lock the asset first, matching assignment/transfer lock ordering.
        \App\Models\Asset::whereKey($assignmentDetail->asset_id)->lockForUpdate()->firstOrFail();
        $assignmentDetail = \App\Models\AssetAssignment::whereKey($id)->lockForUpdate()->firstOrFail();
        if ($assignmentDetail->returned_date) {
            throw \Illuminate\Validation\ValidationException::withMessages(['asset_id' => __('index.asset_already_returned')]);
        }
        if (!auth('admin')->check() && request()->is('api/*') && (int) $assignmentDetail->user_id !== (int) auth()->id()) {
            abort(403);
        }
        if ($assignmentDetail->assigned_date > now()->toDateString()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['assigned_date' => __('index.asset_transfer_date_invalid')]);
        }
        $this->assignmentRepository->update($assignmentDetail, $validatedData);
        return $assignmentDetail;

    }

    /**
     * @throws Exception
     */
    public function deleteAsset($id)
    {
        $assignmentDetail = $this->findAssignmentById($id);

        return $this->assignmentRepository->delete($assignmentDetail);
    }

    /**
     * @throws Exception
     */
    public function toggleAvailabilityStatus($id)
    {
        $assignmentDetail = $this->findAssignmentById($id);

        return $this->assignmentRepository->changeIsAvailableStatus($assignmentDetail);
    }

    /**
     * @throws Exception
     */
    public function toggleRepairStatus($id)
    {
        $assignmentDetail = $this->findAssignmentById($id);

        return $this->assignmentRepository->changeRepairStatus($assignmentDetail);

    }


}
