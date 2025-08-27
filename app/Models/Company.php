<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'legal_identifier',
        'name',
        'address_street',
        'address_number',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_zip',
        'representative_name',
        'representative_role',
        'phone',
        'email',
        'field_of_activity',
        'professional_council',
        'council_registration_number',
        'process_number',
    ];

    /**
     * Get the internships for the company.
     */
    public function internships(): HasMany
    {
        return $this->hasMany(Internship::class);
    }
}
