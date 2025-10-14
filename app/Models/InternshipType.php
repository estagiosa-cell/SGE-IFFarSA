<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipType extends Model
{
    use SoftDeletes;

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
