<?php

namespace App\Services\Supplier;

use App\DTOS\HotelData; // Fixed: was DTOs

class SupplierBService extends BaseSupplierService
{
    public function __construct()
    {
        parent::__construct('supplier_b', config('services.hotels_suppliers.supplier_b', 'http://localhost:8002'));
    }

    public function mapResponseToDTOs(array $data): array
    {
        if (!isset($data['results']) || !is_array($data['results'])) {
            return [];
        }

        return collect($data['results'])->map(fn($hotel) => $this->createHotelData($hotel))->toArray();
    }
}
