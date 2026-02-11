<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TravelRequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly TravelRequest $travelRequest,
        private readonly string $fromStatus,
        private readonly string $toStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'code' => $this->travelRequest->code,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'destination' => [
                'city' => $this->travelRequest->destinationAirport?->city,
                'state' => $this->travelRequest->destinationAirport?->state,
                'iata_code' => $this->travelRequest->destinationAirport?->iata_code,
            ],
            'departure_date' => $this->travelRequest->departure_date?->toDateString(),
            'return_date' => $this->travelRequest->return_date?->toDateString(),
        ];
    }
}
