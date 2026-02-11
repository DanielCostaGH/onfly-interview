<?php

namespace App\Repositories;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Collection;

class AirportRepository
{
    /**
     * @return Collection<int, Airport>
     */
    public function all(): Collection
    {
        return Airport::query()
            ->orderBy('city')
            ->orderBy('iata_code')
            ->get();
    }
}
