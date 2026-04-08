<?php

namespace App\Repositories;


use App\Models\SSF;

class SSFRepository
{

    public function find($id)
    {
        return SSF::where('id',$id)
            ->firstOrFail();
    }

    public function getDetail($select=['*'])
    {
        return SSF::select($select)->where('is_active',1)->first();
    }


    public function store($validatedData)
    {
        return SSF::create($validatedData)->fresh();
    }

    public function update($ssfDetail, $validatedData)
    {
        return $ssfDetail->update($validatedData);
    }

}
