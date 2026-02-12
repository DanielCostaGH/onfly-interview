<?php

namespace App\Repositories;

use App\Models\TravelRequestLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelRequestLogRepository
{
    /**
     * List logs with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, TravelRequestLog>
     */
    public function listWithFilters(array $filters): LengthAwarePaginator
    {
        $query = TravelRequestLog::query()
            ->with(['travelRequest', 'changedBy']);

        if (! empty($filters['status'])) {
            $status = $filters['status'];
            $query->where(function ($builder) use ($status) {
                $builder->where('from_status', $status)
                    ->orWhere('to_status', $status);
            });
        }

        if (! empty($filters['user_id'])) {
            $query->where('changed_by_user_id', $filters['user_id']);
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        if (! empty($filters['code'])) {
            $code = $filters['code'];
            $query->whereHas('travelRequest', function ($builder) use ($code) {
                $builder->where('code', 'like', "%{$code}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }
}
