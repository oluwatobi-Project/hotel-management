<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    public function home()
    {
        $roomTypes = RoomType::withCount('rooms')->orderByDesc('price')->take(3)->get();
        $stats = [
            'rooms' => Room::count(),
            'occupied' => Room::where('status', 'occupied')->count(),
        ];

        return view('site.home', compact('roomTypes', 'stats'));
    }

    public function rooms(Request $request)
    {
        $query = RoomType::withCount('rooms');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $roomTypes = $query->orderBy('price')->paginate(9)->withQueryString();

        return view('site.rooms', compact('roomTypes'));
    }

    public function roomType(RoomType $roomType)
    {
        $roomType->loadCount('rooms');

        return view('site.room_type', compact('roomType'));
    }

    public function availableRooms(Request $request)
    {
        $checkIn = $request->input('check_in_date');
        $checkOut = $request->input('check_out_date');

        if (! $checkIn || ! $checkOut) {
            return response()->json([]);
        }

        $bookedRoomIds = Booking::whereIn('status', ['reserved', 'checked_in'])
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
                'type_id' => $room->room_type_id,
                'type' => $room->roomType->name,
                'price' => (float) $room->roomType->price,
            ]);

        return response()->json($rooms);
    }

    public function contact(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email'],
                'message' => ['required', 'string', 'max:2000'],
            ]);

            \Illuminate\Support\Facades\Log::channel('email')->info('Contact form submission', [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'message' => $request->input('message'),
            ]);

            return back()->with('success', 'Thank you! Your message has been received. We will get back to you soon.');
        }

        return view('site.contact');
    }
}
