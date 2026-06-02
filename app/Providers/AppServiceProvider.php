<?php

namespace App\Providers;

use App\Models\ActiveCart;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Observers\ActiveCartObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        User::creating(function ($user) {
            if (Auth::user() && Auth::user()->owner_id) {
                $user->owner_id = Auth::user()->owner_id;
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ActiveCart::observe(ActiveCartObserver::class);
        Product::observe(ProductObserver::class);
        Purchase::observe(PurchaseObserver::class);
    }
}
