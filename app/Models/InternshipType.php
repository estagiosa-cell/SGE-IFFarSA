<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipType extends Model
{
    protected $fillable = [
        'name',
        'required_hours',
        'weight',
        'course_id',
    ];

    protected $casts = [
        'weight' => 'integer',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
