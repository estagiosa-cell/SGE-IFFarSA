<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Serviço para realizar buscas tolerantes a erros de digitação.
 *
 * Implementa busca fuzzy (aproximada) usando o algoritmo de distância de Levenshtein
 * para encontrar resultados mesmo quando o usuário comete erros de digitação,
 * combinando com extensões nativas do PostgreSQL (pg_trgm, unaccent) quando disponíveis.
 */
class FuzzySearchService
{
    /**
     * Normaliza uma string removendo acentos e convertendo para minúsculas.
     */
    public function normalize(string $string): string
    {
        return mb_strtolower(trim(Str::ascii($string)));
    }

    /**
     * Busca no banco de dados tolerando erros de digitação e abreviações (ex: Magnos R. Pizzoni).
     *
     * @param  string  $model  Classe do modelo (ex: User::class)
     * @param  string  $searchField  Campo no qual buscar (ex: 'name')
     * @param  string  $searchTerm  Texto digitado para buscar
     * @param  float  $minSimilarity  Mínimo de similaridade (0.4 = 40%)
     * @param  \Closure|null  $queryModifier  Função para adicionar filtros extras na query
     * @return array|null Array com ['entity', 'warning', 'exact_match', 'similarity'] ou null se não encontrar
     */
    public function fuzzyFind(string $model, string $searchField, string $searchTerm, float $minSimilarity = 0.4, ?\Closure $queryModifier = null): ?array
    {
        $searchTerm = trim($searchTerm);

        if (! $searchTerm) {
            return null;
        }

        $query = $model::query();
        if ($queryModifier) {
            $queryModifier($query);
        }

        $isPgsql = DB::getDriverName() === 'pgsql';

        // --- Etapa 1: Busca via pg_trgm (PostgreSQL) ou LIKE (SQLite) ---
        if ($isPgsql) {
            // Busca candidatos usando similaridade >= 0.3 ou substring com unaccent
            $candidatesQuery = (clone $query)->select('*')
                ->selectRaw("similarity(unaccent({$searchField}), unaccent(?)) as sim_score", [$searchTerm])
                ->where(function ($q) use ($searchField, $searchTerm) {
                    $q->whereRaw("similarity(unaccent({$searchField}), unaccent(?)) > 0.3", [$searchTerm])
                        ->orWhereRaw("unaccent({$searchField}) ILIKE unaccent(?)", ["%{$searchTerm}%"]);
                })
                ->orderByRaw("similarity(unaccent({$searchField}), unaccent(?)) DESC", [$searchTerm]);

            $matches = $candidatesQuery->get();
        } else {
            $matches = (clone $query)->where($searchField, 'LIKE', "%{$searchTerm}%")->get();
        }

        $normalizedSearchTerm = $this->normalize($searchTerm);
        $possibleEntities = collect();

        // Adiciona os resultados da primeira etapa aos possíveis candidatos
        foreach ($matches as $match) {
            // Se for PostgreSQL, podemos já validar a similaridade do banco
            if ($isPgsql && isset($match->sim_score)) {
                if ($this->normalize($match->{$searchField}) === $normalizedSearchTerm) {
                    // Match perfeito! Ignora qualquer outra coisa.
                    return ['entity' => $match, 'warning' => null, 'exact_match' => true, 'similarity' => 1.0];
                }
            }
            $possibleEntities->push($match);
        }

        // --- Etapa 2: Busca por pedaços do termo (ideal para abreviações como "Magnos R. Pizzoni") ---
        $nameParts = array_filter(preg_split('/\s+/', $searchTerm));
        $validParts = array_filter($nameParts, fn ($part) => mb_strlen($part) > 2); // ignora 'de', 'do', 'R.' etc sozinho

        if (! empty($validParts)) {
            $q = clone $query;
            $q->where(function ($subQuery) use ($validParts, $searchField, $isPgsql) {
                foreach ($validParts as $index => $part) {
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $methodFallback = $index === 0 ? 'where' : 'orWhere';

                    if ($isPgsql) {
                        $subQuery->{$method}("unaccent({$searchField}) ILIKE unaccent(?)", ["%{$part}%"]);
                    } else {
                        $subQuery->{$methodFallback}($searchField, 'LIKE', "%{$part}%");
                    }
                }
            });
            $entitiesFromParts = $q->get();
            foreach ($entitiesFromParts as $entity) {
                $possibleEntities->push($entity);
            }
        }

        // Remove duplicados
        $possibleEntities = $possibleEntities->unique(function ($e) {
            return get_class($e).':'.$e->getKey();
        })->values();

        // --- Etapa 3: Calcula Levenshtein Similarity e avalia ambiguidade ---
        if ($possibleEntities->count() > 0) {
            $bestMatch = null;
            $secondBestMatch = null;
            $highestScore = 0;
            $secondHighestScore = 0;
            $originalField = '';

            foreach ($possibleEntities as $entity) {
                $fieldValue = $entity->{$searchField};
                // Calcula similaridade híbrida (considera abreviações melhor)
                $score = $this->calculateSimilarity($searchTerm, $fieldValue);

                if ($score > $highestScore) {
                    $secondHighestScore = $highestScore;
                    $secondBestMatch = $bestMatch;

                    $highestScore = $score;
                    $bestMatch = $entity;
                    $originalField = $fieldValue;
                } elseif ($score > $secondHighestScore) {
                    $secondHighestScore = $score;
                    $secondBestMatch = $entity;
                }
            }

            if ($highestScore >= $minSimilarity) {
                // Checa ambiguidade (se a diferença de score para o 2º lugar for mínima e não for match exato)
                if ($secondBestMatch && $highestScore < 0.95 && ($highestScore - $secondHighestScore) < 0.15) {
                    return null; // Ambíguo
                }

                $isExactMatch = $highestScore >= 0.99;

                return [
                    'entity' => $bestMatch,
                    'warning' => $isExactMatch ? null : "O termo informado '$searchTerm' foi associado a '$originalField'. Verifique se está correto.",
                    'exact_match' => $isExactMatch,
                    'similarity' => $highestScore,
                ];
            }
        }

        return null;
    }

    /**
     * Calcula a similaridade entre duas strings usando Levenshtein.
     */
    public function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = $this->normalize($str1);
        $str2 = $this->normalize($str2);

        $levenshtein = levenshtein($str1, $str2);
        $maxLength = max(mb_strlen($str1), mb_strlen($str2));

        if ($maxLength === 0) {
            return 1.0;
        }

        return 1.0 - ($levenshtein / $maxLength);
    }
}
