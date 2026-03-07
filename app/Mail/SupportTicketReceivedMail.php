<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Previewable;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class SupportTicketReceivedMail extends Mailable implements Previewable, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    /**
     * Create a representative instance of this mailable for preview purposes.
     */
    public static function preview(): Mailable
    {
        return new self(
            ticket: SupportTicket::factory()->make(),
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New Support Ticket Received'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.support-ticket-received',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
