<?php

namespace App\Models;

use App\Enums\TravelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'destination_airport_id',
        'departure_date',
        'return_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'status' => TravelStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_airport_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TravelRequestLog::class);
    }
}
