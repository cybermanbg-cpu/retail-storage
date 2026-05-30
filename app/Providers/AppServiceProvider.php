<?php

namespace App\Providers;

use App\Models\ActiveCart;
use App\Observers\ActiveCartObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ActiveCart::observe(ActiveCartObserver::class);
    }
}
