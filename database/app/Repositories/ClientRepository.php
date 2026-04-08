<?php

namespace App\Repositories;

use App\Models\Client;
use App\Traits\ImageService;

class ClientRepository
{
    use ImageService;

    CONST IS_ACTIVE = 1;

    public function getAllClients($select=['*'],$with=[])
    {
        return Client::with($with)->select($select)->get();
    }

    public function getTopClientsOfCompany()
    {
       return Client::select(
           'clients.id',
           'clients.name',
           'clients.email',
           'clients.contact_no',
           'clients.avatar',
           'clients.address'
       )
            ->leftJoin('projects', 'clients.id', '=', 'projects.client_id')
            ->selectRaw('count(projects.id) as project_count')
            ->groupBy(
                'clients.id',
                'clients.name',
                'clients.email',
                'clients.contact_no',
                'clients.avatar',
                'clients.address'
            )
            ->orderByDesc('project_count')
            ->take(6)
            ->get();

    }

    public function getAllActiveClients($select=['*'])
    {
        return Client::select($select)->where('is_active',1)->get();
    }

    public function store($validatedData)
    {
        $validatedData['avatar'] = $this->storeImage($validatedData['avatar'], Client::UPLOAD_PATH,500,500);
        return Client::create($validatedData)->fresh();
    }

    public function findClientDetailById($id,$select=['*'],$with= [])
    {
        return Client::select($select)->with($with)
            ->where('id',$id)->first();
    }

    public function delete($clientDetail)
    {
        if($clientDetail['avatar']){
            $this->removeImage(Client::UPLOAD_PATH, $clientDetail['avatar']);
        }
        return $clientDetail->delete();
    }

    public function update($clientDetail,$validatedData)
    {
        if (isset($validatedData['avatar'])) {
            if($clientDetail['avatar']){
                $this->removeImage(Client::UPLOAD_PATH, $clientDetail['avatar']);
            }
            $validatedData['avatar'] = $this->storeImage($validatedData['avatar'], Client::UPLOAD_PATH,500,500);
        }
        return $clientDetail->update($validatedData);
    }

    public function toggleIsActiveStatus($clientDetail)
    {
        return $clientDetail->update([
            'is_active' => !$clientDetail->is_active,
        ]);
    }

}
