<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->with('amenityItems')->orderByDesc('price')->get();

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
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
        ]);

        $roomType = RoomType::create($data);
        $roomType->amenityItems()->sync($request->input('amenity_ids', []));

        return $this->respond($request, $roomType, 'Room type created.');
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
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
        ]);

        $roomType->update($data);
        $roomType->amenityItems()->sync($request->input('amenity_ids', []));

        return $this->respond($request, $roomType, 'Room type updated.');
    }

    public function destroy(Request $request, RoomType $roomType)
    {
        if ($roomType->rooms()->count() > 0) {
            $message = 'Cannot delete a room type that still has rooms.';

            return $this->respond($request, null, $message, 'error');
        }

        $roomType->delete();

        return $this->respond($request, null, 'Room type deleted.');
    }

    protected function respond(Request $request, ?RoomType $roomType, string $message, string $level = 'success')
    {
        if ($request->wantsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'success' => $level === 'success',
                'message' => $message,
                'room_type' => $roomType ? $roomType->loadCount('rooms') : null,
            ], $level === 'success' ? 200 : 422);
        }

        return back()->with($level, $message);
    }
}
