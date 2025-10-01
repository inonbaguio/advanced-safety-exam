<?php

namespace OrderManagement\Providers;

use Illuminate\Support\ServiceProvider;
use OrderManagement\Repositories\OrderRepository;
use OrderManagement\Repositories\ProductRepository;
use OrderManagement\Repositories\WorkflowRepository;
use OrderManagement\Services\OrderService;
use OrderManagement\Services\PermissionService;
use OrderManagement\Services\WorkflowService;
use OrderManagement\Services\StatusCalculator;
use OrderManagement\Policies\OrderPolicy;
use OrderManagement\Models\Order;
use Illuminate\Support\Facades\Gate;

class OrderManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/order-management.php',
            'order-management'
        );

        // Register repositories
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(WorkflowRepository::class);

        // Register services
        $this->app->singleton(StatusCalculator::class);
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(WorkflowService::class);
        $this->app->singleton(OrderService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/order-management.php' => config_path('order-management.php'),
        ], 'order-management-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'order-management-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Register policies
        Gate::policy(Order::class, OrderPolicy::class);
    }
}
