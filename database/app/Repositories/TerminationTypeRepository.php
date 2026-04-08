<?php

namespace App\Repositories;



use App\Models\TerminationType;

class TerminationTypeRepository
{

    public function getAllTerminationTypes($select=['*'],$with=[])
    {
        return TerminationType::select($select)->withCount($with)->get();
    }

    public function getAllActiveTerminationTypes($select=['*'])
    {
        return TerminationType::select($select)->where('status',1)->get();
    }

    public function find($id,$select=['*'],$with=[])
    {
        return TerminationType::select($select)->withCount($with)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return TerminationType::create($validatedData)->fresh();
    }

    public function update($trainingTypeDetail,$validatedData)
    {
        return $trainingTypeDetail->update($validatedData);
    }

    public function delete($trainingTypeDetail)
    {
        return $trainingTypeDetail->delete();
    }

    public function toggleStatus($trainingTypeDetail)
    {
        return $trainingTypeDetail->update([
            'status' => !$trainingTypeDetail->status,
        ]);
    }
}
