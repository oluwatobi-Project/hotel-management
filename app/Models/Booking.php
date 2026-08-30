<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_ref',
        'guest_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'status',
        'total_amount',
        'discount',
        'notes',
        'created_by',
    ];

    public const STATUSES = ['reserved', 'checked_in', 'checked_out', 'cancelled'];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'total_amount' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(RoomRequest::class);
    }

    public function nights(): int
    {
        return max(1, $this->check_in_date->diffInDays($this->check_out_date));
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function outstandingAmount(): float
    {
        return max(0, (float) $this->total_amount - $this->paidAmount());
    }

    /**
     * An unpaid reserved booking is subject to the 24-hour auto-release policy.
     */
    public function isUnpaid(): bool
    {
        return $this->paidAmount() <= 0;
    }

    public function releaseDueAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->status !== 'reserved' || ! $this->isUnpaid()) {
            return null;
        }

        return $this->created_at->copy()->addHours(24);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['reserved', 'checked_in']);
    }

    public static function generateRef(): string
    {
        do {
            $ref = 'GH-' . strtoupper(substr(md5(uniqid((string) rand(), true)), 0, 8));
        } while (static::where('booking_ref', $ref)->exists());

        return $ref;
    }
}
