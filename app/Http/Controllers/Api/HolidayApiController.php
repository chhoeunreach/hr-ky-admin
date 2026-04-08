<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Holiday\HolidayCollection;
use App\Services\Holiday\HolidayService;
use Exception;
use Illuminate\Http\JsonResponse;

class HolidayApiController extends Controller
{
    private HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function getAllActiveHoliday(): JsonResponse
    {
        try {
            $holidays = $this->holidayService->getAllActiveHolidays();
            $getAllHolidays = new HolidayCollection($holidays);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $getAllHolidays);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }


}

