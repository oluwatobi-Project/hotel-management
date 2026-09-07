<?php

namespace App\Services;

use App\Mail\BookingConfirmationMail;
use App\Mail\CheckedInMail;
use App\Mail\CheckoutReceiptMail;
use App\Mail\PaymentReceiptMail;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class BookingNotifier
{
    public function __construct(protected SmsService $sms)
    {
    }

    /**
     * Notify staff (in-app) and the guest (email + SMS) about a booking event.
     */
    public function notifyBookingCreated(Booking $booking): void
    {
        $message = sprintf(
            'New booking %s — %s in room %s (%s → %s). Total: %s.',
            $booking->booking_ref,
            $booking->guest->name,
            $booking->room->room_number,
            $booking->check_in_date->format('Y-m-d'),
            $booking->check_out_date->format('Y-m-d'),
            number_format($booking->total_amount, 2)
        );

        $this->notifyStaff('New booking received', $message, 'success', route('bookings.show', $booking->id));
        $this->emailGuest(
            $booking,
            new BookingConfirmationMail($booking),
            "Booking confirmation {$booking->booking_ref}"
        );
        $this->smsGuest(
            $booking,
            "Dear {$booking->guest->name}, your booking {$booking->booking_ref} at Grand Horizon Hotel is confirmed. Check-in: {$booking->check_in_date->format('d M Y')}, Check-out: {$booking->check_out_date->format('d M Y')}. Room: {$booking->room->room_number}. Thank you."
        );
    }

    public function notifyCheckedIn(Booking $booking): void
    {
        $message = sprintf(
            '%s checked in to room %s.',
            $booking->guest->name,
            $booking->room->room_number
        );

        $this->notifyStaff('Guest checked in', $message, 'info', route('bookings.show', $booking->id));
        $this->emailGuest(
            $booking,
            new CheckedInMail($booking),
            "Check-in confirmation {$booking->booking_ref}"
        );
        $this->smsGuest(
            $booking,
            "Welcome, {$booking->guest->name}! You have checked in to room {$booking->room->room_number} at Grand Horizon Hotel. Enjoy your stay."
        );
    }

    /**
     * Email the guest a receipt whenever a payment is recorded against a booking.
     */
    public function notifyPaymentReceived(Booking $booking, Payment $payment, ?string $context = null): void
    {
        $this->emailGuest(
            $booking,
            new PaymentReceiptMail($booking, $payment, $context),
            "Payment receipt {$payment->receipt_no} — Grand Horizon Hotel"
        );
    }

    public function notifyCheckedOut(Booking $booking, ?Payment $payment = null): void
    {
        $message = $payment
            ? sprintf(
                '%s checked out from room %s. Payment of %s received (%s).',
                $booking->guest->name,
                $booking->room->room_number,
                number_format($payment->amount, 2),
                $payment->method
            )
            : sprintf(
                '%s checked out from room %s. Balance was already settled.',
                $booking->guest->name,
                $booking->room->room_number
            );

        $this->notifyStaff('Guest checked out', $message, 'warning', route('bookings.show', $booking->id));
        $this->emailGuest(
            $booking,
            new CheckoutReceiptMail($booking, $payment),
            $payment
                ? "Your receipt {$payment->receipt_no} — Grand Horizon Hotel"
                : "Your check-out summary {$booking->booking_ref} — Grand Horizon Hotel"
        );
        $this->smsGuest(
            $booking,
            $payment
                ? "Dear {$booking->guest->name}, thank you for staying with us! Receipt {$payment->receipt_no} for {$payment->amount} has been emailed to you. We look forward to hosting you again."
                : "Dear {$booking->guest->name}, thank you for staying with us! Your stay at Grand Horizon Hotel has been settled in full. We look forward to hosting you again."
        );
    }

    public function notifyCancelled(Booking $booking): void
    {
        $message = sprintf(
            'Booking %s for %s has been cancelled.',
            $booking->booking_ref,
            $booking->guest->name
        );

        $this->notifyStaff('Booking cancelled', $message, 'danger', route('bookings.show', $booking->id));
        $this->smsGuest(
            $booking,
            "Dear {$booking->guest->name}, your booking {$booking->booking_ref} has been cancelled. For any questions, please contact the hotel."
        );
    }

    public function notifyStaff(string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        AppNotification::sendToAll($title, $message, $type, $link);

        foreach (User::where('role', 'admin')->get() as $admin) {
            AppNotification::sendTo($admin->id, $title, $message, $type, $link);
        }
    }

    protected function emailGuest(Booking $booking, mixed $mailable, string $subject): void
    {
        if (! $booking->guest->email) {
            return;
        }

        try {
            Mail::to($booking->guest->email)
                ->send($mailable);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function smsGuest(Booking $booking, string $message): void
    {
        $this->sms->send($booking->guest->phone, $message, $booking->id);
    }
}
