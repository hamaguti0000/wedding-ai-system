<?php

namespace App\Mail;

use App\Models\ReminderSchedule;
use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $groomName;
    public string $brideName;
    public string $siteUrl;

    public function __construct(
        public User              $recipient,
        public ReminderSchedule  $reminder,
    ) {
        $setting          = WeddingSetting::first();
        $this->groomName  = $setting?->groom_name_en ?: ($setting?->groom_name ?? 'Kakeru');
        $this->brideName  = $setting?->bride_name_en ?: ($setting?->bride_name ?? 'Mirai');
        $this->siteUrl    = config('app.url');
    }

    public function envelope(): Envelope
    {
        $fromName = trim($this->groomName . '・' . $this->brideName);

        return new Envelope(
            from: new Address(config('mail.from.address'), $fromName),
            subject: $this->reminder->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.reminder',
        );
    }
}
