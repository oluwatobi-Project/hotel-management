<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRequest extends Model
{
    protected $fillable = [
        'booking_id',
        'room_id',
        'guest_id',
        'request_type',
        'description',
        'priority',
        'status',
        'assigned_to',
        'responded_at',
    ];

    public const TYPES = ['cleaning', 'food', 'amenity', 'maintenance', 'other'];
    public const STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
