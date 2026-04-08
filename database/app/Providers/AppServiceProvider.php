<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
//        JsonResource::withoutWrapping();
        Relation::enforceMorphMap([
            'project' => 'App\Models\Project',
            'task' => 'App\Models\Task',
            'comment' => 'App\Models\TaskComment',
            'reply' => 'App\Models\CommentReply',
        ]);

        Task::observe(TaskObserver::class);

        $locale = Cache::get('locale', 'en'); // Default to 'en' if no locale is set
        App::setLocale($locale);

    }
}
