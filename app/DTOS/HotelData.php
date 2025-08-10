<?php

namespace App\DTOS;

class HotelData
{
    public function __construct(
        public string $name,
        public string $location,
        public float $pricePerNight,
        public int $availableRooms,
        public ?float $rating,
        public string $source
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'location' => $this->location,
            'price_per_night' => $this->pricePerNight,
            'available_rooms' => $this->availableRooms,
            'rating' => $this->rating,
            'source' => $this->source,
        ];
    }
}
