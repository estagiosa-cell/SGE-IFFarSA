<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa um tipo de estágio.
 *
 * Define as características de cada modalidade de estágio disponível
 * em um curso, incluindo carga horária e critérios de avaliação.
 */
class InternshipType extends Model
{
    use SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'required_hours',
        'weight',
        'course_id',
        'great_value',
        'very_good_value',
        'good_value',
        'satisfactory_value',
        'unsatisfactory_value',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight' => 'integer',
    ];

    /**
     * Relacionamento: retorna o curso ao qual este tipo de estágio pertence.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
