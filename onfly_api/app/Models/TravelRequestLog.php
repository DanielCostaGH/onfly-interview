<?php

namespace App\Models;

use App\Enums\TravelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequestLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'travel_request_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => TravelStatus::class,
            'to_status' => TravelStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
