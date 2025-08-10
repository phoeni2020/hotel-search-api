<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelSearchRequest;
use App\Services\Hotel\HotelSearchService;
use App\DTOS\HotelSearchFilter;
use Illuminate\Http\JsonResponse;

class HotelSearchController extends Controller
{
    public function __construct(
        private HotelSearchService $hotelSearchService
    ) {}

    /**
     * Search for hotels across multiple suppliers
     *
     * @param HotelSearchRequest $request
     * @return JsonResponse
     */
    public function search(HotelSearchRequest $request): JsonResponse
    {
        try {
            $filters = HotelSearchFilter::fromArray($request->validated());
            $hotels = $this->hotelSearchService->searchHotels($filters);

            return response()->json([
                'success' => true,
                'data' => $hotels,
                'meta' => [
                    'total' => count($hotels),
                    'filters' => $filters->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for hotels',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

