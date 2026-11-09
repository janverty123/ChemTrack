<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountSecurityNotice extends Notification
{
    public function __construct(public string $message) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('ChemTrack account security')->line($this->message)->line('If you did not make this change, contact your teacher or account support.');
    }
}
