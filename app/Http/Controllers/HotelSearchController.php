<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelSearchRequest;
use App\Services\Hotel\HotelSearchService;
use Illuminate\Http\Request;

class HotelSearchController extends Controller
{
    /**
     * @param Request $request
     * @param HotelSearchService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(HotelSearchRequest $hotelSearchServiceData, HotelSearchService $service)
    {
        return response()->json($service->search($hotelSearchServiceData->array()));
    }
}

