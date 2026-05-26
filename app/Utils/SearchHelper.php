<?php

namespace App\Utils;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     * @param callable|array|string $field
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
        $fieldAccessor = is_callable($field) ? $field : null;
        $fields = is_array($field) ? $field : [$field];

        return $items->filter(function ($item) use ($fieldAccessor, $fields, $normalizedWords) {
            $values = [];
            if ($fieldAccessor) {
                $values[] = (string) $fieldAccessor($item);
            } else {
                foreach ($fields as $fieldName) {
                    $values[] = (string) data_get($item, $fieldName);
                }
            }

            $normalizedValues = array_map([self::class, 'normalize'], $values);

            foreach ($normalizedWords as $word) {
                if ($word === '') {
                    continue;
                }

                $found = false;
                foreach ($normalizedValues as $value) {
                    if ($value !== '' && str_contains($value, $word)) {
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * Aplica a busca e pagina, usando unaccent no Postgres ou fallback em Collection.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param string|null $searchTerm
     * @param callable|array|string $fields
     * @param int $perPage
     * @param string $pageName
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function searchAndPaginate($query, Request $request, ?string $searchTerm, $fields, int $perPage = 100, string $pageName = 'page'): LengthAwarePaginator
    {
        $searchTerm = (string) $searchTerm;
        if (trim($searchTerm) === '') {
            return $query->paginate($perPage, ['*'], $pageName);
        }

        if (self::isPostgres()) {
            self::applyUnaccentSearch($query, $searchTerm, $fields);

            return $query->paginate($perPage, ['*'], $pageName);
        }

        $items = $query->get();
        $items = self::filterCollectionByNormalizedWords($items, $searchTerm, $fields);

        return self::paginateCollection($items, $perPage, $request, $pageName);
    }

    /**
     * Aplica unaccent quando o driver suporta.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @param array|string $fields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyUnaccentSearchIfSupported($query, string $searchTerm, $fields)
    {
        if (! self::isPostgres()) {
            return $query;
        }

        return self::applyUnaccentSearch($query, $searchTerm, $fields);
    }

    /**
     * Verifica se o driver atual e Postgres.
     */
    private static function isPostgres(): bool
    {
        return DB::getDriverName() === 'pgsql';
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
