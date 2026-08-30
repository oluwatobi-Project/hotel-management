<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $search = '%'.$request->search.'%';
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('id_card', 'like', $search);
            });
        }

        $guests = $query->withCount('bookings')->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('guests.index', compact('guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'id_card' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $guest = Guest::create($data);

        if ($request->wantsJson()) {
            return response()->json(['id' => $guest->id, 'name' => $guest->name], 201);
        }

        return back()->with('success', 'Guest added.');
    }

    public function update(Request $request, Guest $guest)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'id_card' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $guest->update($data);

        return back()->with('success', 'Guest updated.');
    }

    public function destroy(Guest $guest)
    {
        if ($guest->bookings()->exists()) {
            return back()->with('error', 'Cannot delete a guest who has bookings.');
        }

        $guest->delete();

        return back()->with('success', 'Guest deleted.');
    }
}
