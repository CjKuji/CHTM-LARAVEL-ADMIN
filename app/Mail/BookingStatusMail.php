<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $status;

    /**
     * Create a new message instance.
     * * @param Booking $booking
     * @param string $status ('approved' or 'rejected')
     */
    public function __construct(Booking $booking, string $status)
    {
        $this->booking = $booking;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved' 
            ? 'Your Booking Reservation is Confirmed!' 
            : 'Update regarding your booking reservation request';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-status', // Aligned to match your blade filename
            with: [
                'booking' => $this->booking,
                'status'  => $this->status,
            ],
        );
    }
}