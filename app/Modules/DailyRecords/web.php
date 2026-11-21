<?php

use App\Modules\DailyRecords\DailyRecordsAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/daily-records')->name('admin.daily-records.')->middleware(['admin.auth', 'permission'])
    ->controller(DailyRecordsAdminController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('employees', 'employees')->name('employees');
        Route::get('assign', 'create')->name('create');
        Route::post('assign', 'store')->name('store')->middleware('throttle:20,1');
        Route::post('tasks/review', 'bulkReview')->name('bulk-review');
        Route::post('tasks/{assignment}/actions', 'taskAction')->whereNumber('assignment')->name('task-action');
        Route::post('records/{entry}/review', 'review')->whereNumber('entry')->name('review');
        Route::get('tasks/{assignment}/files/{file}', 'taskFile')->whereNumber(['assignment', 'file'])->name('task-file');
        Route::get('records/{entry}/files/{file}', 'recordFile')->whereNumber(['entry', 'file'])->name('record-file');
        Route::get('export/{format}', 'export')->where('format', 'xlsx|pdf')->name('export')->middleware('throttle:10,1');
    });

require __DIR__ . '/work-web.php';
