<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = ['booking_id', 'to_email', 'subject', 'mailable', 'body', 'status', 'error'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
