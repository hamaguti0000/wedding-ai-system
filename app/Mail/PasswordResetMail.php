<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;
    public string $groomName;
    public string $brideName;

    public function __construct(
        public User $user,
        string $token
    ) {
        $this->resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $setting = WeddingSetting::first();
        $this->groomName = $setting?->groom_name_en ?: ($setting?->groom_name ?? 'Kakeru');
        $this->brideName = $setting?->bride_name_en ?: ($setting?->bride_name ?? 'Mirai');
    }

    public function envelope(): Envelope
    {
        $fromName = trim($this->groomName . '・' . $this->brideName);

        return new Envelope(
            from: new Address(config('mail.from.address'), $fromName),
            subject: 'パスワードの再設定 | ' . $this->groomName . ' & ' . $this->brideName . ' Wedding',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.password-reset',
        );
    }
}
