<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'coordinator_id'
    ];

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

    /**
     * Retorna os estágios do curso.
     */
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}
