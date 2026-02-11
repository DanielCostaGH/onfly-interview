<?php

namespace App\Services;

use App\Models\Airport;
use App\Repositories\AirportRepository;
use Illuminate\Database\Eloquent\Collection;

class AirportService
{
    public function __construct(
        private readonly AirportRepository $airportRepository
    ) {}

    /**
     * List all airports ordered for selection.
     *
     * @return Collection<int, Airport>
     */
    public function list(): Collection
    {
        return $this->airportRepository->all();
    }
}
