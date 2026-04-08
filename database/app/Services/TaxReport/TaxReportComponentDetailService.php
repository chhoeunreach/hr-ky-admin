<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportComponentDetailRepository;
use Exception;

class TaxReportComponentDetailService
{
    public function __construct(
        protected TaxReportComponentDetailRepository $componentDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllComponentDetails($select= ['*'],$with=[])
    {
        return $this->componentDetailRepository->getAllComponentDetail($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findComponentDetailById($id,$select=['*'],$with=[])
    {
        return  $this->componentDetailRepository->find($id,$select,$with);

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
                'type' => $data['type'],
                'amount' => $data['monthly'],
            ];

            $this->componentDetailRepository->create($dataToStore);

        }


    }
    /**
     * @throws Exception
     */
    public function updateComponentDetail($id, $validatedData)
    {

            $componentDetail = $this->findComponentDetailById($id);

            return $this->componentDetailRepository->update($componentDetail, $validatedData);

    }


}
