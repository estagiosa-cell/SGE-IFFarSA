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
     * Formata um CNPJ no padrão brasileiro XX.XXX.XXX/XXXX-XX.
     *
     * Suporta tanto o formato numérico tradicional (14 dígitos) quanto o novo
     * formato alfanumérico (Resolução DREI nº 81/2024), em que os dois primeiros
     * grupos podem conter letras maiúsculas.
     *
     * Remove apenas os separadores da máscara (pontos, barra e traço) e converte
     * para maiúsculas. Se o CNPJ não tiver 14 caracteres após a limpeza, retorna
     * o valor original sem formatação.
     *
     * @param  string  $cnpj  O CNPJ a ser formatado (com ou sem máscara).
     * @return string O CNPJ formatado ou o valor original se inválido.
     */
    public static function formatCnpj($cnpj)
    {
        // Remove apenas os separadores da máscara e converte para maiúsculas.
        // Preserva letras para suportar o formato alfanumérico.
        $clean = strtoupper(preg_replace('/[.\-\/]/', '', $cnpj));

        // Verifica se tem exatamente 14 caracteres.
        if (strlen($clean) !== 14) {
            return $cnpj;
        }

        // Aplica a máscara do CNPJ: XX.XXX.XXX/XXXX-XX.
        return substr($clean, 0, 2).'.'.substr($clean, 2, 3).'.'.substr($clean, 5, 3).'/'.substr($clean, 8, 4).'-'.substr($clean, 12, 2);
    }

    /**
     * Formata um CPF no padrão brasileiro 000.000.000-00.
     *
     * Remove todos os caracteres não numéricos e aplica a máscara.
     * Se o CPF não tiver 11 dígitos, retorna o valor original sem formatação.
     *
     * @param  string  $cpf  O CPF a ser formatado (com ou sem máscara).
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

    /**
     * Valida um CNPJ (numérico ou alfanumérico) verificando os dígitos verificadores.
     *
     * Implementa o algoritmo da Instrução Normativa RFB nº 2.229/2024:
     * - Remove apenas os separadores de máscara (ponto, barra, traço).
     * - Os 12 primeiros caracteres podem ser alfanuméricos [A-Z0-9].
     * - Os 2 últimos caracteres (DVs) são sempre numéricos.
     * - Cada caractere é convertido para ord(char) - 48 (tabela ASCII da DREI):
     *   '0'-'9' → 0-9 | 'A'-'Z' → 17-42
     * - Cálculo pelo Módulo 11 com pesos fixos:
     *   DV1: [5,4,3,2,9,8,7,6,5,4,3,2]
     *   DV2: [6,5,4,3,2,9,8,7,6,5,4,3,2]
     *
     * @param  string  $cnpj  O CNPJ a ser validado (com ou sem máscara).
     * @return bool True se o CNPJ for válido, false caso contrário.
     */
    public static function validateCnpj(string $cnpj): bool
    {
        // Remove apenas os separadores da máscara; preserva letras.
        $c = strtoupper(preg_replace('/[.\-\/\s]/', '', $cnpj));

        // Deve ter 14 caracteres: 12 alfanuméricos + 2 dígitos numéricos.
        if (! preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $c)) {
            return false;
        }

        // Rejeita sequências com todos os caracteres idênticos.
        if (preg_match('/^(.)\1{13}$/', $c)) {
            return false;
        }

        // Converte cada caractere para seu valor numérico via tabela ASCII (ord - 48).
        // Dígitos '0'-'9': ord 48-57 → valores 0-9.
        // Letras  'A'-'Z': ord 65-90 → valores 17-42.
        $v = array_map(fn ($ch) => ord($ch) - 48, str_split($c));

        // Calcula o 1º dígito verificador.
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $v[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $dv1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ($v[12] !== $dv1) {
            return false;
        }

        // Calcula o 2º dígito verificador (inclui o DV1 já calculado).
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $v[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $dv2 = $remainder < 2 ? 0 : 11 - $remainder;

        return $v[13] === $dv2;
    }

    /**
     * Valida um CPF verificando os dígitos verificadores (Módulo 11).
     *
     * @param  string  $cpf  O CPF a ser validado (com ou sem máscara).
     * @return bool True se o CPF for válido, false caso contrário.
     */
    public static function validateCpf(string $cpf): bool
    {
        $c = preg_replace('/\D/', '', $cpf);

        if (strlen($c) !== 11 || preg_match('/^(.)\1{10}$/', $c)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $i = 0; $i < $t; $i++) {
                $d += (int) $c[$i] * (($t + 1) - $i);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $c[$t] !== $d) {
                return false;
            }
        }

        return true;
    }
}
