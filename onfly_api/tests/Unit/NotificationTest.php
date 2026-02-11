<?php

use App\Enums\TravelStatus;
use App\Models\Airport;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusNotification;

test('user receives and marks notifications as read', function () {
    $airport = Airport::query()->create([
        'iata_code' => 'NTF',
        'name' => 'Notify Airport',
        'city' => 'Notify City',
        'state' => 'NT',
        'country' => 'Brasil',
    ]);

    $user = User::factory()->create();

    $travelRequest = TravelRequest::query()->create([
        'code' => 'TRV-2026-000123',
        'user_id' => $user->id,
        'destination_airport_id' => $airport->id,
        'departure_date' => '2026-03-15',
        'return_date' => '2026-03-20',
        'status' => TravelStatus::Requested->value,
    ]);

    $travelRequest->load('destinationAirport');

    $user->notify(new TravelRequestStatusNotification(
        $travelRequest,
        TravelStatus::Requested->value,
        TravelStatus::Approved->value
    ));

    expect($user->notifications()->count())->toBe(1)
        ->and($user->unreadNotifications()->count())->toBe(1);

    $notification = $user->notifications()->firstOrFail();
    $notification->markAsRead();

    expect($notification->fresh()->read_at)->not()->toBeNull()
        ->and($user->unreadNotifications()->count())->toBe(0);
});
