<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['receipt_no', 'booking_id', 'amount', 'method', 'status', 'paid_at'];

    public const METHODS = ['cash', 'card', 'mobile'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public static function generateReceiptNo(): string
    {
        do {
            $no = 'RCP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid((string) rand(), true)), 0, 6));
        } while (static::where('receipt_no', $no)->exists());

        return $no;
    }
}
