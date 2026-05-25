<?php

namespace App\Utils;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
     * Aplica busca por unaccent no Postgres para um ou mais campos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @param array|string $fields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyUnaccentSearch($query, string $searchTerm, $fields)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $fields = is_array($fields) ? $fields : [$fields];
        $words = array_filter(preg_split('/\s+/', trim($searchTerm)));

        if (empty($words)) {
            return $query;
        }

        return $query->where(function ($q) use ($words, $fields) {
            foreach ($words as $word) {
                $q->where(function ($subQuery) use ($fields, $word) {
                    foreach (array_values($fields) as $index => $field) {
                        $wrappedField = $subQuery->getQuery()->getGrammar()->wrap($field);
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $subQuery->{$method}(
                            "unaccent({$wrappedField}) ILIKE unaccent(?)",
                            ['%'.$word.'%']
                        );
                    }
                });
            }
        });
    }

    /**
     * Filtra uma Collection comparando palavras normalizadas contra um campo.
     *
     * @param \Illuminate\Support\Collection $items
     * @param string $searchTerm
     * @param callable|string $field
     * @return \Illuminate\Support\Collection
     */
    public static function filterCollectionByNormalizedWords(Collection $items, string $searchTerm, $field): Collection
    {
        if (trim($searchTerm) === '') {
            return $items;
        }

        $words = array_filter(preg_split('/\s+/', trim($searchTerm)));
        if (empty($words)) {
            return $items;
        }

        $normalizedWords = array_map([self::class, 'normalize'], $words);
        $fieldAccessor = is_callable($field)
            ? $field
            : static fn ($item) => data_get($item, $field);

        return $items->filter(function ($item) use ($fieldAccessor, $normalizedWords) {
            $value = (string) $fieldAccessor($item);
            $normalizedValue = self::normalize($value);

            foreach ($normalizedWords as $word) {
                if ($word === '') {
                    continue;
                }

                if (! str_contains($normalizedValue, $word)) {
                    return false;
                }
            }

            return true;
        })->values();
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

    /**
     * Cria um paginador para uma Collection preservando a pagina atual.
     *
     * @param \Illuminate\Support\Collection $items
     * @param int $perPage
     * @param \Illuminate\Http\Request $request
     * @param string $pageName
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function paginateCollection(Collection $items, int $perPage, Request $request, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $items = $items->values();
        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator($results, $items->count(), $perPage, $page, [
            'path' => $request->url(),
            'pageName' => $pageName,
        ]);
    }
}
