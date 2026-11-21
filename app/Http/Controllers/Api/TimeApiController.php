<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TimeApiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $timezone = config('app.timezone');
        $serverTime = now($timezone);

        return AppHelper::sendSuccessResponse('ok', [
            'server_time_utc' => $serverTime->copy()->utc()->format('Y-m-d\TH:i:s.v\Z'),
            'timezone' => $timezone,
            'timezone_offset' => $serverTime->format('P'),
        ], ['Cache-Control' => 'public, max-age=0, s-maxage=1']);
    }
}
