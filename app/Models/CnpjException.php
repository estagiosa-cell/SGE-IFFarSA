<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnpjException extends Model
{
    protected $fillable = [
        'razao_social',
        'cnpj_matriz',
    ];
}
