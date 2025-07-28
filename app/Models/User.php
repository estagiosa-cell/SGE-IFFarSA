<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\WelcomeNotification;
use App\Enums\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'deactivated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'deactivated_at' => 'datetime',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        // Constrói a URL de forma segura e com o e-mail
        $url = route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        // Envia a notificação com a URL de redefinição de senha
        $this->notify(new ResetPasswordNotification($url));
    }

    public function sendWelcomeNotification(): void
    {
        $this->notify(new WelcomeNotification($this));
    }

    /**
     * Verifica se o usuário está ativo.
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /**
     * Retorna todos os coordenadores ativos
     */
    public static function coordinators()
    {
        return self::where('role', UserRole::COORDENADOR)
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get();
    }
}
