<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelRequestRepository
{
    public function create(array $data): TravelRequest
    {
        return TravelRequest::query()->create($data);
    }

    public function update(TravelRequest $travelRequest, array $data): TravelRequest
    {
        $travelRequest->fill($data);
        $travelRequest->save();

        return $travelRequest;
    }

    public function findOrFail(int $id): TravelRequest
    {
        return TravelRequest::query()
            ->with(['user', 'destinationAirport'])
            ->findOrFail($id);
    }

    /**
     * @return LengthAwarePaginator<int, TravelRequest>
     */
    public function listWithFilters(User $user, array $filters): LengthAwarePaginator
    {
        $query = TravelRequest::query()
            ->with(['user', 'destinationAirport']);

        if ($user->role !== UserRole::Admin) {
            $query->where('user_id', $user->id);
        }

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['destination_airport_id'])) {
            $query->where('destination_airport_id', $filters['destination_airport_id']);
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        if (! empty($filters['travel_from'])) {
            $query->whereDate('departure_date', '>=', $filters['travel_from']);
        }

        if (! empty($filters['travel_to'])) {
            $query->whereDate('return_date', '<=', $filters['travel_to']);
        }
    }
}
