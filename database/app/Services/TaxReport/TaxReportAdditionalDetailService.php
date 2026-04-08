<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportAdditionalDetailRepository;
use Exception;

class TaxReportAdditionalDetailService
{
    public function __construct(
        protected TaxReportAdditionalDetailRepository $additionalDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllAdditionalDetails($select= ['*'],$with=[])
    {
        return $this->additionalDetailRepository->getAll($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findAdditionalDetailById($id,$select=['*'],$with=[])
    {
        return  $this->additionalDetailRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function store($taxReportId, $validatedData)
    {

        foreach($validatedData as $data) {

            $dataToStore = [
                'tax_report_id'=>$taxReportId,
                'salary_component_id' => $data['id'],
                'amount' => $data['monthly'],
            ];

            $this->additionalDetailRepository->create($dataToStore);

        }
    }
    /**
     * @throws Exception
     */
    public function updateAdditionalDetail($validatedData)
    {

        foreach($validatedData as $key => $data) {

          $additionalData = $this->findAdditionalDetailById($key);

          $this->additionalDetailRepository->update($additionalData, ['amount'=>$data]);

        }

        return true;

    }


}
