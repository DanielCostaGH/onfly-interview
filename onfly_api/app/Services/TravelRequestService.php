<?php

namespace App\Services;

use App\Enums\TravelStatus;
use App\Models\TravelRequest;
use App\Models\TravelRequestLog;
use App\Models\User;
use App\Notifications\TravelRequestStatusNotification;
use App\Repositories\TravelRequestRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TravelRequestService
{
    public function __construct(
        private readonly TravelRequestRepository $travelRequestRepository
    ) {}

    /**
     * List travel requests with filters and scope based on user role.
     *
     * @return LengthAwarePaginator<int, TravelRequest>
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        return $this->travelRequestRepository->listWithFilters($user, $filters);
    }

    /**
     * Create a new travel request for the authenticated user.
     */
    public function create(User $user, array $data): TravelRequest
    {
        return DB::transaction(function () use ($user, $data) {
            $tempCode = 'TMP-'.Str::uuid();

            $travelRequest = $this->travelRequestRepository->create([
                'code' => $tempCode,
                'user_id' => $user->id,
                'destination_airport_id' => $data['destination_airport_id'],
                'departure_date' => $data['departure_date'],
                'return_date' => $data['return_date'],
                'status' => TravelStatus::Requested->value,
            ]);

            $code = $this->generateCode($travelRequest->id);
            $this->travelRequestRepository->update($travelRequest, ['code' => $code]);

            return $this->travelRequestRepository->findOrFail($travelRequest->id);
        });
    }

    /**
     * Retrieve a travel request with relations.
     */
    public function findOrFail(int $id): TravelRequest
    {
        return $this->travelRequestRepository->findOrFail($id);
    }

    /**
     * Update the request status and notify the owner.
     *
     * @throws ValidationException
     */
    public function update(User $user, TravelRequest $travelRequest, string $newStatus): TravelRequest
    {
        $currentStatus = $travelRequest->status;

        if ($currentStatus !== TravelStatus::Requested) {
            throw ValidationException::withMessages([
                'status' => ['Apenas pedidos em análise podem ser alterados.'],
            ]);
        }

        if (! in_array($newStatus, [TravelStatus::Approved->value, TravelStatus::Canceled->value], true)) {
            throw ValidationException::withMessages([
                'status' => ['Status inválido.'],
            ]);
        }

        return DB::transaction(function () use ($user, $travelRequest, $newStatus, $currentStatus) {
            $this->travelRequestRepository->update($travelRequest, [
                'status' => $newStatus,
            ]);

            TravelRequestLog::query()->create([
                'travel_request_id' => $travelRequest->id,
                'changed_by_user_id' => $user->id,
                'from_status' => $currentStatus->value,
                'to_status' => $newStatus,
                'created_at' => now(),
            ]);

            $travelRequest->loadMissing(['user', 'destinationAirport']);
            $travelRequest->user->notify(
                new TravelRequestStatusNotification(
                    $travelRequest,
                    $currentStatus->value,
                    $newStatus
                )
            );

            return $travelRequest;
        });
    }

    private function generateCode(int $id): string
    {
        $year = now()->year;
        $sequence = str_pad((string) $id, 6, '0', STR_PAD_LEFT);

        return "TRV-{$year}-{$sequence}";
    }
}
