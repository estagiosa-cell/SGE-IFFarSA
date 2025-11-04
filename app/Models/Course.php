<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa um curso da instituição.
 *
 * Gerencia informações dos cursos, incluindo seu coordenador,
 * tipos de estágio associados e estágios em andamento.
 */
class Course extends Model
{
    use SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'coordinator_id'
    ];

    /**
     * Relacionamento: retorna o coordenador do curso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Relacionamento: retorna os tipos de estágio configurados para este curso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function internshipTypes()
    {
        return $this->hasMany(InternshipType::class);
    }

    /**
     * Relacionamento: retorna os estágios vinculados a este curso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}
