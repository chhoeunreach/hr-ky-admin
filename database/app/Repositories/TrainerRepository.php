<?php

namespace App\Repositories;


use App\Helpers\AppHelper;
use App\Models\Trainer;
use Illuminate\Support\Facades\DB;

class TrainerRepository
{
    public function getAllTrainerPaginated($select=['*'],$with=[])
    {
        return Trainer::select($select)->with($with)
            ->latest()
            ->paginate(Trainer::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Trainer::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function findTrainers($ids,$select=['*'])
    {
        return Trainer::select($select)
            ->whereIn('id',$ids)
            ->get();
    }

    public function findByType($type)
    {
        return Trainer::select(
                'trainers.id',
                DB::raw('COALESCE(trainers.name, users.name) AS name')
            )
            ->leftJoin('users','trainers.employee_id','users.id')
            ->where('trainers.trainer_type',$type)
            ->get();
    }

    public function store($validatedData)
    {
        $validatedData['created_by'] = auth()->user()->id;
        return Trainer::create($validatedData)->fresh();
    }

    public function update($trainerDetail,$validatedData)
    {
        return $trainerDetail->update($validatedData);
    }

    public function delete($trainerDetail)
    {
        return $trainerDetail->delete();
    }
    public function toggleStatus($trainerDetail)
    {
        return $trainerDetail->update([
            'status' => !$trainerDetail->status,
        ]);
    }



}
