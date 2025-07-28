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

    /**
     * Cria uma nova instância de notificação.
     *
     * @param  mixed  $user  Usuário que receberá a notificação
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Obtém os canais de entrega da notificação.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable = null)
    {
        return ['mail'];
    }

    /**
     * Obtém a representação da notificação para o canal de e-mail.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable = null): MailMessage
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
