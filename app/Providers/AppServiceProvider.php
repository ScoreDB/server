<?php

namespace App\Providers;

use App\Services\Integrations\Github;
use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Github::class, function ($app) {
            return new Github($app->make(GitHubManager::class));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
