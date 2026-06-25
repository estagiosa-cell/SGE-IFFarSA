<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipAmendment extends Model
{
    use SoftDeletes;

    protected $fillable = ['internship_id'];

    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }
}
