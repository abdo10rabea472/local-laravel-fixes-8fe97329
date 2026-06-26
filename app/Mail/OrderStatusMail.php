<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $kind = 'placed')
    {
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->kind) {
            'placed' => 'تم استلام طلبك #' . $this->order->order_number,
            'shipped' => 'تم شحن طلبك #' . $this->order->order_number,
            'delivered' => 'تم توصيل طلبك #' . $this->order->order_number,
            'cancelled' => 'تم إلغاء طلبك #' . $this->order->order_number,
            'refunded' => 'تم استرداد طلبك #' . $this->order->order_number,
            default => 'تحديث حالة طلبك #' . $this->order->order_number,
        };
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-status', with: [
            'order' => $this->order,
            'kind' => $this->kind,
        ]);
    }
}
