<?php

namespace App\Services;

use App\Models\TravelRequestLog;
use App\Repositories\TravelRequestLogRepository;
use Illuminate\Database\Eloquent\Collection;

class TravelRequestLogService
{
    public function __construct(
        private readonly TravelRequestLogRepository $travelRequestLogRepository
    ) {}

    /**
     * List logs with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, TravelRequestLog>
     */
    public function list(array $filters): Collection
    {
        return $this->travelRequestLogRepository->listWithFilters($filters);
    }
}
