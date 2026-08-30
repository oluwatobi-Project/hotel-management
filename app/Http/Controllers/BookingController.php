<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Services\BookingNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['guest', 'room.roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('guest', function ($g) use ($request) {
                    $g->where('name', 'like', '%'.$request->search.'%');
                })->orWhere('booking_ref', 'like', '%'.$request->search.'%');
            });
        }

        $bookings = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $guests = Guest::orderBy('name')->get();
        $rooms = Room::with('roomType')->active()->orderBy('room_number')->get();
        $statuses = Booking::STATUSES;

        return view('bookings.create', compact('guests', 'rooms', 'statuses'));
    }

    public function store(Request $request, BookingNotifier $notifier)
    {
        $data = $this->validateData($request);

        $room = Room::with('roomType')->findOrFail($data['room_id']);
        $nights = $this->nights($data['check_in_date'], $data['check_out_date']);
        $subtotal = round($nights * $room->roomType->price, 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $total = max(0, $subtotal - $discount);

        $booking = Booking::create([
            'booking_ref' => Booking::generateRef(),
            'guest_id' => $data['guest_id'],
            'room_id' => $data['room_id'],
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'status' => 'reserved',
            'total_amount' => $total,
            'discount' => $discount,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $notifier->notifyBookingCreated($booking);

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', "Booking {$booking->booking_ref} created. Confirmation emailed and SMS sent to guest.");
    }

    public function show(Booking $booking)
    {
        $booking->load(['guest', 'room.roomType', 'payments', 'requests.room', 'requests.assignee', 'creator']);

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return back()->with('error', 'Finished bookings cannot be edited.');
        }

        $guests = Guest::orderBy('name')->get();
        $rooms = Room::with('roomType')->active()->orderBy('room_number')->get();
        $statuses = Booking::STATUSES;

        return view('bookings.edit', compact('booking', 'guests', 'rooms', 'statuses'));
    }

    public function update(Request $request, Booking $booking, BookingNotifier $notifier)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return back()->with('error', 'Finished bookings cannot be edited.');
        }

        $data = $this->validateData($request, $booking->id);

        $room = Room::with('roomType')->findOrFail($data['room_id']);
        $nights = $this->nights($data['check_in_date'], $data['check_out_date']);
        $subtotal = round($nights * $room->roomType->price, 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $total = max(0, $subtotal - $discount);

        $booking->update([
            'guest_id' => $data['guest_id'],
            'room_id' => $data['room_id'],
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'total_amount' => $total,
            'discount' => $discount,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Booking updated.');
    }

    public function checkIn(Booking $booking, BookingNotifier $notifier)
    {
        if ($booking->status !== 'reserved') {
            return back()->with('error', 'Only reserved bookings can be checked in.');
        }

        $booking->update(['status' => 'checked_in']);
        $booking->room()->update(['status' => 'occupied']);

        $notifier->notifyCheckedIn($booking);

        return back()->with('success', "{$booking->guest->name} has checked in.");
    }

    public function checkOut(Request $request, Booking $booking, BookingNotifier $notifier)
    {
        if ($booking->status !== 'checked_in') {
            return back()->with('error', 'Only checked-in bookings can be checked out.');
        }

        $data = $request->validate([
            'method' => ['required', 'in:'.implode(',', Payment::METHODS)],
        ]);

        $payment = Payment::create([
            'receipt_no' => Payment::generateReceiptNo(),
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'method' => $data['method'],
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $booking->update(['status' => 'checked_out']);
        $booking->room()->update(['status' => 'available']);

        $notifier->notifyCheckedOut($booking, $payment);

        return back()->with('success', "Checked out. Receipt {$payment->receipt_no} generated and sent to guest.");
    }

    public function cancel(Booking $booking, BookingNotifier $notifier)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return back()->with('error', 'Booking is already finished.');
        }

        $wasCheckedIn = $booking->status === 'checked_in';

        $booking->update(['status' => 'cancelled']);
        if ($wasCheckedIn) {
            $booking->room()->update(['status' => 'available']);
        }

        $notifier->notifyCancelled($booking);

        return back()->with('success', 'Booking cancelled.');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->status === 'checked_in') {
            return back()->with('error', 'Check out the guest before deleting the booking.');
        }

        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Booking deleted.');
    }

    public function availableRooms(Request $request)
    {
        $checkIn = $request->input('check_in_date');
        $checkOut = $request->input('check_out_date');
        $excludeId = $request->integer('exclude_booking');

        if (! $checkIn || ! $checkOut) {
            return response()->json([]);
        }

        $bookedRoomIds = Booking::whereIn('status', ['reserved', 'checked_in'])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->pluck('room_id');

        $rooms = Room::with('roomType')
            ->active()
            ->whereNotIn('id', $bookedRoomIds)
            ->orderBy('room_number')
            ->get()
            ->map(fn (Room $room) => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'type' => $room->roomType->name,
                'price' => (float) $room->roomType->price,
            ]);

        return response()->json($rooms);
    }

    protected function validateData(Request $request, ?int $ignoreBookingId = null): array
    {
        $data = $request->validate([
            'guest_id' => ['required', 'exists:guests,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $overlap = Booking::whereIn('status', ['reserved', 'checked_in'])
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->where('room_id', $data['room_id'])
            ->where('check_in_date', '<', $data['check_out_date'])
            ->where('check_out_date', '>', $data['check_in_date'])
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'room_id' => 'The selected room is already booked for those dates.',
            ]);
        }

        return $data;
    }

    protected function nights(string $checkIn, string $checkOut): int
    {
        return max(1, now()->parse($checkIn)->diffInDays(now()->parse($checkOut)));
    }
}
