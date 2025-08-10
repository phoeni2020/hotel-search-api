<?php

namespace App\DTOS;

class HotelSearchFilter
{
    public function __construct(
        public string $location,
        public string $checkIn,
        public string $checkOut,
        public ?int $guests = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public ?string $sortBy = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['location'],
            $data['check_in'],
            $data['check_out'],
            $data['guests'] ?? null,
            $data['min_price'] ?? null,
            $data['max_price'] ?? null,
            $data['sort_by'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'location' => $this->location,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'guests' => $this->guests,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'sort_by' => $this->sortBy,
        ];
    }
}
