<?php

use App\Enums\TravelStatus;
use App\Models\Airport;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('login endpoint is rate limited', function () {
    User::factory()->create([
        'email' => 'ratelimit@empresa.com',
        'password' => Hash::make('secret123'),
    ]);

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->postJson('/api/auth/login', [
            'email' => 'ratelimit@empresa.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    $response = $this->postJson('/api/auth/login', [
        'email' => 'ratelimit@empresa.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);

    $retryAfter = (int) $response->headers->get('Retry-After', '0');
    expect($retryAfter)->toBeGreaterThanOrEqual(295);
});

test('issued api token has expiration date', function () {
    config(['sanctum.expiration' => 120]);

    $user = User::factory()->create([
        'email' => 'token@empresa.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'token@empresa.com',
        'password' => 'secret123',
    ])->assertOk();

    $plainTextToken = $response->json('token');
    $tokenId = (int) explode('|', $plainTextToken)[0];
    $token = $user->tokens()->findOrFail($tokenId);

    expect($token->expires_at)->not()->toBeNull();
});

test('travel requests index uses backend pagination', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $airport = Airport::query()->create([
        'iata_code' => 'PAG',
        'name' => 'Pagination Airport',
        'city' => 'Pag City',
        'state' => 'PG',
        'country' => 'Brasil',
    ]);

    for ($i = 1; $i <= 12; $i++) {
        TravelRequest::query()->create([
            'code' => sprintf('TRV-2026-%06d', $i),
            'user_id' => $user->id,
            'destination_airport_id' => $airport->id,
            'departure_date' => '2026-03-15',
            'return_date' => '2026-03-20',
            'status' => TravelStatus::Requested->value,
        ]);
    }

    $response = $this->getJson('/api/travel-requests?per_page=5&page=2')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'per_page', 'total'],
        ]);

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.current_page'))->toBe(2)
        ->and($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.total'))->toBe(12);
});

test('cors preflight allows configured frontend origin', function () {
    $response = $this->withHeaders([
        'Origin' => 'http://localhost:9000',
        'Access-Control-Request-Method' => 'POST',
    ])->options('/api/auth/login');

    $response->assertSuccessful()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:9000');
});
