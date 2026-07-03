<?php

namespace App\Services;

use App\Models\Internship;
use App\Models\InternshipAmendment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Service responsável pelas queries analíticas do módulo de relatórios gerenciais.
 *
 * Extrai dados de auditoria da tabela activity_log (Spatie v5) e da tabela
 * internship_amendments para gerar métricas de tempo de tramitação,
 * cancelamentos agrupados por motivo e contagem de aditivos.
 */
class InternshipReportService
{
    /**
     * Calcula o tempo de tramitação (em dias) entre a criação do estágio
     * e a primeira mudança de status para "Liberado" ou "Em Andamento".
     *
     * Estágios que ainda não atingiram nenhum desses status não são incluídos.
     *
     * @param  string|null  $startDate  Data inicial do filtro (internships.created_at).
     * @param  string|null  $endDate  Data final do filtro (internships.created_at).
     * @return \Illuminate\Support\Collection Coleção com id, student_name, created_at, released_at e days_to_release.
     */
    public function getProcessingTimes(?string $startDate = null, ?string $endDate = null): Collection
    {
        $subquery = Activity::query()
            ->select('subject_id', DB::raw('MIN(created_at) as released_at'))
            ->where('subject_type', (new Internship)->getMorphClass())
            ->where('log_name', 'internships')
            ->where(function ($q) {
                $q->whereRaw("attribute_changes->'attributes'->>'status' = ?", ['Liberado'])
                    ->orWhereRaw("attribute_changes->'attributes'->>'status' = ?", ['Em Andamento']);
            })
            ->groupBy('subject_id');

        $query = Internship::query()
            ->select([
                'internships.id',
                'internships.student_name',
                'internships.created_at',
                'log_data.released_at',
                DB::raw("EXTRACT(DAY FROM (log_data.released_at - internships.created_at)) as days_to_release"),
            ])
            ->joinSub($subquery, 'log_data', function ($join) {
                $join->on('internships.id', '=', 'log_data.subject_id');
            });

        if ($startDate) {
            $query->where('internships.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('internships.created_at', '<=', $endDate);
        }

        return $query->orderBy('days_to_release', 'desc')->get();
    }

    /**
     * Busca os cancelamentos com motivo e agrupa por tipo de motivo.
     *
     * Retorna apenas os cancelamentos que possuem motivo registrado
     * manualmente via activity() facade (description = 'Estágio cancelado com motivo').
     *
     * @param  string|null  $startDate  Data inicial do filtro (activity_log.created_at).
     * @param  string|null  $endDate  Data final do filtro (activity_log.created_at).
     * @return \Illuminate\Support\Collection Coleção com motivo e total.
     */
    public function getCancellationsByReason(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Activity::query()
            ->select([
                DB::raw("COALESCE(properties->>'motivo', 'Sem motivo informado') as motivo"),
                DB::raw('COUNT(*) as total'),
            ])
            ->where('log_name', 'internships')
            ->where('description', 'Estágio cancelado com motivo')
            ->groupByRaw("COALESCE(properties->>'motivo', 'Sem motivo informado')")
            ->orderByDesc('total');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Conta quantos estágios únicos possuem aditivos em um período.
     *
     * Utiliza a tabela internship_amendments existente em produção.
     *
     * @param  string|null  $startDate  Data inicial do filtro (internship_amendments.created_at).
     * @param  string|null  $endDate  Data final do filtro (internship_amendments.created_at).
     * @return int Quantidade de estágios distintos com pelo menos um aditivo no período.
     */
    public function getAmendmentsCount(?string $startDate = null, ?string $endDate = null): int
    {
        $query = InternshipAmendment::query()
            ->distinct('internship_id');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->count('internship_id');
    }
}
