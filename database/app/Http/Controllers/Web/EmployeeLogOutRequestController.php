<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeLogOutRequestController extends Controller
{
    private $view ='admin.logoutRequest.';

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllCompanyEmployeeLogOutRequest(Request $request)
    {
        $this->authorize('list_logout_request');
        try{
            $select = ['id','name','logout_status'];
            $logoutRequests = $this->userRepository->getAllCompanyEmployeeLogOutRequest($select);
            return view($this->view . 'index',compact('logoutRequests'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function acceptLogoutRequest($employeeId)
    {
        $this->authorize('accept_logout_request');
        try {
            DB::beginTransaction();
                $this->userRepository->acceptLogoutRequest($employeeId);
            DB::commit();
            return redirect()->back()->with('success', __('message.logout_request'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
