<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Services\BookingNotifier;
use Illuminate\Console\Command;

class ReleaseUnpaidBookings extends Command
{
    protected $signature = 'bookings:release-unpaid {--hours=24 : Age in hours after which an unpaid reserved booking is released}';

    protected $description = 'Automatically cancel reserved bookings that received no payment within 24 hours, freeing the room again.';

    public function handle(BookingNotifier $notifier): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours);

        $bookings = Booking::with(['guest', 'room', 'payments'])
            ->where('status', 'reserved')
            ->where('created_at', '<=', $cutoff)
            ->get()
            ->filter(fn (Booking $booking) => $booking->isUnpaid());

        $released = 0;
        foreach ($bookings as $booking) {
            $booking->update([
                'status' => 'cancelled',
                'notes' => trim(($booking->notes ? $booking->notes.' | ' : '').'Auto-released: no payment received within '.$hours.' hours.'),
            ]);

            AppNotification::sendToAll(
                'Booking auto-released',
                sprintf('Booking %s for %s was released because no payment was received within %d hours. Room %s is available again.',
                    $booking->booking_ref, $booking->guest->name, $hours, $booking->room->room_number),
                'warning',
                route('bookings.index')
            );

            $notifier->smsGuest($booking, sprintf(
                'Dear %s, your reservation %s at Grand Horizon Hotel was released because no payment was received within %d hours. The room is available again — please book and pay within the window next time. Thank you.',
                $booking->guest->name, $booking->booking_ref, $hours
            ));

            $released++;
        }

        $this->info("Released {$released} unpaid booking(s).");

        return self::SUCCESS;
    }
}
