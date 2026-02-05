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

        // Force URL scheme for subfolder/port setup
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));

            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME);
            if ($scheme) {
                URL::forceScheme($scheme);
            }
        }
    }
}
