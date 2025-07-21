<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'level',
        'type',
        'coordinator_id'
    ];

    /**
     * Retorna o coordenador do curso.
     */
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
}
