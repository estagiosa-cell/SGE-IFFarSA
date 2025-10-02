<?php

namespace App\Services;

class FuzzySearchService
{
    /**
     * Busca no banco tolerando erros de digitação
     *
     * @param  string  $model  Classe do modelo (ex: User::class)
     * @param  string  $searchField  Campo para buscar (ex: 'name')
     * @param  string  $searchTerm  Texto digitado para buscar
     * @param  float  $minSimilarity  Mínimo de 0.6 = 60% de similaridade
     * @param  array  $additionalWhere  Filtros extras ['campo' => 'valor']
     * @return array|null ['entity', 'warning', 'exact_match', 'similarity'] ou null
     */
    public function fuzzyFind(string $model, string $searchField, string $searchTerm, float $minSimilarity = 0.6, array $additionalWhere = [])
    {
        if (! $searchTerm) {
            return null;
        }

        // Etapa 1: busca exata (mais rápido)
        $query = $model::query();

        foreach ($additionalWhere as $field => $value) {
            $query->where($field, $value);
        }

        $exactMatch = $query->where($searchField, 'LIKE', $searchTerm)->first();

        if ($exactMatch) {
            return [
                'entity' => $exactMatch,
                'warning' => null,
                'exact_match' => true,
            ];
        }

        // Etapa 2: busca por pedaços ("João Silva" vira ["João", "Silva"])
        $nameParts = preg_split('/\s+/', $searchTerm);
        $possibleEntities = collect();

        foreach ($nameParts as $part) {
            if (strlen($part) > 3) { // ignora palavras tipo "de", "da"
                $query = $model::query();

                foreach ($additionalWhere as $field => $value) {
                    $query->where($field, $value);
                }

                $entities = $query->where($searchField, 'LIKE', "%{$part}%")->get();

                foreach ($entities as $entity) {
                    $possibleEntities->push($entity);
                }
            }
        }

        // Etapa 3: escolhe o mais parecido dos candidatos
        if ($possibleEntities->count() > 0) {
            $bestMatch = null;
            $highestScore = 0;
            $originalField = '';

            foreach ($possibleEntities as $entity) {
                $fieldValue = $entity->{$searchField};
                $score = $this->calculateSimilarity($searchTerm, $fieldValue);

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $entity;
                    $originalField = $fieldValue;
                }
            }

            // só aceita se tiver no mínimo X% de similaridade
            if ($highestScore >= $minSimilarity) {
                return [
                    'entity' => $bestMatch,
                    'warning' => "Termo '$searchTerm' possivelmente corrigido para '$originalField'. Verifique se está correto.",
                    'exact_match' => false,
                    'similarity' => $highestScore,
                ];
            }
        }

        // não encontrou nada com similaridade suficiente
        return null;
    }

    /**
     * Calcula quanto duas strings são parecidas (0 = nada, 1 = idênticas)
     *
     * Usa algoritmo Levenshtein - conta quantas letras precisa mudar
     */
    public function calculateSimilarity($str1, $str2)
    {
        // deixa tudo minúsculo e sem espaços no inicio e fim
        $str1 = mb_strtolower(trim($str1));
        $str2 = mb_strtolower(trim($str2));

        // calcula quantas mudanças precisa fazer
        $levenshtein = levenshtein($str1, $str2);
        $maxLength = max(mb_strlen($str1), mb_strlen($str2));

        if ($maxLength === 0) {
            return 1.0; // ambas vazias = iguais
        }

        return 1.0 - ($levenshtein / $maxLength);
    }
}
