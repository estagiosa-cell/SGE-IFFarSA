<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'internship_id',
        'recipient',
        'subject_type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the internship associated with the email log.
     */
    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }
}
