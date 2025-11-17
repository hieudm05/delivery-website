<?php

namespace App\Providers;

use App\Models\Driver\Orders\OrderDelivery;
use App\Observers\OrderDeliveryObserver;
use Illuminate\Pagination\Paginator;
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
        //
        Paginator::useBootstrap();
        OrderDelivery::observe(OrderDeliveryObserver::class);
    }
}
