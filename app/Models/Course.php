<?php

namespace App\Models;

use App\Enums\CourseLevel;
use App\Enums\CourseType;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'level',
        'type',
        'coordinator_id'
    ];

    protected $casts = [
        'level' => CourseLevel::class,
        'type' => CourseType::class,
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($course) {
            // Se tem nível E tipo, valida a compatibilidade
            if ($course->level && $course->type) {
                if (!CourseType::isValidForLevel($course->type, $course->level)) {
                    throw new \InvalidArgumentException(
                        "Erro: O tipo '{$course->type->label()}' não pode ser do nível '{$course->level->label()}'"
                    );
                }
            }
        });
    }

    /**
     * Retorna o coordenador do curso.
     */
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
}
