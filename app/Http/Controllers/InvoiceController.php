<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['guest', 'room.roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('booking_ref', 'like', '%'.$request->search.'%')
                    ->orWhereHas('guest', fn ($g) => $g->where('name', 'like', '%'.$request->search.'%'))
                    ->orWhereHas('room', fn ($r) => $r->where('room_number', 'like', '%'.$request->search.'%'));
            });
        }

        $bookings = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $totals = [
            'reserved' => Booking::where('status', 'reserved')->count(),
            'checked_in' => Booking::where('status', 'checked_in')->count(),
            'checked_out' => Booking::where('status', 'checked_out')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        return view('invoices.index', compact('bookings', 'totals'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['guest', 'room.roomType', 'payments']);

        $paid = $booking->paidAmount();
        $due = $booking->outstandingAmount();
        $nights = $booking->nights();
        $ratePerNight = $nights > 0 ? round(($booking->total_amount + $booking->discount) / $nights, 2) : 0.0;

        $document = match ($booking->status) {
            'checked_out' => ['label' => 'Tax Invoice', 'subtitle' => 'Settled'],
            'checked_in' => ['label' => 'Stay Invoice', 'subtitle' => 'Statement of account'],
            'cancelled' => ['label' => 'Cancelled', 'subtitle' => 'No charge'],
            default => ['label' => 'Proforma Invoice', 'subtitle' => 'Awaiting payment'],
        };

        return view('invoices.show', compact('booking', 'paid', 'due', 'nights', 'ratePerNight', 'document'));
    }
}
