<?php

use App\Enums\TravelStatus;
use App\Enums\UserRole;
use App\Models\Airport;
use App\Models\TravelRequest;
use App\Models\TravelRequestLog;
use App\Models\User;
use App\Repositories\TravelRequestRepository;
use App\Services\TravelRequestService;
use Illuminate\Validation\ValidationException;

test('creates a travel request with code and requested status', function () {
    $airport = Airport::query()->create([
        'iata_code' => 'TST',
        'name' => 'Test Airport',
        'city' => 'Test City',
        'state' => 'TS',
        'country' => 'Brasil',
    ]);

    $user = User::factory()->create();

    $service = new TravelRequestService(new TravelRequestRepository());

    $travelRequest = $service->create($user, [
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-03-15',
        'return_date' => '2026-03-20',
    ]);

    expect($travelRequest->status)->toBe(TravelStatus::Requested)
        ->and($travelRequest->code)->toStartWith('TRV-'.now()->year.'-')
        ->and($travelRequest->destination_airport_id)->toBe($airport->id)
        ->and($travelRequest->user_id)->toBe($user->id);
});

test('updates status, logs and creates notification', function () {
    $airport = Airport::query()->create([
        'iata_code' => 'TS2',
        'name' => 'Test Airport 2',
        'city' => 'Test City',
        'state' => 'TS',
        'country' => 'Brasil',
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $user = User::factory()->create();

    $travelRequest = TravelRequest::query()->create([
        'code' => 'TRV-2026-000999',
        'user_id' => $user->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-03-15',
        'return_date' => '2026-03-20',
        'status' => TravelStatus::Requested->value,
    ]);

    $service = new TravelRequestService(new TravelRequestRepository());

    $updated = $service->update($admin, $travelRequest, TravelStatus::Approved->value);

    expect($updated->status)->toBe(TravelStatus::Approved);

    $log = TravelRequestLog::query()->first();
    expect($log)->not()->toBeNull()
        ->and($log->from_status)->toBe(TravelStatus::Requested)
        ->and($log->to_status)->toBe(TravelStatus::Approved)
        ->and($log->changed_by_user_id)->toBe($admin->id);

    $notification = $user->notifications()->first();
    expect($notification)->not()->toBeNull()
        ->and($notification->data['to_status'])->toBe(TravelStatus::Approved->value);
});

test('prevents status update when request is not requested', function () {
    $airport = Airport::query()->create([
        'iata_code' => 'TS3',
        'name' => 'Test Airport 3',
        'city' => 'Test City',
        'state' => 'TS',
        'country' => 'Brasil',
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $user = User::factory()->create();

    $travelRequest = TravelRequest::query()->create([
        'code' => 'TRV-2026-000888',
        'user_id' => $user->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-03-15',
        'return_date' => '2026-03-20',
        'status' => TravelStatus::Canceled->value,
    ]);

    $service = new TravelRequestService(new TravelRequestRepository());

    expect(fn () => $service->update($admin, $travelRequest, TravelStatus::Approved->value))
        ->toThrow(ValidationException::class);
});
