<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['room_number', 'floor', 'room_type_id', 'status', 'notes'];

    public const STATUSES = ['available', 'occupied', 'maintenance', 'cleaning'];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(RoomRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'maintenance');
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }
}
