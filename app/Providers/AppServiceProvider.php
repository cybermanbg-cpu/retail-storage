<?php

namespace App\Providers;

use App\Models\ActiveCart;
use App\Models\Product;
use App\Observers\ActiveCartObserver;
use App\Observers\ProductObserver;
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
        Product::observe(ProductObserver::class);
    }
}
