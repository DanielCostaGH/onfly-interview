<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequestLog\IndexTravelRequestLogRequest;
use App\Http\Resources\TravelRequestLogResource;
use App\Models\TravelRequestLog;
use App\Services\TravelRequestLogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TravelRequestLogController extends Controller
{
    /**
     * List travel request logs (admin only).
     */
    public function index(
        IndexTravelRequestLogRequest $request,
        TravelRequestLogService $travelRequestLogService
    ): AnonymousResourceCollection {
        $this->authorize('viewAny', TravelRequestLog::class);

        $logs = $travelRequestLogService->list($request->validated());

        return TravelRequestLogResource::collection($logs);
    }
}
