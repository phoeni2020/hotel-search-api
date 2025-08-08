<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HotelSearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/hotels/search', [HotelSearchController::class, 'search']);
