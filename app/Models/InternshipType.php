<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipType extends Model
{
    protected $fillable = [
        'name',
        'required_hours',
        'course_id',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
