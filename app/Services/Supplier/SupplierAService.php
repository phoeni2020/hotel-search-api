<?php

namespace App\Services\Supplier;

use App\DTOS\HotelData; // Fixed: was DTOs

class SupplierAService extends BaseSupplierService
{
    public function __construct()
    {
        parent::__construct('supplier_a', config('services.hotels_suppliers.supplier_a', 'http://localhost:8001'));
    }

    public function mapResponseToDTOs(array $data): array
    {
        if (!isset($data['hotels']) || !is_array($data['hotels'])) {
            return [];
        }

        return collect($data['hotels'])->map(fn($hotel) => $this->createHotelData($hotel))->toArray();
    }
}
