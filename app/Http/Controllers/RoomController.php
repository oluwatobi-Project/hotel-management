<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('roomType');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('room_number', 'like', '%'.$request->search.'%');
        }

        $rooms = $query->orderBy('floor')->orderBy('room_number')->get();
        $roomTypes = RoomType::orderBy('price', 'desc')->get();
        $statuses = Room::STATUSES;

        return view('rooms.index', compact('rooms', 'roomTypes', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:10', 'unique:rooms,room_number'],
            'floor' => ['required', 'integer', 'min:0'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'status' => ['required', 'in:available,occupied,maintenance,cleaning'],
            'notes' => ['nullable', 'string'],
        ]);

        Room::create($data);

        return back()->with('success', 'Room added.');
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:10', 'unique:rooms,room_number,'.$room->id],
            'floor' => ['required', 'integer', 'min:0'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'status' => ['required', 'in:available,occupied,maintenance,cleaning'],
            'notes' => ['nullable', 'string'],
        ]);

        $room->update($data);

        return back()->with('success', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('status', ['reserved', 'checked_in'])->exists()) {
            return back()->with('error', 'Cannot delete a room with active bookings.');
        }

        $room->delete();

        return back()->with('success', 'Room deleted.');
    }

    public function setStatus(Request $request, Room $room)
    {
        $data = $request->validate([
            'status' => ['required', 'in:available,occupied,maintenance,cleaning'],
        ]);

        $room->update($data);

        return back()->with('success', "Room {$room->room_number} marked as {$data['status']}.");
    }
}
