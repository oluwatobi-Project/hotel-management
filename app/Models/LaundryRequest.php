<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryRequest extends Model
{
    protected $fillable = [
        'booking_id', 'guest_id', 'room_id', 'service_type', 'item_description',
        'quantity', 'estimated_cost', 'status', 'notes',
    ];

    public const SERVICE_TYPES = ['wash', 'iron', 'wash_iron', 'dry_clean'];
    public const STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
