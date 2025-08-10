<?php

namespace App\Providers;

use App\Services\Hotel\HotelSearchService;
use App\Services\Supplier\SupplierAService;
use App\Services\Supplier\SupplierBService;
use App\Services\Supplier\SupplierCService;
use App\Services\Supplier\SupplierDService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register supplier services
        $this->app->singleton(SupplierAService::class);
        $this->app->singleton(SupplierBService::class);
        $this->app->singleton(SupplierCService::class);
        $this->app->singleton(SupplierDService::class);

        // Register HotelSearchService with all suppliers
        $this->app->singleton(HotelSearchService::class, function ($app) {
            return new HotelSearchService(
                $app->make(SupplierAService::class),
                $app->make(SupplierBService::class),
                $app->make(SupplierCService::class),
                $app->make(SupplierDService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enable strict mode for database operations if needed
        // Model::shouldBeStrict();
    }
}
