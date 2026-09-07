<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public Payment $payment, public ?string $context = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment receipt '.$this->payment->receipt_no.' — Grand Horizon Hotel',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-receipt',
        );
    }
}
