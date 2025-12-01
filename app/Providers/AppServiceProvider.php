<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Administration;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Observers\AdministrationObserver;

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
        Paginator::useBootstrapFive();
        Carbon::setLocale('id');

        // Register observers
        Administration::observe(AdministrationObserver::class);

        // pastikan semua asset punya prefix folder arka-hero
        URL::forceRootUrl(config('app.url'));

        if (config('app.asset_url')) {
            URL::forceScheme('http');
        }
    }
}
