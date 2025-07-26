<?php

namespace App\Utils;

class Formatter
{
    /**
     * Formata um CNPJ no padrão 00.000.000/0000-00
     * @param string $cnpj
     * @return string
     */
    public static function formatCnpj($cnpj)
    {
        $digits = preg_replace('/\D/', '', $cnpj);
        if (strlen($digits) !== 14) {
            return $cnpj;
        }
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
    }
}
