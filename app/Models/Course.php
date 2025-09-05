<?php

namespace App\Models;

use App\Enums\CourseLevel;
use App\Enums\CourseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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
                    throw ValidationException::withMessages([
                        'type' => "O tipo '{$course->type->label()}' não é válido para o nível '{$course->level->label()}'."
                    ]);
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

    /**
     * Retorna os tipos de estágio do curso.
     */
    public function internshipTypes()
    {
        return $this->hasMany(InternshipType::class);
    }
}
