<?php

namespace Database\Seeders;

use App\Enums\TravelStatus;
use App\Models\Airport;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TravelRequestSeeder extends Seeder
{
    /**
     * Seed travel requests for demo users.
     */
    public function run(): void
    {
        $users = User::query()
            ->whereIn('email', ['maria@empresa.com', 'joao@empresa.com'])
            ->get()
            ->keyBy('email');

        if ($users->count() < 2) {
            return;
        }

        $airportIds = Airport::query()->pluck('id')->all();
        if (count($airportIds) < 3) {
            return;
        }

        $baseDate = Carbon::today();

        $requests = [
            [
                'status' => TravelStatus::Requested->value,
                'departure_date' => $baseDate->copy()->addDays(10)->toDateString(),
                'return_date' => $baseDate->copy()->addDays(15)->toDateString(),
            ],
            [
                'status' => TravelStatus::Approved->value,
                'departure_date' => $baseDate->copy()->addDays(20)->toDateString(),
                'return_date' => $baseDate->copy()->addDays(25)->toDateString(),
            ],
            [
                'status' => TravelStatus::Canceled->value,
                'departure_date' => $baseDate->copy()->addDays(30)->toDateString(),
                'return_date' => $baseDate->copy()->addDays(35)->toDateString(),
            ],
        ];

        foreach (['maria@empresa.com', 'joao@empresa.com'] as $index => $email) {
            $user = $users->get($email);
            if (! $user) {
                continue;
            }

            foreach ($requests as $offset => $requestData) {
                $destinationId = $airportIds[($index + $offset) % count($airportIds)];

                $travelRequest = TravelRequest::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'destination_airport_id' => $destinationId,
                        'departure_date' => $requestData['departure_date'],
                        'return_date' => $requestData['return_date'],
                        'status' => $requestData['status'],
                    ],
                    [
                        'code' => 'TMP-'.Str::uuid(),
                    ]
                );

                $expectedCode = $this->generateCode($travelRequest->id);
                if ($travelRequest->code !== $expectedCode) {
                    $travelRequest->update(['code' => $expectedCode]);
                }
            }
        }
    }

    private function generateCode(int $id): string
    {
        $year = now()->year;
        $sequence = str_pad((string) $id, 6, '0', STR_PAD_LEFT);

        return "TRV-{$year}-{$sequence}";
    }
}
