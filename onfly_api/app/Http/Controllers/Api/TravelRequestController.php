<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\IndexTravelRequest;
use App\Http\Requests\TravelRequest\StoreTravelRequestRequest;
use App\Http\Requests\TravelRequest\UpdateTravelRequestStatusRequest;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TravelRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TravelRequest::class, 'travelRequest');
    }

    /**
     * List travel requests with optional filters.
     */
    public function index(IndexTravelRequest $request, TravelRequestService $travelRequestService): AnonymousResourceCollection
    {
        $items = $travelRequestService->list($request->user(), $request->validated());

        return TravelRequestResource::collection($items);
    }

    /**
     * Create a travel request for the authenticated user.
     */
    public function store(StoreTravelRequestRequest $request, TravelRequestService $travelRequestService): TravelRequestResource
    {
        $travelRequest = $travelRequestService->create($request->user(), $request->validated());

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Show a specific travel request.
     */
    public function show(TravelRequest $travelRequest, TravelRequestService $travelRequestService): TravelRequestResource
    {
        $travelRequest = $travelRequestService->findOrFail($travelRequest->id);

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Update request status (admin only).
     */
    public function update(UpdateTravelRequestStatusRequest $request, TravelRequest $travelRequest,
        TravelRequestService $travelRequestService): TravelRequestResource
    {
        $updated = $travelRequestService->update(
            $request->user(),
            $travelRequest,
            $request->validated('status')
        );

        return new TravelRequestResource($updated);
    }
}
