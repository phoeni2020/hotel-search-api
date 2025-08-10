<?php

namespace App\Services\Hotel;

use App\DTOS\HotelData;
use App\DTOS\HotelSearchFilter;
use App\Interfaces\SupplierInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HotelSearchService
{
    protected array $suppliers;

    public function __construct(SupplierInterface ...$suppliers)
    {
        $this->suppliers = $suppliers;
    }

    public function searchHotels(HotelSearchFilter $filters): array
    {
        $startTime = microtime(true);

        // Fetch from all suppliers in parallel
        $allHotels = $this->fetchFromAllSuppliers($filters);

        // Apply filters
        $filteredHotels = $this->applyFilters($allHotels, $filters);

        // Deduplicate and pick best deals
        $deduplicatedHotels = $this->deduplicateAndPickBestDeal($filteredHotels);

        // Sort results
        $sortedHotels = $this->sortHotels($deduplicatedHotels, $filters->sortBy);

        $executionTime = microtime(true) - $startTime;
        Log::info('Hotel search completed', [
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'total_hotels' => count($allHotels),
            'filtered_hotels' => count($filteredHotels),
            'final_results' => count($sortedHotels)
        ]);

        // Convert DTOs to arrays before returning
        return $sortedHotels->map(fn(HotelData $hotel) => $hotel->toArray())->toArray();
    }

    protected function fetchFromAllSuppliers(HotelSearchFilter $filters): Collection
    {
        $allHotels = collect();

        // Use Http::pool for parallel requests
        $responses = Http::pool(function ($pool) use ($filters) {
            foreach ($this->suppliers as $key => $supplier) {
                $pool->as("supplier{$key}")->withOptions($supplier->options($filters))
                    ->get($supplier->url($filters));
            }
        });

        // Process responses
        foreach ($this->suppliers as $key => $supplier) {
            $alias = "supplier{$key}";
            if (isset($responses[$alias])) {
                try {
                    // Check if response is successful and not an exception
                    if (method_exists($responses[$alias], 'successful') && $responses[$alias]->successful()) {
                        $hotels = $supplier->mapResponseToDTOs($responses[$alias]->json());
                        $allHotels = $allHotels->merge($hotels);
                    } else {
                        Log::warning('Supplier request failed', [
                            'supplier' => get_class($supplier),
                            'status' => method_exists($responses[$alias], 'status') ? $responses[$alias]->status() : 'unknown'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process supplier response', [
                        'supplier' => get_class($supplier),
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning('Supplier response not found', [
                    'supplier' => get_class($supplier),
                    'alias' => $alias
                ]);
            }
        }

        return $allHotels;
    }

    protected function applyFilters(Collection $hotels, HotelSearchFilter $filters): Collection
    {
        return $hotels->filter(function (HotelData $hotel) use ($filters) {
            // filter by price range
            if ($filters->minPrice && $hotel->pricePerNight < $filters->minPrice)
                return false;

            if ($filters->maxPrice && $hotel->pricePerNight > $filters->maxPrice)
                return false;

            // filter using available rooms at least 1 room is available
            if ($hotel->availableRooms < 1)
                return false;

            return true;
        });
    }

    protected function deduplicateAndPickBestDeal(Collection $hotels): Collection
    {
        return $hotels
            ->groupBy(function (HotelData $hotel) {
                // group by hotel name and location to get duplicates
                return strtolower(trim($hotel->name)) . '|' . strtolower(trim($hotel->location));
            })
            ->map(function (Collection $group) {
                // each group get hotel with best price
                return $group->sortBy('pricePerNight')->first();
            })
            ->values();
    }

    protected function sortHotels(Collection $hotels, ?string $sortBy): Collection
    {
        if (!$sortBy) {
            // def sorting price lowest to highest
            return $hotels->sortBy('pricePerNight');
        }

        return match ($sortBy) {
            'pricePerNight' => $hotels->sortBy('pricePerNight'),
            'rating' => $hotels->sortByDesc('rating'),
            default => $hotels->sortBy('pricePerNight')
        };
    }
}
