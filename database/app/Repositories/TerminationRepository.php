<?php

namespace App\Repositories;

use App\Models\Termination;
use App\Traits\ImageService;

class TerminationRepository
{
    use ImageService;


    public function getAllTerminationPaginated($select=['*'],$with=[])
    {
        return Termination::with($with)->select($select)
            ->latest()
            ->paginate(Termination::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Termination::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        $validatedData['created_by']= auth()->user()->id;
        if(isset($validatedData['document'])){
            $validatedData['document'] = $this->storeImage($validatedData['document'], Termination::UPLOAD_PATH, 500, 250);
        }
        return Termination::create($validatedData)->fresh();
    }

    public function update($terminationDetail,$validatedData)
    {
        $validatedData['updated_by']= auth()->user()->id;
        if (isset($validatedData['document'])) {
            if ($terminationDetail['document']) {
                $this->removeImage(Termination::UPLOAD_PATH, $terminationDetail['document']);
            }
            $validatedData['document'] = $this->storeImage($validatedData['document'], Termination::UPLOAD_PATH, 500, 250);
        }
        return $terminationDetail->update($validatedData);
    }

    public function delete($terminationDetail)
    {
        return $terminationDetail->delete();
    }


}
