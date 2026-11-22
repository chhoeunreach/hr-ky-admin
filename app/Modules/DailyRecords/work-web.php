<?php

use App\Modules\DailyRecords\WorkRecordsAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/daily-records')->name('admin.daily-records.')->middleware(['admin.auth', 'permission'])
    ->controller(WorkRecordsAdminController::class)->group(function () {
        Route::get('incidents/{entry}', 'incident')->whereNumber('entry')->name('incident');
        Route::post('incidents/{entry}', 'incidentAction')->whereNumber('entry')->name('incident-action');
        Route::get('submissions', 'submissions')->name('submissions');
        Route::get('branch-routes', 'branchRoutes')->name('branch-routes');
        Route::get('submissions/{entry}', 'submission')->whereNumber('entry')->name('submission');
        Route::post('submissions/{entry}/review', 'review')->whereNumber('entry')->name('submission-review');
        Route::get('categories', 'categories')->name('categories');
        Route::post('categories', 'categorySave')->name('category-save');
    });
