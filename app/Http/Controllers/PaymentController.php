<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking.guest', 'booking.room']);

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_no', 'like', '%'.$request->search.'%')
                    ->orWhereHas('booking.guest', fn ($g) => $g->where('name', 'like', '%'.$request->search.'%'));
            });
        }

        $payments = $query->orderByDesc('paid_at')->paginate(20)->withQueryString();
        $total = Payment::where('status', 'paid')->sum('amount');

        return view('payments.index', compact('payments', 'total'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:'.implode(',', Payment::METHODS)],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);
        $paid = $booking->payments()->where('status', 'paid')->sum('amount');

        if ($paid + $data['amount'] > $booking->total_amount) {
            return back()->with('error', 'Payment exceeds the booking total.');
        }

        Payment::create([
            'receipt_no' => Payment::generateReceiptNo(),
            'booking_id' => $booking->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payment recorded.');
    }

    public function refund(Payment $payment)
    {
        if ($payment->status === 'refunded') {
            return back()->with('error', 'Payment is already refunded.');
        }

        $payment->update(['status' => 'refunded']);

        return back()->with('success', 'Payment refunded.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return back()->with('success', 'Payment record deleted.');
    }
}
