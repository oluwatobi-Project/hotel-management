<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public const PROVIDER = 'log';

    /**
     * Send an SMS to a phone number.
     *
     * The default provider writes the message to storage/logs/sms.log and
     * records a row in sms_logs. Swap in a real provider (Twilio, Vonage,
     * Africa's Talking, etc.) behind this method for production.
     */
    public function send(string $to, string $message, ?int $bookingId = null): bool
    {
        $to = $this->normalizeNumber($to);

        if (! $to) {
            Log::warning('SMS skipped: no valid recipient number.', ['message' => $message]);

            return false;
        }

        try {
            $this->dispatch($to, $message);
            SmsLog::create([
                'booking_id' => $bookingId,
                'to_number' => $to,
                'message' => $message,
                'provider' => self::PROVIDER,
                'status' => 'sent',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS sending failed: '.$e->getMessage());
            SmsLog::create([
                'booking_id' => $bookingId,
                'to_number' => $to,
                'message' => $message,
                'provider' => self::PROVIDER,
                'status' => 'failed',
            ]);

            return false;
        }
    }

    protected function dispatch(string $to, string $message): void
    {
        $line = '['.now()->toDateTimeString().'] SMS to '.$to.': '.$message;
        Log::channel('sms')->info($line);
    }

    protected function normalizeNumber(?string $number): ?string
    {
        if (! $number) {
            return null;
        }

        $number = preg_replace('/[^0-9+]/', '', $number);

        return $number !== '' ? $number : null;
    }
}
