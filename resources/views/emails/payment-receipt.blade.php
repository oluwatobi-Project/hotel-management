<x-mail::message>
# Payment Receipt

Dear **{{ $booking->guest->name }}**,

@if($context)
{{ $context }}
@endif

Thank you for your payment. Here is your receipt.

**Receipt No:** {{ $payment->receipt_no }}
**Booking Reference:** {{ $booking->booking_ref }}

| Detail | Value |
|:-------|:------|
| Amount Paid | {{ $settings['currency'] ?? '$' }}{{ number_format($payment->amount, 2) }} |
| Payment Method | {{ ucfirst($payment->method) }} |
| Date | {{ $payment->paid_at?->format('d F Y H:i') }} |
| Booking Total | {{ $settings['currency'] ?? '$' }}{{ number_format($booking->total_amount, 2) }} |
| Balance Due | {{ $settings['currency'] ?? '$' }}{{ number_format(max(0, $booking->outstandingAmount()), 2) }} |

If you have any questions, please contact the front desk.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
