<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailLog;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send a mailable to the booking's guest and persist an email_logs row
     * with the rendered HTML body so it can be viewed or re-sent later.
     */
    public function send(?Booking $booking, Mailable $mailable, ?string $overrideTo = null): bool
    {
        $to = $overrideTo ?: $booking?->guest?->email;

        if (! $to) {
            Log::warning('Email skipped: no recipient address.', ['mailable' => class_basename($mailable)]);

            return false;
        }

        $subject = (string) class_basename($mailable);
        $body = null;
        $error = null;

        try {
            $subject = (string) $mailable->envelope()->subject;
            $body = $mailable->render();
        } catch (\Throwable $e) {
            $error = 'Rendering failed: '.$e->getMessage();
            Log::warning('Email rendered unsuccessfully.', ['mailable' => class_basename($mailable), 'error' => $e->getMessage()]);
        }

        $status = 'failed';
        try {
            Mail::to($to)->send($mailable);
            $status = 'sent';
            $error = null;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            report($e);
        }

        EmailLog::create([
            'booking_id' => $booking?->id,
            'to_email' => $to,
            'subject' => $subject,
            'mailable' => class_basename($mailable),
            'body' => $body,
            'status' => $status,
            'error' => $error,
        ]);

        return $status === 'sent';
    }

    /**
     * Re-deliver a previously captured email (optionally to a different address).
     * The original body is used verbatim so the guest receives exactly what was sent before.
     */
    public function resend(EmailLog $log, ?string $overrideTo = null): bool
    {
        $to = $overrideTo ?: $log->to_email;

        if (! $to) {
            return false;
        }

        $status = 'failed';
        $error = null;

        try {
            Mail::html($log->body ?: '<p> </p>', function (Message $message) use ($log, $to) {
                $message->to($to)
                    ->subject($log->subject ?: 'Grand Horizon Hotel');
            });
            $status = 'sent';
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            report($e);
        }

        EmailLog::create([
            'booking_id' => $log->booking_id,
            'to_email' => $to,
            'subject' => $log->subject,
            'mailable' => $log->mailable ? $log->mailable.' (resent)' : 'raw',
            'body' => $log->body,
            'status' => $status,
            'error' => $error,
        ]);

        return $status === 'sent';
    }

    /**
     * Send a plain test email to verify the configured transport.
     */
    public function sendTest(string $to): bool
    {
        if (! $to) {
            return false;
        }

        $status = 'failed';
        $error = null;

        try {
            Mail::html(
                '<p>This is a test email from the Grand Horizon Hotel system.</p><p>If you can read this, the mail transport is working.</p>',
                function (Message $message) use ($to) {
                    $message->to($to)
                        ->subject('Test email from Grand Horizon Hotel');
                }
            );
            $status = 'sent';
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            report($e);
        }

        EmailLog::create([
            'booking_id' => null,
            'to_email' => $to,
            'subject' => 'Test email from Grand Horizon Hotel',
            'mailable' => 'test',
            'body' => '<p>This is a test email from the Grand Horizon Hotel system.</p><p>If you can read this, the mail transport is working.</p>',
            'status' => $status,
            'error' => $error,
        ]);

        return $status === 'sent';
    }
}
