<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao ' . env('APP_NAME', 'SGE') . '!')
            ->greeting('Olá, ' . $this->user->name . '!')
            ->line('Sua conta foi criada com sucesso no ' . env('APP_NAME', 'SGE') . '.')
            ->line('Para acessar o sistema, utilize o e-mail cadastrado e clique no link abaixo para definir sua senha:')
            ->action('Definir senha', route('password.request', ['email' => $this->user->email]))
            ->line('**Importante:** O acesso ao sistema só é possível quando conectado na rede do campus.');
    }
}
