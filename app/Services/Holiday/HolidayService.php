<?php

namespace App\Services\Holiday;

use App\Helpers\AppHelper;
use App\Repositories\HolidayRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    private HolidayRepository $holidayRepo;

    public function __construct(HolidayRepository $holidayRepo)
    {
        $this->holidayRepo = $holidayRepo;
    }

    public function getAllHolidayLists($filterParameters, $select = ['*'], $with = [])
    {
        try {
            if (AppHelper::ifDateInBsEnabled()) {
                $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                $filterParameters['event_year'] = $filterParameters['event_year'] ?? $nepaliDate['year'];
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['event_year'], $filterParameters['month']);
                $filterParameters['start_date'] = $dateInAD['start_date'];
                $filterParameters['end_date'] = $dateInAD['end_date'];
            }
            return $this->holidayRepo->getAllHolidays($filterParameters, $select, $with);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function getAllActiveHolidays()
    {
        try {
            $date = AppHelper::yearDetailToFilterData();
            if (isset($date['end_date'])) {
                $date['end_date'] = AppHelper::getBsNxtYearEndDateInAd();
            }
            return $this->holidayRepo->getAllActiveHolidays($date);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function findHolidayDetailById($id)
    {
        try {
            $holidayDetail = $this->holidayRepo->findHolidayDetailById($id);
            if (!$holidayDetail) {
                throw new Exception(__('message.holiday_not_found'), 404);
            }
            return $holidayDetail;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function store($validatedData)
    {

            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();

        return $this->holidayRepo->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function update($validatedData, $id)
    {

            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $holidayDetail = $this->findHolidayDetailById($id);

        return $this->holidayRepo->update($holidayDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function toggleHolidayStatus($id)
    {


            $holidayDetail = $this->findHolidayDetailById($id);
        return $this->holidayRepo->toggleStatus($holidayDetail);

    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {

            $holidayDetail = $this->findHolidayDetailById($id);

        return $this->holidayRepo->delete($holidayDetail);

    }

    public function getAllActiveHolidaysFromNowToGivenNumberOfDays($numberOfDays)
    {

            $nowDate = Carbon::now()->format('Y-m-d');
            $toDate = Carbon::now()->addDay($numberOfDays)->format('Y-m-d');
            return $this->holidayRepo->getAllActiveHolidaysBetweenGivenDates($nowDate,$toDate);

    }

    public function getCurrentActiveHoliday()
    {

            return $this->holidayRepo->getRecentActiveHoliday();

    }

    public function getHolidayByDate($date, $select=['*'])
    {

            return $this->holidayRepo->getHolidayByDate($date, $select);

    }

}
