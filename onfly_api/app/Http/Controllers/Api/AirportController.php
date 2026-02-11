<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirportResource;
use App\Services\AirportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AirportController extends Controller
{
    /**
     * List airports.
     */
    public function index(AirportService $airportService): AnonymousResourceCollection
    {
        return AirportResource::collection($airportService->list());
    }
}
