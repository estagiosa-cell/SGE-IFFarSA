<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use App\Traits\Searchable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model que representa um usuário do sistema.
 *
 * Gerencia os dados de autenticação e autorização dos usuários,
 * incluindo administradores, coordenadores e orientadores.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    use Searchable;

    /**
     * Os atributos que podem ser atribuídos em massa.
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
     * Os atributos que devem ser ocultados na serialização.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Obtém os atributos que devem ser convertidos para tipos nativos.
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

    /**
     * Envia a notificação de redefinição de senha para o usuário.
     *
     * Sobrescreve o método padrão do Laravel para usar uma notificação customizada
     * com URL de redefinição incluindo o e-mail do usuário.
     *
     * @param  string  $token  O token de redefinição de senha.
     */
    public function sendPasswordResetNotification($token): void
    {
        // Constrói a URL de forma segura incluindo o token e o e-mail.
        $url = route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        // Envia a notificação customizada com a URL de redefinição de senha.
        $this->notify(new ResetPasswordNotification($url));
    }

    /**
     * Verifica se o usuário está ativo no sistema.
     *
     * @return bool True se o usuário está ativo, false se foi desativado.
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /**
     * Retorna todos os coordenadores ativos do sistema.
     *
     * @return Collection
     */
    public static function coordinators(array $columns = ['id', 'name'])
    {
        return self::select($columns)
            ->where('role', UserRole::COORDENADOR)
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get();
    }

    /**
     * Relacionamento: estágios onde este usuário é orientador.
     *
     * @return HasMany
     */
    public function advisedInternships()
    {
        return $this->hasMany(Internship::class, 'advisor_id');
    }

    /**
     * Relacionamento: cursos onde este usuário é coordenador.
     *
     * @return HasMany
     */
    public function coordinatedCourses()
    {
        return $this->hasMany(Course::class, 'coordinator_id');
    }

    /**
     * Relacionamento: cursos onde este usuário é coordenador secundário.
     *
     * @return HasMany
     */
    public function secondaryCoordinatedCourses()
    {
        return $this->hasMany(Course::class, 'secondary_coordinator_id');
    }
}
