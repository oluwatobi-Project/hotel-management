<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantMenuItem extends Model
{
    protected $table = 'restaurant_menu_items';

    protected $fillable = ['name', 'category', 'price', 'description', 'image_url', 'is_available'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class, 'restaurant_menu_item_id');
    }
}
