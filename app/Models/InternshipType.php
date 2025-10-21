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
        'great_value',
        'very_good_value',
        'good_value',
        'satisfactory_value',
        'unsatisfactory_value',
    ];

    protected $casts = [
        'weight' => 'integer',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
