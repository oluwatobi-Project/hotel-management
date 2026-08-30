<x-mail::message>
# Booking Confirmation

Dear **{{ $booking->guest->name }}**,

Thank you for choosing Grand Horizon Hotel. Your booking has been confirmed.

**Booking Reference:** {{ $booking->booking_ref }}

| Detail | Value |
|:-------|:------|
| Room | {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }}) |
| Check-In | {{ $booking->check_in_date->format('l, d F Y') }} |
| Check-Out | {{ $booking->check_out_date->format('l, d F Y') }} |
| Nights | {{ $booking->nights() }} |
| Total | ${{ number_format($booking->total_amount, 2) }} |

@if($booking->notes)
**Notes:** {{ $booking->notes }}
@endif

We look forward to welcoming you. If you have any questions, please contact the front desk.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
