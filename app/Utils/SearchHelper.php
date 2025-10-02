<?php

namespace App\Utils;

class SearchHelper
{
    /**
     * Normaliza uma string: remove acentos, converte para minúsculo e faz trim
     */
    public static function normalize(string $string): string
    {
        // Trim (remove espaços no início e fim)
        $texto = trim($string);

        // Converte para minúsculo
        $texto = mb_strtolower($texto);

        // Converte o texto para ASCII com transliteração, removendo acentos
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        // Remove caracteres especiais que possam restar (como ')
        $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);

        return $texto;
    }

    /**
     * Aplica busca avançada em uma query do Laravel
     * - Ignora acentos
     * - Busca por palavras separadas (cada palavra deve estar presente)
     * - Busca em múltiplos campos
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array|string  $fields  - Campo(s) onde buscar
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applySearch($query, string $searchTerm, $fields)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        // Garante que $fields seja um array
        $fields = is_array($fields) ? $fields : [$fields];

        // Divide o termo de busca em palavras
        $words = array_filter(explode(' ', trim($searchTerm)));

        return $query->where(function ($q) use ($words, $fields) {
            foreach ($words as $word) {
                // Normaliza a palavra (remove acentos, minúsculo, trim)
                $normalizedWord = self::normalize($word);

                $q->where(function ($subQuery) use ($normalizedWord, $fields, $word) {
                    foreach ($fields as $field) {
                        // Busca tanto a palavra original quanto a normalizada
                        // Isso garante compatibilidade enquanto mantém performance
                        $subQuery->where($field, 'like', '%'.$word.'%')
                            ->orWhere($field, 'like', '%'.$normalizedWord.'%');
                    }
                });
            }
        });
    }

    /**
     * Versão simplificada da busca para um único campo
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function searchInField($query, string $searchTerm, string $field)
    {
        return self::applySearch($query, $searchTerm, $field);
    }

    /**
     * Versão simplificada da busca para múltiplos campos
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function searchInFields($query, string $searchTerm, array $fields)
    {
        return self::applySearch($query, $searchTerm, $fields);
    }
}
