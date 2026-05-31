<?php

namespace App\Services;

use App\Utils\SearchHelper;
use Illuminate\Support\Facades\DB;

/**
 * Serviço para realizar buscas tolerantes a erros de digitação.
 *
 * Implementa busca fuzzy (aproximada) usando o algoritmo de distância de Levenshtein
 * para encontrar resultados mesmo quando o usuário comete erros de digitação.
 */
class FuzzySearchService
{
    /**
     * Busca no banco de dados tolerando erros de digitação.
     *
     * Realiza uma busca em três etapas:
     * 1. Busca exata (mais rápida)
     * 2. Busca por partes do termo (divide palavras)
     * 3. Calcula similaridade e retorna a melhor correspondência
     *
     * @param  string  $model  Classe do modelo (ex: User::class)
     * @param  string  $searchField  Campo no qual buscar (ex: 'name')
     * @param  string  $searchTerm  Texto digitado para buscar
     * @param  float  $minSimilarity  Mínimo de similaridade (0.6 = 60%)
     * @param  array  $additionalWhere  Filtros extras no formato ['campo' => 'valor']
     * @return array|null Array com ['entity', 'warning', 'exact_match', 'similarity'] ou null se não encontrar
     */
    public function fuzzyFind(string $model, string $searchField, string $searchTerm, float $minSimilarity = 0.6, array $additionalWhere = [])
    {
        if (! $searchTerm) {
            return null;
        }

        // Etapa 1: busca exata (ou por ILIKE/unaccent quando suportado).
        $query = $model::query();

        // Aplica filtros adicionais à query.
        foreach ($additionalWhere as $field => $value) {
            $query->where($field, $value);
        }

        // Tenta correspondência mais próxima no banco (usando unaccent/ILIKE se disponível).
        $exactQuery = clone $query;
        if (DB::getDriverName() === 'pgsql') {
            $exactQuery = SearchHelper::applyUnaccentSearchIfSupported($exactQuery, $searchTerm, $searchField);
            $exactMatch = $exactQuery->first();
        } else {
            $exactMatch = $exactQuery->where($searchField, 'LIKE', "%{$searchTerm}%")->first();
        }

        // Se encontrou uma correspondência direta no banco, retorna imediatamente.
        if ($exactMatch) {
            return [
                'entity' => $exactMatch,
                'warning' => null,
                'exact_match' => true,
            ];
        }

        // Etapa 2: busca por pedaços do termo (ex: "João Silva" vira ["João", "Silva"]).
        $nameParts = array_filter(preg_split('/\s+/', $searchTerm));
        $possibleEntities = collect();

        foreach ($nameParts as $part) {
            // Ignora palavras muito curtas (preposições como "de", "da", etc.).
            if (mb_strlen($part) > 2) {
                $q = $model::query();

                foreach ($additionalWhere as $field => $value) {
                    $q->where($field, $value);
                }

                // Use applyUnaccentSearchIfSupported para aproveitar unaccent/ILIKE no Postgres,
                // e em bancos sem suporte usa um LIKE simples.
                if (DB::getDriverName() === 'pgsql') {
                    $q = SearchHelper::applyUnaccentSearchIfSupported($q, $part, $searchField);
                } else {
                    $q->where($searchField, 'LIKE', "%{$part}%");
                }

                $entities = $q->get();

                foreach ($entities as $entity) {
                    $possibleEntities->push($entity);
                }
            }
        }

        // Remove duplicados (mesma entidade retornada por diferentes partes).
        $possibleEntities = $possibleEntities->unique(function ($e) {
            return get_class($e).':'.$e->getKey();
        })->values();

        // Etapa 3: escolhe o candidato mais parecido com o termo original.
        if ($possibleEntities->count() > 0) {
            $bestMatch = null;
            $highestScore = 0;
            $originalField = '';

            // Calcula a similaridade de cada candidato com o termo de busca.
            foreach ($possibleEntities as $entity) {
                $fieldValue = $entity->{$searchField};
                $score = $this->calculateSimilarity($searchTerm, $fieldValue);

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $entity;
                    $originalField = $fieldValue;
                }
            }

            // Só aceita o resultado se tiver no mínimo a similaridade especificada.
            if ($highestScore >= $minSimilarity) {
                return [
                    'entity' => $bestMatch,
                    'warning' => "Termo '$searchTerm' possivelmente corrigido para '$originalField'. Verifique se está correto.",
                    'exact_match' => false,
                    'similarity' => $highestScore,
                ];
            }
        }

        // Não encontrou nada com similaridade suficiente.
        return null;
    }

    /**
     * Calcula a similaridade entre duas strings (0 = totalmente diferentes, 1 = idênticas).
     *
     * Usa o algoritmo de distância de Levenshtein, que conta quantas operações
     * (inserção, remoção ou substituição) são necessárias para transformar uma string em outra.
     *
     * @param  string  $str1  Primeira string para comparação.
     * @param  string  $str2  Segunda string para comparação.
     * @return float Valor entre 0 e 1 representando a similaridade.
     */
    public function calculateSimilarity($str1, $str2)
    {
        // Normaliza as strings usando SearchHelper (remove acentos, lower, trim).
        $str1 = SearchHelper::normalize((string) $str1);
        $str2 = SearchHelper::normalize((string) $str2);

        // Calcula a distância de Levenshtein entre as strings.
        $levenshtein = levenshtein($str1, $str2);
        $maxLength = max(mb_strlen($str1), mb_strlen($str2));

        // Se ambas as strings são vazias, considera como idênticas.
        if ($maxLength === 0) {
            return 1.0;
        }

        // Converte a distância em um índice de similaridade (quanto menor a distância, maior a similaridade).
        return 1.0 - ($levenshtein / $maxLength);
    }
}
