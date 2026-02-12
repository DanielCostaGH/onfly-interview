<?php

namespace App\Services;

use App\Models\TravelRequestLog;
use App\Repositories\TravelRequestLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelRequestLogService
{
    public function __construct(
        private readonly TravelRequestLogRepository $travelRequestLogRepository
    ) {}

    /**
     * List logs with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, TravelRequestLog>
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->travelRequestLogRepository->listWithFilters($filters);
    }
}
