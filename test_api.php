<?php

require_once 'vendor/autoload.php';

use App\DTOS\HotelSearchFilter;
use App\Services\Hotel\HotelSearchService;
use App\Services\Supplier\SupplierAService;
use App\Services\Supplier\SupplierBService;
use App\Services\Supplier\SupplierCService;
use App\Services\Supplier\SupplierDService;

// Create supplier instances
$suppliers = [
    new SupplierAService(),
    new SupplierBService(),
    new SupplierCService(),
    new SupplierDService(),
];

// Create service
$service = new HotelSearchService(...$suppliers);

// Create filters
$filters = new HotelSearchFilter(
    'New York',
    '2024-01-15',
    '2024-01-20',
    2,
    100.0,
    300.0,
    'pricePerNight'
);

// Search for hotels
echo "Searching for hotels...\n";
$startTime = microtime(true);

try {
    $hotels = $service->searchHotels($filters);
    $executionTime = (microtime(true) - $startTime) * 1000;
    
    echo "Found " . count($hotels) . " hotels in " . round($executionTime, 2) . "ms\n\n";
    
    foreach ($hotels as $hotel) {
        echo "Hotel: {$hotel['name']}\n";
        echo "Location: {$hotel['location']}\n";
        echo "Price: \${$hotel['price_per_night']}/night\n";
        echo "Available Rooms: {$hotel['available_rooms']}\n";
        echo "Rating: {$hotel['rating']}\n";
        echo "Source: {$hotel['source']}\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
