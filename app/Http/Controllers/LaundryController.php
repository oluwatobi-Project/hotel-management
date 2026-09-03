<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\LaundryRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LaundryController extends Controller
{
    public function index(Request $request)
    {
        $query = LaundryRequest::with(['booking.guest', 'guest', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed', 'cancelled')")->orderByDesc('created_at')->paginate(20)->withQueryString();

        $rooms = Room::orderBy('room_number')->get();
        $bookings = Booking::with('guest')->whereIn('status', ['reserved', 'checked_in'])->orderByDesc('created_at')->get();
        $guests = Guest::orderBy('name')->get();

        return view('laundry.index', compact('requests', 'rooms', 'bookings', 'guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['nullable', 'exists:rooms,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'service_type' => ['required', 'in:'.implode(',', LaundryRequest::SERVICE_TYPES)],
            'item_description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1', 'max:200'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $laundry = LaundryRequest::create($data);

        AppNotification::sendToAll(
            'New laundry request',
            sprintf('Laundry request — Room %s (%s): %s', $laundry->room?->room_number ?? '—', $laundry->guest?->name ?? 'Walk-in', $laundry->item_description),
            'info',
            route('laundry.index', ['status' => 'pending'])
        );

        return back()->with('success', 'Laundry request created.');
    }

    public function setStatus(Request $request, LaundryRequest $laundry)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', LaundryRequest::STATUSES)],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $laundry->update([
            'status' => $data['status'],
            'estimated_cost' => $data['estimated_cost'] ?? $laundry->estimated_cost,
        ]);

        return back()->with('success', 'Laundry request status updated.');
    }

    public function destroy(Request $request, LaundryRequest $laundry)
    {
        $laundry->delete();

        return back()->with('success', 'Laundry request deleted.');
    }
}
