<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificação para redefinição de senha do usuário.
 *
 * Envia um e-mail com um link para redefinir a senha, incluindo
 * informações sobre expiração e requisitos de acesso.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * A URL de redefinição de senha.
     *
     * @var string
     */
    public $url;

    /**
     * Cria uma nova instância da notificação.
     *
     * @param  string  $url  A URL para redefinição de senha.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Define os canais de entrega da notificação.
     *
     * @param  object  $notifiable  A entidade que receberá a notificação.
     * @return array<int, string> Array com os canais de entrega.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Constrói a representação em e-mail da notificação.
     *
     * Define o assunto, conteúdo e ações do e-mail de redefinição de senha.
     *
     * @param  object  $notifiable  A entidade que receberá a notificação.
     * @return \Illuminate\Notifications\Messages\MailMessage A mensagem de e-mail formatada.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Redefinição de Senha')
            ->line('Você está recebendo este e-mail porque recebemos um pedido de redefinição de senha para sua conta.')
            ->action('Redefinir Senha', $this->url) // Usa a URL customizada.
            ->line('Este link de redefinição de senha irá expirar em '.config('auth.passwords.users.expire').' minutos.')
            ->line('**Importante:** O acesso ao sistema só é possível quando conectado na rede do campus.')
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.');
    }

    /**
     * Define a representação em array da notificação.
     *
     * Usado quando a notificação é armazenada no banco de dados.
     *
     * @param  object  $notifiable  A entidade que receberá a notificação.
     * @return array<string, mixed> Array com os dados da notificação.
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
