<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmAccountEmail extends Notification
{
    public function __construct(public string $url) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Confirm your ChemTrack email change')->line('Review and confirm your email change while signed in to ChemTrack. This link expires in 30 minutes.')->action('Review email change', $this->url)->line('If you did not request this, ignore this message. Your existing email remains unchanged.');
    }
}
