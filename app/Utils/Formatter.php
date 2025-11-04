<?php

namespace App\Utils;

/**
 * Utilitário para formatação de dados comuns do sistema.
 *
 * Fornece métodos estáticos para formatar documentos brasileiros
 * (CPF, CNPJ) no padrão legível com pontuação.
 */
class Formatter
{
    /**
     * Formata um CNPJ no padrão brasileiro 00.000.000/0000-00.
     *
     * Remove todos os caracteres não numéricos e aplica a máscara.
     * Se o CNPJ não tiver 14 dígitos, retorna o valor original sem formatação.
     *
     * @param string $cnpj O CNPJ a ser formatado (com ou sem máscara).
     * @return string O CNPJ formatado ou o valor original se inválido.
     */
    public static function formatCnpj($cnpj)
    {
        // Remove todos os caracteres não numéricos.
        $digits = preg_replace('/\D/', '', $cnpj);
        
        // Verifica se tem exatamente 14 dígitos.
        if (strlen($digits) !== 14) {
            return $cnpj;
        }
        
        // Aplica a máscara do CNPJ.
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
    }

    /**
     * Formata um CPF no padrão brasileiro 000.000.000-00.
     *
     * Remove todos os caracteres não numéricos e aplica a máscara.
     * Se o CPF não tiver 11 dígitos, retorna o valor original sem formatação.
     *
     * @param string $cpf O CPF a ser formatado (com ou sem máscara).
     * @return string O CPF formatado ou o valor original se inválido.
     */
    public static function formatCpf($cpf)
    {
        // Remove todos os caracteres não numéricos.
        $digits = preg_replace('/\D/', '', $cpf);
        
        // Verifica se tem exatamente 11 dígitos.
        if (strlen($digits) !== 11) {
            return $cpf;
        }
        
        // Aplica a máscara do CPF.
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
    }
}
