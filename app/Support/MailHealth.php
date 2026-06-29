<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

class MailHealth
{
    /**
     * Return null when mail config looks complete, otherwise a user-friendly
     * reason string explaining what is missing.
     */
    public static function failureReason(): ?string
    {
        $mailer = (string) Config::get('mail.default');
        if ($mailer === '' || $mailer === 'log' || $mailer === 'array' || $mailer === 'null') {
            return 'Email delivery is not configured (current driver: '.($mailer ?: 'none').'). '
                .'Please configure SMTP settings (MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS) in your .env first.';
        }

        $from = Config::get('mail.from.address');
        if (empty($from) || $from === 'hello@example.com') {
            return 'Sender address (MAIL_FROM_ADDRESS) is missing or still using the example default. Set it in your .env first.';
        }

        if ($mailer === 'smtp') {
            $host = Config::get('mail.mailers.smtp.host');
            $port = Config::get('mail.mailers.smtp.port');
            if (empty($host) || $host === 'mailpit' || empty($port)) {
                return 'SMTP host/port not configured properly (MAIL_HOST / MAIL_PORT). Please complete SMTP settings in your .env first.';
            }
        }

        return null;
    }

    public static function isConfigured(): bool
    {
        return self::failureReason() === null;
    }
}
