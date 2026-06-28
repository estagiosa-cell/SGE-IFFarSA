<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model que representa um período de pausa em um estágio.
 *
 * Pausas são períodos em que o estagiário não acumula horas.
 * O cálculo da data de término do estágio ignora os dias
 * que caem dentro de um período de pausa.
 */
class InternshipPause extends Model
{
    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internship_id',
        'start_date',
        'end_date',
        'reason',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relacionamento: retorna o estágio ao qual esta pausa pertence.
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }
}
