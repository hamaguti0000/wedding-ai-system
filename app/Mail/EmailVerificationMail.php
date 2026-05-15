<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verifyUrl;
    public string $groomName;
    public string $brideName;

    public function __construct(
        public User $user,
        string $plainToken
    ) {
        $this->verifyUrl = url('/verify?token=' . $plainToken);

        $setting = WeddingSetting::first();
        $this->groomName = $setting?->groom_name ?? 'Kakeru';
        $this->brideName = $setting?->bride_name ?? 'Mirai';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'メールアドレスの確認 | ' . $this->groomName . ' & ' . $this->brideName . ' Wedding',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.email-verification',
        );
    }
}
