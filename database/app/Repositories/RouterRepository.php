<?php

namespace App\Repositories;

use App\Models\Router;

class RouterRepository
{
    const IS_ACTIVE = 1;

    public function getAllRouters($select=['*'],$with=[])
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

        return Router::with($with)->select($select)
            ->when(isset($branchId) && ($authUserId != 1), function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->latest()->paginate(Router::RECORDS_PER_PAGE);
    }

    public function getAllBranchActiveRouters($select=['*'])
    {
        return Router::where('is_active',self::IS_ACTIVE)->get();
    }

    public function findRouterDetailByBranchId($authUserBranchId,$with=[],$select=['*'])
    {
        return Router::with($with)
                    ->select($select)
                    ->where('is_active',self::IS_ACTIVE)
                    ->where('branch_id',$authUserBranchId)
                    ->first();
    }

    public function findRouterDetailBSSID($routerBSSID)
    {
        $convertedBssid = $this->convertBssid($routerBSSID);
        return Router::where('is_active',self::IS_ACTIVE)
            ->where('router_ssid',$convertedBssid)
            ->first();
    }
    public function convertBssid($routerBSSID)
    {
        $bssid= '';
        if(strlen($routerBSSID) == 17){
            return $routerBSSID;
        }else{

            $splitArray = explode(':',$routerBSSID);
            $count = count($splitArray);
            foreach ($splitArray as $key=>$value){

                if($key == ($count-1)){
                    $separator = '';
                }else{
                    $separator = ':';
                }

                if(strlen($value) == 1){
                    $bssid .= '0'.$value.$separator;
                }elseif(strlen($value) == 0){
                    $bssid .='00'.$separator;
                }else{
                    $bssid .=$value.$separator;
                }
            }

           return $bssid;
        }
    }

    public function store($validatedData)
    {
        return Router::create($validatedData)->fresh();
    }

    public function findRouterDetailById($id)
    {
        return Router::where('id',$id)->first();
    }

    public function delete($routerDetail)
    {
        return $routerDetail->delete();
    }

    public function update($routerDetail,$validatedData)
    {
        return $routerDetail->update($validatedData);
    }

    public function toggleStatus($id)
    {
        $routerDetail = Router::where('id',$id)->first();
        return $routerDetail->update([
            'is_active' => !$routerDetail->is_active,
        ]);
    }
}
