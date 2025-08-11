<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotelSearchApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock HTTP responses for suppliers
        Http::fake([
            'localhost:8001/*' => Http::response([
                'hotels' => [
                    [
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 150.00,
                        'available_rooms' => 3,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
            'localhost:8002/*' => Http::response([
                'results' => [
                    [
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 140.00,
                        'available_rooms' => 2,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
            'localhost:8003/*' => Http::response([
                'data' => [
                    [
                        'name' => 'Seaside Resort',
                        'location' => 'Miami, USA',
                        'price_per_night' => 180.00,
                        'available_rooms' => 3,
                        'rating' => 4.2
                    ]
                ]
            ], 200),
            'localhost:8004/*' => Http::response([
                'items' => [
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

    public function test_hotel_search_endpoint_returns_successful_response()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => now()->addDays(1)->format('Y-m-d'), // Fixed: future date
            'check_out' => now()->addDays(6)->format('Y-m-d'), // Fixed: future date
            'guests' => 2
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'name',
                            'location',
                            'price_per_night',
                            'available_rooms',
                            'rating',
                            'source'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'filters'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    public function test_hotel_search_validates_required_fields()
    {
        $response = $this->getJson('/api/hotels/search');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['location', 'check_in', 'check_out']);
    }

    public function test_hotel_search_validates_date_constraints()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => '2023-01-15', // Past date
            'check_out' => '2024-01-20',
            'guests' => 2
        ]));

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['check_in']);

        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => '2024-01-20',
            'check_out' => '2024-01-15', // Check-out before check-in
            'guests' => 2
        ]));

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['check_out']);
    }

    public function test_hotel_search_validates_guest_count()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => '2024-01-15',
            'check_out' => '2024-01-20',
            'guests' => 0 // Invalid guest count
        ]));

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['guests']);
    }

    public function test_hotel_search_validates_price_range()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => '2024-01-15',
            'check_out' => '2024-01-20',
            'guests' => 2,
            'min_price' => 200,
            'max_price' => 100 // Max price less than min price
        ]));

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['max_price']);
    }

    public function test_hotel_search_with_price_filters()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => now()->addDays(1)->format('Y-m-d'), // Fixed: future date
            'check_out' => now()->addDays(6)->format('Y-m-d'), // Fixed: future date
            'guests' => 2,
            'min_price' => 150,
            'max_price' => 200
        ]));

        $response->assertStatus(200);
        
        $hotels = $response->json('data');
        foreach ($hotels as $hotel) {
            $this->assertGreaterThanOrEqual(150, $hotel['price_per_night']);
            $this->assertLessThanOrEqual(200, $hotel['price_per_night']);
        }
    }

    public function test_hotel_search_with_sorting()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => now()->addDays(1)->format('Y-m-d'), // Fixed: future date
            'check_out' => now()->addDays(6)->format('Y-m-d'), // Fixed: future date
            'guests' => 2,
            'sort_by' => 'pricePerNight'
        ]));

        $response->assertStatus(200);
        
        $hotels = $response->json('data');
        $prices = collect($hotels)->pluck('price_per_night')->toArray();
        $sortedPrices = $prices;
        sort($sortedPrices);
        
        $this->assertEquals($sortedPrices, $prices);
    }

    public function test_hotel_search_deduplicates_duplicate_hotels()
    {
        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'New York',
            'check_in' => now()->addDays(1)->format('Y-m-d'), // Fixed: future date
            'check_out' => now()->addDays(6)->format('Y-m-d'), // Fixed: future date
            'guests' => 2
        ]));

        $response->assertStatus(200);
        
        $hotels = $response->json('data');
        $grandHotelCount = collect($hotels)->where('name', 'Grand Hotel')->count();
        
        // Grand Hotel should appear only once (deduplicated)
        $this->assertEquals(1, $grandHotelCount);
        
        // Should return the one with the best price (140 from supplier B)
        $grandHotel = collect($hotels)->where('name', 'Grand Hotel')->first();
        $this->assertEquals(140.00, $grandHotel['price_per_night']);
    }

    public function test_hotel_search_handles_supplier_failures()
    {
        // Mock some suppliers to fail
        Http::fake([
            'localhost:8001/*' => Http::response([], 500),
            'localhost:8002/*' => Http::response([
                'results' => [
                    [
                        'name' => 'Test Hotel',
                        'location' => 'Test City',
                        'price_per_night' => 100.00,
                        'available_rooms' => 2,
                        'rating' => 4.0
                    ]
                ]
            ], 200),
            'localhost:8003/*' => Http::response([], 500),
            'localhost:8004/*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/hotels/search?' . http_build_query([
            'location' => 'Test City',
            'check_in' => now()->addDays(1)->format('Y-m-d'), // Fixed: future date
            'check_out' => now()->addDays(6)->format('Y-m-d'), // Fixed: future date
            'guests' => 2
        ]));

        $response->assertStatus(200);
        
        // Should still return results from working suppliers
        $this->assertGreaterThan(0, $response->json('meta.total'));
    }
}
