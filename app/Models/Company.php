<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa uma empresa concedente de estágios.
 *
 * Armazena informações cadastrais de empresas que oferecem
 * oportunidades de estágio aos alunos.
 */
class Company extends Model
{
    use SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa.
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
     * Retorna os estágios associados a esta empresa.
     *
     * Relacionamento baseado no identificador legal (CNPJ/CPF) sem chave estrangeira formal,
     * pois os estágios podem ser criados sem cadastrar previamente a empresa.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function internships()
    {
        return Internship::where('company_legal_identifier', $this->legal_identifier);
    }
}
