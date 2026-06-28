<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa um curso da instituição.
 *
 * Gerencia informações dos cursos, incluindo seu coordenador,
 * tipos de estágio associados e estágios em andamento.
 */
class Course extends Model
{
    use Searchable;
    use SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'coordinator_id',
        'secondary_coordinator_id',
    ];

    /**
     * Relacionamento: retorna o coordenador do curso.
     *
     * @return BelongsTo
     */
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Relacionamento: retorna o coordenador secundário do curso.
     *
     * @return BelongsTo
     */
    public function secondaryCoordinator()
    {
        return $this->belongsTo(User::class, 'secondary_coordinator_id');
    }

    /**
     * Relacionamento: retorna os tipos de estágio configurados para este curso.
     *
     * @return HasMany
     */
    public function internshipTypes()
    {
        return $this->hasMany(InternshipType::class);
    }

    /**
     * Relacionamento: retorna os estágios vinculados a este curso.
     *
     * @return HasMany
     */
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}
