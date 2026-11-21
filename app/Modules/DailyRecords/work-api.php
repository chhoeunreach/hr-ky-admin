<?php

use App\Http\Middleware\MobileFunctionPermission;
use App\Modules\DailyRecords\WorkRecordsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/daily-records')->middleware(['auth:api', 'app.security', 'permission', MobileFunctionPermission::class])
    ->controller(WorkRecordsApiController::class)->group(function () {
        Route::get('catalog', 'catalog');
        Route::get('work-records', 'records');
        Route::post('work-records', 'create')->middleware('throttle:30,1');
        Route::post('work-records/{entry}', 'update')->whereNumber('entry')->middleware('throttle:30,1');
        Route::post('work-records/{entry}/note', 'noteAction')->whereNumber('entry');
        Route::get('assigned-incidents/{entry}', 'assignedIncident')->whereNumber('entry');
        Route::post('assigned-incidents/{entry}/actions', 'assignedResponse')->whereNumber('entry')->middleware('throttle:30,1');
        Route::get('assigned-incidents/{entry}/files/{file}', 'assignedFile')->whereNumber(['entry', 'file']);
        Route::get('drafts/{key}', 'draft')->where('key', '[a-z0-9-]{1,48}');
        Route::post('drafts/{key}', 'saveDraft')->where('key', '[a-z0-9-]{1,48}')->middleware('throttle:60,1');
        Route::get('summaries/preview', 'preview');
        Route::get('summaries', 'ownSummaries');
        Route::post('summaries', 'saveSummary')->middleware('throttle:20,1');
        Route::get('summaries/{entry}', 'ownSummary')->whereNumber('entry');
        Route::get('summary-inbox', 'inbox');
        Route::get('summary-inbox/{entry}', 'managerSummary')->whereNumber('entry');
        Route::post('summary-inbox/{entry}/actions', 'review')->whereNumber('entry')->middleware('throttle:30,1');
        Route::get('summary-inbox/{entry}/files/{file}', 'reviewFile')->whereNumber(['entry', 'file']);
    });
