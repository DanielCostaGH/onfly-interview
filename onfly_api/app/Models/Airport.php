<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = [
        'iata_code',
        'name',
        'city',
        'state',
        'country',
    ];

    public function travelRequests(): HasMany
    {
        return $this->hasMany(TravelRequest::class, 'destination_airport_id');
    }
}
