<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantOrder extends Model
{
    protected $fillable = ['order_no', 'booking_id', 'guest_id', 'room_id', 'status', 'total', 'notes'];

    public const STATUSES = ['pending', 'preparing', 'served', 'cancelled'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public static function generateOrderNo(): string
    {
        return 'ORD-'.strtoupper(uniqid());
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

    public function items(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }

    public function itemsCountLabel(): string
    {
        $count = $this->items->sum('quantity');

        return $count.' '.($count === 1 ? 'item' : 'items').' · '.number_format((float) $this->total, 2);
    }
}
