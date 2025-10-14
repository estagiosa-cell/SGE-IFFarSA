<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

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
     * Relacionamento baseado em company_legal_identifier (não há FK)
     */
    public function internships()
    {
        return Internship::where('company_legal_identifier', $this->legal_identifier);
    }
}
