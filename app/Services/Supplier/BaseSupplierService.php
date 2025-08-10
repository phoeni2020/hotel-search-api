<?php

namespace App\Services\Supplier;

use App\DTOS\HotelData;
use App\DTOS\HotelSearchFilter;
use App\Interfaces\SupplierInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseSupplierService implements SupplierInterface
{
    protected string $supplierName;
    protected string $baseUrl;

    public function __construct(string $supplierName, string $baseUrl)
    {
        $this->supplierName = $supplierName;
        $this->baseUrl = $baseUrl;
    }

    public function url(HotelSearchFilter $filters): string
    {
        return $this->baseUrl . '/search';
    }

    public function options(HotelSearchFilter $filters): array
    {
        return [
            'query' => $this->buildQueryParameters($filters),
            'timeout' => 10,
        ];
    }

    protected function buildQueryParameters(HotelSearchFilter $filters): array
    {
        $params = [
            'location' => $filters->location,
            'check_in' => $filters->checkIn,
            'check_out' => $filters->checkOut,
        ];

        if ($filters->guests) {
            $params['guests'] = $filters->guests;
        }

        if ($filters->minPrice) {
            $params['min_price'] = $filters->minPrice;
        }

        if ($filters->maxPrice) {
            $params['max_price'] = $filters->maxPrice;
        }

        return $params;
    }

    public function search(HotelSearchFilter $filters): array
    {
        try {
            $response = Http::timeout(10)->get($this->url($filters), $this->buildQueryParameters($filters));
            
            if ($response->successful()) {
                return $this->mapResponseToDTOs($response->json());
            }

            Log::warning("Supplier {$this->supplierName} request failed", [
                'status' => $response->status(),
                'response' => $response->body(),
                'filters' => $filters->toArray()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error("Supplier {$this->supplierName} request exception", [
                'message' => $e->getMessage(),
                'filters' => $filters->toArray()
            ]);

            return [];
        }
    }

    abstract public function mapResponseToDTOs(array $data): array;

    protected function createHotelData(array $hotel): HotelData
    {
        return new HotelData(
            $hotel['name'] ?? '',
            $hotel['location'] ?? '',
            (float)($hotel['price_per_night'] ?? 0),
            (int)($hotel['available_rooms'] ?? 0),
            isset($hotel['rating']) ? (float)$hotel['rating'] : null,
            $this->supplierName
        );
    }
}
