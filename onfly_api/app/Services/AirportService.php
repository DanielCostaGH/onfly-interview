<?php

namespace App\Services;

use App\Repositories\AirportRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Airport;

class AirportService
{
    public function __construct(
        private readonly AirportRepository $airportRepository
    ) {
    }

    /**
     * @return Collection<int, Airport>
     */
    public function list(): Collection
    {
        return $this->airportRepository->all();
    }
}
