<?php

namespace App\Services\Supplier;

use App\DTOS\HotelData;

class SupplierCService extends BaseSupplierService
{
    public function __construct()
    {
        parent::__construct('supplier_c', config('services.hotels_suppliers.supplier_c', 'http://localhost:8003'));
    }

    public function mapResponseToDTOs(array $data): array
    {
        if (!isset($data['data']) || !is_array($data['data'])) {
            return [];
        }

        return collect($data['data'])->map(fn($hotel) => $this->createHotelData($hotel))->toArray();
    }
}
