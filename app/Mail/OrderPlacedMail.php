<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
        $this->order->load(['user', 'items.productVariant.product']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Shop order #' . $this->order->id . ' – ' . $this->order->user->name,
            replyTo: [$this->order->user->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
        );
    }
}
