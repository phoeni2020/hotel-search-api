<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelSearchRequest;
use App\Services\Hotel\HotelSearchService;
use Illuminate\Http\Request;

class HotelSearchController extends Controller
{
    /**
     * @param HotelSearchRequest $request
     * @param HotelSearchService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(HotelSearchRequest $hotelSearchServiceData, HotelSearchService $service)
    {
        dd($hotelSearchServiceData);
        return response()->json($service->search($hotelSearchServiceData->array()));
    }
}

