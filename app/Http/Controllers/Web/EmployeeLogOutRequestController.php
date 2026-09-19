<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeLogOutRequestController extends Controller
{
    use CustomAuthorizesRequests;
    private $view ='admin.logoutRequest.';

    public function __construct(protected UserRepository $userRepository, protected CompanyRepository $companyRepository)
    {}

    public function getAllCompanyEmployeeLogOutRequest(Request $request)
    {
        $this->authorize('list_logout_request');
        try{
            $filterData = [
                'company_id' => AppHelper::getAuthUserCompanyId(),
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterData['branch_id'] = auth()->user()->branch_id;
            }
            $select = [
                'id', 'name', 'english_name', 'employee_code', 'email', 'phone', 'avatar',
                'employment_type', 'device_type', 'logout_status', 'branch_id',
                'department_id', 'post_id', 'role_id', 'updated_at',
            ];
            $relations = [
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
                'role:id,name,slug',
                'latestDeviceLocation' => function ($query) {
                    $query->select([
                        'user_locations.id',
                        'user_locations.user_id',
                        'user_locations.device_name',
                        'user_locations.battery_level',
                        'user_locations.updated_at',
                    ]);
                },
            ];
            $logoutRequests = $this->userRepository->getAllCompanyEmployeeLogOutRequest($filterData, $select, $relations);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index',compact('logoutRequests','companyDetail','filterData'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function acceptLogoutRequest($employeeId)
    {
        $this->authorize('accept_logout_request');
        try {
            $employee = $this->userRepository->findUserDetailById($employeeId, ['id', 'company_id', 'branch_id', 'logout_status']);
            if (!$employee
                || (int) $employee->company_id !== (int) AppHelper::getAuthUserCompanyId()
                || (!auth('admin')->check()
                    && auth()->check()
                    && filled(auth()->user()->branch_id)
                    && (int) $employee->branch_id !== (int) auth()->user()->branch_id)
                || (int) $employee->logout_status !== User::LOGOUT_STATUS['pending']) {
                return redirect()->back()->with('danger', __('index.no_records_found'));
            }
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
