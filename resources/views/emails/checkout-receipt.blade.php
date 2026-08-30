<x-mail::message>
# Your Receipt — Grand Horizon Hotel

Dear **{{ $booking->guest->name }}**,

Thank you for staying with us. Here is your receipt for your stay at room **{{ $booking->room->room_number }}**.

**Receipt No:** {{ $payment->receipt_no }}
**Booking Reference:** {{ $booking->booking_ref }}

| Detail | Value |
|:-------|:------|
| Check-In | {{ $booking->check_in_date->format('l, d F Y') }} |
| Check-Out | {{ $booking->check_out_date->format('l, d F Y') }} |
| Nights | {{ $booking->nights() }} |
| Amount Paid | ${{ number_format($payment->amount, 2) }} |
| Payment Method | {{ ucfirst($payment->method) }} |
| Date | {{ $payment->paid_at?->format('d F Y H:i') }} |

We hope you enjoyed your stay and look forward to welcoming you back soon!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
