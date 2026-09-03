<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index()
    {
        $amenities = Amenity::withCount('roomTypes')->orderBy('name')->get();

        return view('amenities.index', compact('amenities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Amenity::create($data);

        return $this->respond($request, null, 'Amenity created.');
    }

    public function update(Request $request, Amenity $amenity)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $amenity->update($data);

        return $this->respond($request, $amenity, 'Amenity updated.');
    }

    public function destroy(Request $request, Amenity $amenity)
    {
        $amenity->delete();

        return $this->respond($request, null, 'Amenity deleted.');
    }

    protected function respond(Request $request, ?Amenity $amenity, string $message, string $level = 'success')
    {
        if ($request->wantsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'success' => $level === 'success',
                'message' => $message,
                'amenity' => $amenity ? $amenity->loadCount('roomTypes') : null,
            ], $level === 'success' ? 200 : 422);
        }

        return back()->with($level, $message);
    }
}
