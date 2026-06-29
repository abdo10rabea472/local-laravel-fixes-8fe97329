<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $adminName,
        public string $otp,
        public string $actionTitle,
        public string $actionDetail,
        public string $ip,
        public string $userAgent,
        public int $expiresInMinutes = 10,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Verification Code — '.$this->actionTitle,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-otp');
    }
}
