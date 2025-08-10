<?php

namespace App\Services\Supplier;

use App\DTOS\HotelData; // Fixed: was DTOs

class SupplierDService extends BaseSupplierService
{
    public function __construct()
    {
        parent::__construct('supplier_d', config('services.hotels_suppliers.supplier_d', 'http://localhost:8004'));
    }

    public function mapResponseToDTOs(array $data): array
    {
        if (!isset($data['items']) || !is_array($data['items'])) {
            return [];
        }

        return collect($data['items'])->map(fn($hotel) => $this->createHotelData($hotel))->toArray();
    }
}
