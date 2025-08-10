<?php

namespace App\Inerfaces;

use App\DTOS\HotelSearchFilter;

interface SupplierInterface
{
    public function url(HotelSearchFilter $filters): string;
    public function options(HotelSearchFilter $filters): array;
    public function mapResponseToDTOs(array $data): array;}
