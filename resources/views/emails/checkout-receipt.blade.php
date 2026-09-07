<x-mail::message>
# @if($payment) Your Receipt — Grand Horizon Hotel @else Check-Out Summary — Grand Horizon Hotel @endif

Dear **{{ $booking->guest->name }}**,

@if($payment)
Thank you for staying with us. Here is your receipt for your stay at room **{{ $booking->room->room_number }}**.

**Receipt No:** {{ $payment->receipt_no }}
**Booking Reference:** {{ $booking->booking_ref }}

| Detail | Value |
|:-------|:------|
| Check-In | {{ $booking->check_in_date->format('l, d F Y') }} |
| Check-Out | {{ $booking->check_out_date->format('l, d F Y') }} |
| Nights | {{ $booking->nights() }} |
| Amount Paid | {{ $settings['currency'] ?? '$' }}{{ number_format($payment->amount, 2) }} |
| Payment Method | {{ ucfirst($payment->method) }} |
| Date | {{ $payment->paid_at?->format('d F Y H:i') }} |
@else
You have checked out of room **{{ $booking->room->room_number }}**. Your stay was settled in full, so no further payment is due.

| Detail | Value |
|:-------|:------|
| Booking Reference | {{ $booking->booking_ref }} |
| Check-In | {{ $booking->check_in_date->format('l, d F Y') }} |
| Check-Out | {{ $booking->check_out_date->format('l, d F Y') }} |
| Nights | {{ $booking->nights() }} |
| Total | {{ $settings['currency'] ?? '$' }}{{ number_format($booking->total_amount, 2) }} |
@endif

We hope you enjoyed your stay and look forward to welcoming you back soon!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
