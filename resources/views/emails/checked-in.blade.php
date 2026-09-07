<x-mail::message>
# Welcome — You've Checked In

Dear **{{ $booking->guest->name }}**,

You have successfully checked in at **{{ config('app.name') }}**. We hope you enjoy your stay!

**Booking Reference:** {{ $booking->booking_ref }}

| Detail | Value |
|:-------|:------|
| Room | {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }}) |
| Check-In | {{ $booking->check_in_date->format('l, d F Y') }} |
| Check-Out | {{ $booking->check_out_date->format('l, d F Y') }} |
| Nights | {{ $booking->nights() }} |
| Total | {{ $settings['currency'] ?? '$' }}{{ number_format($booking->total_amount, 2) }} |

For room service or housekeeping requests, please call the front desk or use the guest portal.

We look forward to making your stay memorable!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
