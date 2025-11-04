<?php

namespace App\Utils;

/**
 * Utilitário para realizar buscas avançadas em queries do Laravel.
 *
 * Fornece métodos para busca que ignoram acentos, permitem múltiplas palavras
 * e funcionam em um ou mais campos simultaneamente.
 */
class SearchHelper
{
    /**
     * Normaliza uma string removendo acentos, convertendo para minúsculo e fazendo trim.
     *
     * Utilizado para melhorar a busca, tornando-a insensível a acentos e maiúsculas.
     *
     * @param string $string A string a ser normalizada.
     * @return string A string normalizada.
     */
    public static function normalize(string $string): string
    {
        // Remove espaços no início e fim.
        $texto = trim($string);

        // Converte para minúsculo.
        $texto = mb_strtolower($texto);

        // Converte o texto para ASCII com transliteração, removendo acentos.
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        // Remove caracteres especiais que possam restar (como apóstrofos).
        $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);

        return $texto;
    }

    /**
     * Aplica busca avançada em uma query do Laravel.
     *
     * Características da busca:
     * - Ignora acentos
     * - Divide o termo em palavras e busca todas elas
     * - Pode buscar em múltiplos campos simultaneamente
     *
     * @param \Illuminate\Database\Eloquent\Builder $query A query do Eloquent.
     * @param string $searchTerm O termo a ser buscado.
     * @param array|string $fields Campo(s) onde buscar.
     * @return \Illuminate\Database\Eloquent\Builder A query com os filtros de busca aplicados.
     */
    public static function applySearch($query, string $searchTerm, $fields)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        // Garante que $fields seja um array.
        $fields = is_array($fields) ? $fields : [$fields];

        // Divide o termo de busca em palavras.
        $words = array_filter(explode(' ', trim($searchTerm)));

        return $query->where(function ($q) use ($words, $fields) {
            // Para cada palavra, aplica a busca.
            foreach ($words as $word) {
                // Normaliza a palavra (remove acentos, minúsculo, trim).
                $normalizedWord = self::normalize($word);

                $q->where(function ($subQuery) use ($normalizedWord, $fields, $word) {
                    foreach ($fields as $field) {
                        // Busca tanto a palavra original quanto a normalizada.
                        // Isso garante compatibilidade enquanto mantém performance.
                        $subQuery->where($field, 'like', '%'.$word.'%')
                            ->orWhere($field, 'like', '%'.$normalizedWord.'%');
                    }
                });
            }
        });
    }

    /**
     * Versão simplificada da busca para um único campo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query A query do Eloquent.
     * @param string $searchTerm O termo a ser buscado.
     * @param string $field O campo onde buscar.
     * @return \Illuminate\Database\Eloquent\Builder A query com os filtros de busca aplicados.
     */
    public static function searchInField($query, string $searchTerm, string $field)
    {
        return self::applySearch($query, $searchTerm, $field);
    }

    /**
     * Versão simplificada da busca para múltiplos campos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query A query do Eloquent.
     * @param string $searchTerm O termo a ser buscado.
     * @param array $fields Os campos onde buscar.
     * @return \Illuminate\Database\Eloquent\Builder A query com os filtros de busca aplicados.
     */
    public static function searchInFields($query, string $searchTerm, array $fields)
    {
        return self::applySearch($query, $searchTerm, $fields);
    }
}
