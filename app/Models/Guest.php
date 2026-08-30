<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'id_card', 'nationality', 'address', 'notes'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
