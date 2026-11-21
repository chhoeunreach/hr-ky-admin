<?php

use App\Http\Middleware\MobileFunctionPermission;
use App\Modules\DailyRecords\DailyRecordsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/daily-records')->middleware(['auth:api', 'app.security', 'permission', MobileFunctionPermission::class])
    ->controller(DailyRecordsApiController::class)->group(function () {
        Route::get('capabilities', 'capabilities');
        Route::get('employees', 'employees');
        Route::get('groups', 'groups');
        Route::post('assign', 'assign')->middleware('throttle:20,1');
        Route::get('tasks', 'tasks');
        Route::get('tasks/{assignment}', 'task')->whereNumber('assignment');
        Route::post('tasks/{assignment}/actions', 'taskAction')->whereNumber('assignment');
        Route::get('tasks/{assignment}/files/{file}', 'taskFile')->whereNumber(['assignment', 'file']);
        Route::get('records', 'records');
        Route::post('records', 'createRecord')->middleware('throttle:30,1');
        Route::get('records/{entry}', 'record')->whereNumber('entry');
        Route::post('records/{entry}/actions', 'recordAction')->whereNumber('entry');
        Route::get('records/{entry}/files/{file}', 'recordFile')->whereNumber(['entry', 'file']);
    });

require __DIR__ . '/work-api.php';
