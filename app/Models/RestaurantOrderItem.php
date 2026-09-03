<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantOrderItem extends Model
{
    protected $fillable = ['restaurant_order_id', 'restaurant_menu_item_id', 'quantity', 'unit_price'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenuItem::class);
    }
}
