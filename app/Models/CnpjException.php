<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnpjException extends Model
{
    protected $fillable = [
        'razao_social',
        'cnpj_matriz',
    ];

    /**
     * Busca exceção de CNPJ pela raiz (primeiros 8 dígitos)
     *
     * @param string|null $cnpj
     * @return self|null
     */
    public static function findByRoot($cnpj)
    {
        // Garante que o CNPJ não é nulo e tem pelo menos 8 dígitos
        if (empty($cnpj) || strlen($cnpj) < 8) {
            return null;
        }

        $raiz = substr($cnpj, 0, 8);
        return self::whereLike('cnpj_matriz', $raiz . '%')->first();
    }
}
