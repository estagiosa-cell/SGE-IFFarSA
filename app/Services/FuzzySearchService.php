<?php

namespace App\Services;

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
     * @param string $model Classe do modelo (ex: User::class)
     * @param string $searchField Campo no qual buscar (ex: 'name')
     * @param string $searchTerm Texto digitado para buscar
     * @param float $minSimilarity Mínimo de similaridade (0.6 = 60%)
     * @param array $additionalWhere Filtros extras no formato ['campo' => 'valor']
     * @return array|null Array com ['entity', 'warning', 'exact_match', 'similarity'] ou null se não encontrar
     */
    public function fuzzyFind(string $model, string $searchField, string $searchTerm, float $minSimilarity = 0.6, array $additionalWhere = [])
    {
        if (! $searchTerm) {
            return null;
        }

        // Etapa 1: busca exata (mais rápido).
        $query = $model::query();

        // Aplica filtros adicionais à query.
        foreach ($additionalWhere as $field => $value) {
            $query->where($field, $value);
        }

        $exactMatch = $query->where($searchField, 'LIKE', $searchTerm)->first();

        // Se encontrou uma correspondência exata, retorna imediatamente.
        if ($exactMatch) {
            return [
                'entity' => $exactMatch,
                'warning' => null,
                'exact_match' => true,
            ];
        }

        // Etapa 2: busca por pedaços do termo (ex: "João Silva" vira ["João", "Silva"]).
        $nameParts = preg_split('/\s+/', $searchTerm);
        $possibleEntities = collect();

        foreach ($nameParts as $part) {
            // Ignora palavras muito curtas (preposições como "de", "da", etc.).
            if (strlen($part) > 3) {
                $query = $model::query();

                // Aplica filtros adicionais à query.
                foreach ($additionalWhere as $field => $value) {
                    $query->where($field, $value);
                }

                // Busca entidades que contenham a parte do termo.
                $entities = $query->where($searchField, 'LIKE', "%{$part}%")->get();

                foreach ($entities as $entity) {
                    $possibleEntities->push($entity);
                }
            }
        }

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
     * @param string $str1 Primeira string para comparação.
     * @param string $str2 Segunda string para comparação.
     * @return float Valor entre 0 e 1 representando a similaridade.
     */
    public function calculateSimilarity($str1, $str2)
    {
        // Normaliza as strings: converte para minúsculo e remove espaços nas extremidades.
        $str1 = mb_strtolower(trim($str1));
        $str2 = mb_strtolower(trim($str2));

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
