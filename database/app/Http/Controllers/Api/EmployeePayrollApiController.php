<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Requests\Payroll\Payslip\PayslipRequest;
use App\Resources\Payroll\Payslip\PayrollCollection;
use App\Resources\Payroll\Payslip\PayslipResource;
use App\Services\Payroll\GeneratePayrollService;
use App\Services\Payroll\UnderTimeSettingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EmployeePayrollApiController extends Controller
{
    public function __construct(protected GeneratePayrollService $generatePayrollService, protected UnderTimeSettingService $utSettingService){}

    public function getPayrollList(PayslipRequest $request): JsonResponse
    {
        try {

            $this->authorize('view_payslip_list');

            $validatedData = $request->validated();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            if($isBsEnabled)
            {
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($validatedData['year'], $validatedData['month']);

                $startDate = date('Y-m-d',strtotime($dateInAD['start_date'])) ?? null;
                $endDate = date('Y-m-d',strtotime($dateInAD['end_date'])) ?? null;
            }else{
                $firstDayOfMonth  = Carbon::create($validatedData['year'], $validatedData['month'], 1)->startOfDay();
                $startDate = date('Y-m-d',strtotime($firstDayOfMonth));
                $endDate = date('Y-m-d',strtotime($firstDayOfMonth->endOfMonth()));
            }

            $payslip = $this->generatePayrollService->getEmployeePayslip(getAuthUserCode(), $startDate, $endDate, $isBsEnabled);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            $payslipData = new PayrollCollection($payslip);

            $data =[
                'payslip'=>$payslipData,
                'currency'=>$currency
            ];
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getEmployeePayslipDetailById($id)
    {
        try {
            $this->authorize('view_payslip_detail');

            $payslipDetail = $this->generatePayrollService->getEmployeePayslipDetail($id);

            $components = $this->generatePayrollService->getEmployeePayslipDetailData($id);
            $earnings = array_values(array_filter($components, function ($component) {
                return $component['component_type'] == 'earning';
            }));

            $deductions = array_values(array_filter($components, function ($component) {
                return $component['component_type'] == 'deductions';
            }));

            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            /** for pdf view */
            $companyLogoPath = Company::UPLOAD_PATH;

            $html = View::make('admin.payroll.employeeSalary.download_payslip', compact('payslipDetail', 'earnings', 'deductions', 'companyLogoPath','currency'))->render();

            /** resource for payslip data */
            $payslipDetailData = new PayslipResource($payslipDetail);

            $data =[
                'payslipData'=>$payslipDetailData,
                'currency'=>$currency,
                'earnings'=>$earnings,
                'deductions'=>$deductions,
                'file'=>$html,
            ];


            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}
