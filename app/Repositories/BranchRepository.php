<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Branch;
use App\Traits\ImageService;

class BranchRepository
{
    use ImageService;

    /**
     * @param array $select
     * @return mixed
     */
    public function getAllCompanyBranches($filterParameters,array $select=['*']): mixed
    {
        return Branch::select($select)
            ->with(['employees:id,name,avatar,branch_id'])
            ->withCount('employees')
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->latest()
            ->paginate($filterParameters['per_page']);
    }

    /**
     * @param $validatedData
     * @return mixed
     */
    public function store($validatedData):mixed
    {
        $validatedData = $this->prepareBranchUploads($validatedData);

        return Branch::create($validatedData)->fresh();
    }

    public function getLoggedInUserCompanyBranches($companyId,$select=['*'])
    {
       return  Branch::select($select)->where('company_id',$companyId)->get();
    }

    /**
     * @throws \Exception
     */
    public function getBranchesWithDepartments()
    {
        return Branch::select('id', 'name')
            ->with('departments:id,dept_name,branch_id')
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function toggleStatus($id):mixed
    {
        $branchDetail = $this->findBranchDetailById($id);
        return $branchDetail->update([
            'is_active' => !$branchDetail->is_active,
        ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findBranchDetailById($id,$with=[]):mixed
    {
        return Branch::with($with)->where('id',$id)->first();
    }

    public function update($branchDetail, $validatedData)
    {
        $validatedData = $this->prepareBranchUploads($validatedData, $branchDetail);

        return $branchDetail->update($validatedData);
    }

    public function delete(Branch $branch)
    {
        if ($branch->logo) {
            $this->removeImage(Branch::UPLOAD_PATH, $branch->logo);
        }

        foreach (($branch->payment_qr_codes ?? []) as $paymentQrCode) {
            if (!empty($paymentQrCode['qr_code'])) {
                $this->removeImage(Branch::UPLOAD_PATH, $paymentQrCode['qr_code']);
            }
        }

        return $branch->delete();


    }

    public function checkBranchHead($userId, $branchId=0)
    {
        $branch =  Branch::where('branch_head_id', $userId);

        if($branchId != 0){
            $branch =$branch->where('id','!=',$branchId);
        }

        return  $branch->exists();

    }

    private function prepareBranchUploads(array $validatedData, ?Branch $branch = null): array
    {
        if (isset($validatedData['logo'])) {
            if ($branch?->logo) {
                $this->removeImage(Branch::UPLOAD_PATH, $branch->logo);
            }

            $validatedData['logo'] = $this->storeImage($validatedData['logo'], Branch::UPLOAD_PATH, 500, 500);
        }

        $existingQrCodes = collect($branch?->payment_qr_codes ?? [])
            ->pluck('qr_code')
            ->filter()
            ->all();

        $paymentQrCodes = [];

        foreach (($validatedData['payment_qr_codes'] ?? []) as $paymentQrCode) {
            $paymentName = trim($paymentQrCode['payment_name'] ?? '');
            $existingQrCode = $paymentQrCode['existing_qr_code'] ?? null;
            $uploadedQrCode = $paymentQrCode['qr_code'] ?? null;

            if (!$paymentName && !$existingQrCode && !$uploadedQrCode) {
                continue;
            }

            $qrCodeFileName = $existingQrCode;

            if ($uploadedQrCode) {
                if ($existingQrCode) {
                    $this->removeImage(Branch::UPLOAD_PATH, $existingQrCode);
                }

                $qrCodeFileName = $this->storeImage($uploadedQrCode, Branch::UPLOAD_PATH, 500, 500);
            }

            if ($paymentName && $qrCodeFileName) {
                $paymentQrCodes[] = [
                    'payment_name' => $paymentName,
                    'qr_code' => $qrCodeFileName,
                ];
            }
        }

        if ($branch) {
            $keptQrCodes = collect($paymentQrCodes)->pluck('qr_code')->filter()->all();

            foreach (array_diff($existingQrCodes, $keptQrCodes) as $removedQrCode) {
                $this->removeImage(Branch::UPLOAD_PATH, $removedQrCode);
            }
        }

        $validatedData['payment_qr_codes'] = $paymentQrCodes ?: null;

        return $validatedData;
    }

}
