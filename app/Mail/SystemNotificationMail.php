<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ?string $actionUrl = null;

    public string $logoUrl;

    public function __construct(
        public string $recipientName,
        public string $titleText,
        public ?string $bodyText = null,
        ?string $relativeLink = null,
    ) {
        $base = rtrim((string) config('app.url'), '/');

        if ($relativeLink) {
            $this->actionUrl = str_starts_with($relativeLink, 'http')
                ? $relativeLink
                : $base.'/'.ltrim($relativeLink, '/');
        }

        $this->logoUrl = $base.'/slice/assets/images/logo.png';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->titleText);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system-notification',
            with: [
                'recipientName' => $this->recipientName,
                'titleText' => $this->titleText,
                'bodyText' => $this->bodyText,
                'actionUrl' => $this->actionUrl,
                'logoUrl' => $this->logoUrl,
            ],
        );
    }
}
