<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RoomRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomRequest::with(['room', 'guest', 'booking', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        $requests = $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")->orderByDesc('created_at')->paginate(20)->withQueryString();

        $rooms = Room::orderBy('room_number')->get();
        $bookings = Booking::with('guest')->whereIn('status', ['reserved', 'checked_in'])->orderByDesc('created_at')->get();
        $guests = Guest::orderBy('name')->get();
        $staff = User::orderBy('name')->get();

        return view('requests.index', compact('requests', 'rooms', 'bookings', 'guests', 'staff'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $roomRequest = RoomRequest::create($data);

        AppNotification::sendToAll(
            'New room request',
            sprintf('Room %s requested: %s — %s', $roomRequest->room->room_number, strtoupper($roomRequest->request_type), $roomRequest->description),
            'info',
            route('requests.index', ['status' => 'pending'])
        );

        return back()->with('success', 'Room request created.');
    }

    public function update(Request $request, RoomRequest $roomRequest)
    {
        $data = $request->validate([
            'request_type' => ['required', 'in:'.implode(',', RoomRequest::TYPES)],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:'.implode(',', RoomRequest::STATUSES)],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $roomRequest->update($data);

        return back()->with('success', 'Room request updated.');
    }

    public function setStatus(Request $request, RoomRequest $roomRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', RoomRequest::STATUSES)],
        ]);

        $roomRequest->update([
            'status' => $data['status'],
            'assigned_to' => $data['status'] === 'in_progress' ? ($roomRequest->assigned_to ?? auth()->id()) : $roomRequest->assigned_to,
            'responded_at' => in_array($data['status'], ['completed', 'cancelled']) ? now() : $roomRequest->responded_at,
        ]);

        return back()->with('success', 'Request status updated.');
    }

    public function destroy(RoomRequest $roomRequest)
    {
        $roomRequest->delete();

        return back()->with('success', 'Room request deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'request_type' => ['required', 'in:'.implode(',', RoomRequest::TYPES)],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);
    }
}
