<?php

use App\Enums\TravelStatus;
use App\Enums\UserRole;
use App\Models\Airport;
use App\Models\TravelRequest;
use App\Models\TravelRequestLog;
use App\Models\User;
use App\Repositories\TravelRequestLogRepository;
use Illuminate\Support\Carbon;

test('log repository filters by status, user, code, and date range', function () {
    $airport = Airport::query()->create([
        'iata_code' => 'LOG',
        'name' => 'Log Airport',
        'city' => 'Log City',
        'state' => 'LG',
        'country' => 'Brasil',
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $user = User::factory()->create();

    $requestA = TravelRequest::query()->create([
        'code' => 'TRV-2026-000001',
        'user_id' => $user->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-03-15',
        'return_date' => '2026-03-20',
        'status' => TravelStatus::Requested->value,
    ]);

    $requestB = TravelRequest::query()->create([
        'code' => 'TRV-2026-000002',
        'user_id' => $user->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-04-15',
        'return_date' => '2026-04-20',
        'status' => TravelStatus::Requested->value,
    ]);

    TravelRequestLog::query()->create([
        'travel_request_id' => $requestA->id,
        'changed_by_user_id' => $admin->id,
        'from_status' => TravelStatus::Requested->value,
        'to_status' => TravelStatus::Approved->value,
        'created_at' => Carbon::parse('2026-02-10 10:00:00'),
    ]);

    TravelRequestLog::query()->create([
        'travel_request_id' => $requestB->id,
        'changed_by_user_id' => $admin->id,
        'from_status' => TravelStatus::Requested->value,
        'to_status' => TravelStatus::Canceled->value,
        'created_at' => Carbon::parse('2026-02-12 15:00:00'),
    ]);

    TravelRequestLog::query()->create([
        'travel_request_id' => $requestB->id,
        'changed_by_user_id' => $user->id,
        'from_status' => TravelStatus::Approved->value,
        'to_status' => TravelStatus::Canceled->value,
        'created_at' => Carbon::parse('2026-02-13 08:00:00'),
    ]);

    $repo = new TravelRequestLogRepository;

    $byStatus = $repo->listWithFilters(['status' => TravelStatus::Canceled->value]);
    expect($byStatus)->toHaveCount(2);

    $byUser = $repo->listWithFilters(['user_id' => $admin->id]);
    expect($byUser)->toHaveCount(2);

    $byCode = $repo->listWithFilters(['code' => '000001']);
    expect($byCode)->toHaveCount(1)
        ->and($byCode->first()->travel_request_id)->toBe($requestA->id);

    $byDate = $repo->listWithFilters([
        'created_from' => '2026-02-11',
        'created_to' => '2026-02-12',
    ]);
    expect($byDate)->toHaveCount(1);
});
