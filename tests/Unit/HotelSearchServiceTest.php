<?php

namespace Tests\Unit;

use App\DTOS\HotelData;
use App\DTOS\HotelSearchFilter;
use App\Services\Hotel\HotelSearchService;
use App\Services\Supplier\SupplierAService;
use App\Services\Supplier\SupplierBService;
use App\Services\Supplier\SupplierCService;
use App\Services\Supplier\SupplierDService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotelSearchServiceTest extends TestCase
{
    private HotelSearchService $service;
    private array $suppliers;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->suppliers = [
            new SupplierAService(),
            new SupplierBService(),
            new SupplierCService(),
            new SupplierDService(),
        ];
        
        $this->service = new HotelSearchService(...$this->suppliers);
    }

    public function test_search_hotels_returns_merged_results()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2,
            null,
            null,
            null
        );

        $results = $this->service->searchHotels($filters);

        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));
        
        // Check that results are properly structured
        foreach ($results as $hotel) {
            $this->assertIsArray($hotel);
            $this->assertArrayHasKey('name', $hotel);
            $this->assertArrayHasKey('location', $hotel);
            $this->assertArrayHasKey('price_per_night', $hotel);
            $this->assertArrayHasKey('available_rooms', $hotel);
            $this->assertArrayHasKey('rating', $hotel);
            $this->assertArrayHasKey('source', $hotel);
        }
    }

    public function test_search_hotels_deduplicates_duplicate_hotels()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2
        );

        $results = $this->service->searchHotels($filters);

        // Check that Grand Hotel appears only once (it exists in multiple suppliers)
        $grandHotelCount = collect($results)->where('name', 'Grand Hotel')->count();
        $this->assertEquals(1, $grandHotelCount);

        // Check that the Grand Hotel returned has the best price (140 from supplier B)
        $grandHotel = collect($results)->where('name', 'Grand Hotel')->first();
        $this->assertEquals(140.00, $grandHotel['price_per_night']);
    }

    public function test_search_hotels_applies_price_filters()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2,
            150.00, // min_price
            200.00  // max_price
        );

        $results = $this->service->searchHotels($filters);

        $this->assertGreaterThan(0, count($results));
        
        foreach ($results as $hotel) {
            $this->assertGreaterThanOrEqual(150.00, $hotel['price_per_night']);
            $this->assertLessThanOrEqual(200.00, $hotel['price_per_night']);
        }
    }

    public function test_search_hotels_sorts_by_price_when_specified()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2,
            null,
            null,
            'pricePerNight'
        );

        $results = $this->service->searchHotels($filters);

        $prices = collect($results)->pluck('price_per_night')->toArray();
        $sortedPrices = $prices;
        sort($sortedPrices);

        $this->assertEquals($sortedPrices, $prices);
    }

    public function test_search_hotels_sorts_by_rating_when_specified()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2,
            null,
            null,
            'rating'
        );

        $results = $this->service->searchHotels($filters);

        $ratings = collect($results)->pluck('rating')->filter()->toArray();
        $sortedRatings = $ratings;
        rsort($sortedRatings);

        $this->assertEquals($sortedRatings, $ratings);
    }

    public function test_search_hotels_filters_out_hotels_with_no_available_rooms()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2
        );

        $results = $this->service->searchHotels($filters);

        $this->assertGreaterThan(0, count($results));
        
        foreach ($results as $hotel) {
            $this->assertGreaterThan(0, $hotel['available_rooms']);
        }
    }

    public function test_search_hotels_handles_supplier_failures_gracefully()
    {
        // Mock one supplier to fail
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

        $filters = new HotelSearchFilter(
            'Test City',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2
        );

        $results = $this->service->searchHotels($filters);

        // Should still return results from working suppliers
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));
    }

    public function test_search_hotels_returns_empty_array_when_all_suppliers_fail()
    {
        // Mock all suppliers to fail - use Http::fake() with fresh mocks
        Http::fake([
            'localhost:8001/*' => Http::response([], 500),
            'localhost:8002/*' => Http::response([], 500),
            'localhost:8003/*' => Http::response([], 500),
            'localhost:8004/*' => Http::response([], 500),
        ]);

        $filters = new HotelSearchFilter(
            'Test City',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2
        );

        $results = $this->service->searchHotels($filters);

        $this->assertIsArray($results);
        $this->assertEquals(0, count($results));
    }

    public function test_hotel_data_structure_is_correct()
    {
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
                        'name' => 'Grand Hotel',
                        'location' => 'New York, USA',
                        'price_per_night' => 145.00,
                        'available_rooms' => 1,
                        'rating' => 4.5
                    ]
                ]
            ], 200),
        ]);

        $filters = new HotelSearchFilter(
            'New York',
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2
        );

        $results = $this->service->searchHotels($filters);

        $this->assertGreaterThan(0, count($results));
        
        if (count($results) > 0) {
            $hotel = $results[0];
            
            $this->assertIsArray($hotel);
            $this->assertArrayHasKey('name', $hotel);
            $this->assertArrayHasKey('location', $hotel);
            $this->assertArrayHasKey('price_per_night', $hotel);
            $this->assertArrayHasKey('available_rooms', $hotel);
            $this->assertArrayHasKey('rating', $hotel);
            $this->assertArrayHasKey('source', $hotel);
            
            $this->assertIsString($hotel['name']);
            $this->assertIsString($hotel['location']);
            $this->assertIsNumeric($hotel['price_per_night']);
            $this->assertIsInt($hotel['available_rooms']);
            $this->assertTrue(is_null($hotel['rating']) || is_numeric($hotel['rating']));
            $this->assertIsString($hotel['source']);
        }
    }
}
