<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'خوش آمدید به MOLIDO — حساب شما آماده است',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: view('emails.welcome', [
                'user' => $this->user,
                'appName' => config('app.name', 'MOLIDO'),
                'frontendUrl' => rtrim(env('FRONTEND_URL', config('app.url')), '/'),
            ])->render(),
        );
    }
}
