<?php

use App\Enums\TravelStatus;
use App\Enums\UserRole;
use App\Models\Airport;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\TravelRequestRepository;

test('repository filters by status and respects user scope', function () {
    $airport = Airport::query()->create([
        'iata_code' => 'TST',
        'name' => 'Test Airport',
        'city' => 'Test City',
        'state' => 'TS',
        'country' => 'Brasil',
    ]);

    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    TravelRequest::query()->create([
        'code' => 'TRV-2026-000001',
        'user_id' => $userA->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-03-10',
        'return_date' => '2026-03-15',
        'status' => TravelStatus::Requested->value,
    ]);

    TravelRequest::query()->create([
        'code' => 'TRV-2026-000002',
        'user_id' => $userA->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-04-10',
        'return_date' => '2026-04-15',
        'status' => TravelStatus::Approved->value,
    ]);

    TravelRequest::query()->create([
        'code' => 'TRV-2026-000003',
        'user_id' => $userB->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-05-10',
        'return_date' => '2026-05-15',
        'status' => TravelStatus::Approved->value,
    ]);

    $repo = new TravelRequestRepository;

    $userResults = $repo->listWithFilters($userA, ['status' => TravelStatus::Approved->value]);
    expect($userResults)->toHaveCount(1);

    $adminResults = $repo->listWithFilters($admin, ['status' => TravelStatus::Approved->value]);
    expect($adminResults)->toHaveCount(2);
});
