<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OrderManagement\Providers\OrderManagementServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the Order Management package
        $this->app->register(OrderManagementServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
