<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpMockServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'testing' || config('app.debug')) {
            $this->mockSupplierResponses();
        }
    }

    protected function mockSupplierResponses(): void
    {
        // mock data for Supplier A response
        Http::fake([
            'localhost:8001/*' => Http::response([
                'hotels' => [
                    [
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 150.00,
                        'available_rooms' => 3,
                        'rating' => 4.5
                    ],
                    [
                        'name' => 'Seaside Resort',
                        'location' => 'Miami, USA',
                        'price_per_night' => 200.00,
                        'available_rooms' => 5,
                        'rating' => 4.2
                    ]
                ]
            ], 200),

            // Mock Supplier B responses
            'localhost:8002/*' => Http::response([
                'results' => [
                    [
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 140.00,
                        'available_rooms' => 2,
                        'rating' => 4.5
                    ],
                    [
                        'name' => 'Mountain View Lodge',
                        'location' => 'Denver, USA',
                        'price_per_night' => 120.00,
                        'available_rooms' => 4,
                        'rating' => 4.0
                    ]
                ]
            ], 200),

            // Mock Supplier C responses
            'localhost:8003/*' => Http::response([
                'data' => [
                    [
                        'name' => 'Seaside Resort',
                        'location' => 'Miami, USA',
                        'price_per_night' => 180.00,
                        'available_rooms' => 3,
                        'rating' => 4.2
                    ],
                    [
                        'name' => 'City Center Hotel',
                        'location' => 'Chicago, USA',
                        'price_per_night' => 160.00,
                        'available_rooms' => 6,
                        'rating' => 4.3
                    ]
                ]
            ], 200),

            // Mock Supplier D responses
            'localhost:8004/*' => Http::response([
                'items' => [
                    [
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ],
                    [
                        'name' => 'Luxury Inn',
                        'location' => 'Los Angeles, USA',
                        'price_per_night' => 250.00,
                        'available_rooms' => 2,
                        'rating' => 4.7
                    ]
                ]
            ], 200),
        ]);
    }
}
