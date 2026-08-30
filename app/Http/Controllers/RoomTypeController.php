<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->orderByDesc('price')->get();

        return view('room_types.index', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'bed_count' => ['required', 'integer', 'min:1'],
            'amenities' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        RoomType::create($data);

        return back()->with('success', 'Room type created.');
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'bed_count' => ['required', 'integer', 'min:1'],
            'amenities' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $roomType->update($data);

        return back()->with('success', 'Room type updated.');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->count() > 0) {
            return back()->with('error', 'Cannot delete a room type that still has rooms.');
        }

        $roomType->delete();

        return back()->with('success', 'Room type deleted.');
    }
}
