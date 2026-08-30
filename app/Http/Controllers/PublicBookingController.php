<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Services\BookingNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    public const RELEASE_HOURS = 24;

    public function create()
    {
        return view('site.booking');
    }

    public function store(Request $request, BookingNotifier $notifier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'id_card' => ['nullable', 'string', 'max:50'],
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureRoomAvailable($data['room_id'], $data['check_in_date'], $data['check_out_date']);

        $guest = Guest::where('email', $data['email'])->first()
            ?? Guest::where('phone', $data['phone'] ?? '')->whereNotNull('phone')->first()
            ?? Guest::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'id_card' => $data['id_card'] ?? null,
            ]);

        $room = Room::with('roomType')->findOrFail($data['room_id']);
        $nights = $this->nights($data['check_in_date'], $data['check_out_date']);
        $total = round($nights * (float) $room->roomType->price, 2);

        $booking = Booking::create([
            'booking_ref' => Booking::generateRef(),
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'status' => 'reserved',
            'total_amount' => $total,
            'discount' => 0,
            'notes' => trim(($data['notes'] ?? '').' | Online reservation — held for '.self::RELEASE_HOURS.' hours pending payment.'),
            'created_by' => null,
        ]);

        $notifier->notifyBookingCreated($booking);

        Session::put('verified_booking', $booking->id);

        return redirect()->route('site.booking.confirmation', $booking->id)
            ->with('success', "Reservation {$booking->booking_ref} confirmed!");
    }

    public function confirmation(Booking $booking)
    {
        $booking->load(['guest', 'room.roomType', 'payments']);
        $this->assertVerified($booking);

        return view('site.confirmation', compact('booking'));
    }

    public function lookup()
    {
        return view('site.lookup');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'booking_ref' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
        ]);

        $booking = Booking::with('guest')
            ->where('booking_ref', strtoupper($data['booking_ref']))
            ->whereHas('guest', fn ($q) => $q->where('email', $data['email']))
            ->first();

        if (! $booking) {
            throw ValidationException::withMessages([
                'booking_ref' => 'No booking found for that reference and email.',
            ]);
        }

        Session::put('verified_booking', $booking->id);

        return redirect()->route('site.booking.portal', $booking->id);
    }

    public function portal(Booking $booking)
    {
        $booking->load(['guest', 'room.roomType', 'payments', 'requests', 'requests.room']);
        $this->assertVerified($booking);

        return view('site.portal', compact('booking'));
    }

    public function pay(Request $request, Booking $booking, BookingNotifier $notifier)
    {
        $booking->load(['payments']);
        $this->assertVerified($booking);

        if (! in_array($booking->status, ['reserved', 'checked_in'])) {
            return back()->with('error', 'This booking cannot accept payments.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:'.implode(',', Payment::METHODS)],
            'card_name' => ['nullable', 'string', 'max:150'],
            'card_number' => ['nullable', 'string', 'max:19'],
            'card_expiry' => ['nullable', 'string', 'max:7'],
            'card_cvv' => ['nullable', 'string', 'max:4'],
        ]);

        $outstanding = $booking->outstandingAmount();

        if ($data['amount'] > $outstanding) {
            throw ValidationException::withMessages([
                'amount' => "Amount cannot exceed the outstanding balance of {$outstanding}.",
            ]);
        }

        // Demo gateway: no real card processing is performed.
        $payment = Payment::create([
            'receipt_no' => Payment::generateReceiptNo(),
            'booking_id' => $booking->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        AppNotification::sendToAll(
            'Online payment received',
            sprintf('Payment of %s received for booking %s (%s).', $data['amount'], $booking->booking_ref, $data['method']),
            'success',
            route('bookings.show', $booking->id)
        );

        if ($booking->status === 'reserved' && $booking->outstandingAmount() <= 0) {
            $booking->update(['notes' => trim(($booking->notes ?? '').' | Paid in full online.')]);
        }

        $notifier->notifyStaff(
            'Online payment received',
            sprintf('Online payment of %s (receipt %s) received for booking %s.', $data['amount'], $payment->receipt_no, $booking->booking_ref),
            'success',
            route('bookings.show', $booking->id)
        );
        $notifier->smsGuest($booking, sprintf(
            'Dear %s, your payment of %s for booking %s was received. Receipt %s. Thank you!',
            $booking->guest->name, $data['amount'], $booking->booking_ref, $payment->receipt_no
        ));

        return redirect()->route('site.booking.portal', $booking->id)
            ->with('success', 'Payment successful. Your reservation is now secured.');
    }

    public function cancel(Booking $booking, BookingNotifier $notifier)
    {
        $this->assertVerified($booking);

        if (! in_array($booking->status, ['reserved'])) {
            return back()->with('error', 'Only reserved bookings can be cancelled online.');
        }

        $booking->update(['status' => 'cancelled', 'notes' => trim(($booking->notes ?? '').' | Cancelled online by guest.')]);

        AppNotification::sendToAll(
            'Booking cancelled online',
            sprintf('Booking %s for %s was cancelled by the guest online.', $booking->booking_ref, $booking->guest->name),
            'danger',
            route('bookings.show', $booking->id)
        );

        return back()->with('success', 'Your booking has been cancelled.');
    }

    public function requestStore(Request $request, Booking $booking)
    {
        $this->assertVerified($booking);

        if ($booking->status !== 'checked_in') {
            return back()->with('error', 'Room requests are available once you have checked in.');
        }

        $data = $request->validate([
            'request_type' => ['required', 'in:'.implode(',', RoomRequest::TYPES)],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        RoomRequest::create([
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'guest_id' => $booking->guest_id,
            'request_type' => $data['request_type'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'pending',
        ]);

        AppNotification::sendToAll(
            'New room request from guest',
            sprintf('Room %s (%s) — %s', $booking->room->room_number, strtoupper($data['request_type']), $data['description']),
            'info',
            route('requests.index')
        );

        return back()->with('success', 'Your request has been sent to the front desk.');
    }

    protected function ensureRoomAvailable(int $roomId, string $checkIn, string $checkOut): void
    {
        $overlap = Booking::whereIn('status', ['reserved', 'checked_in'])
            ->where('room_id', $roomId)
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'room_id' => 'That room is no longer available for the selected dates.',
            ]);
        }
    }

    protected function nights(string $checkIn, string $checkOut): int
    {
        return max(1, now()->parse($checkIn)->diffInDays(now()->parse($checkOut)));
    }

    protected function assertVerified(Booking $booking): void
    {
        if ((int) Session::get('verified_booking') !== $booking->id) {
            abort(403, 'Please look up your booking to continue.');
        }
    }
}
