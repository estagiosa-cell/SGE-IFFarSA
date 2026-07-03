<?php

namespace App\Services;

use App\Enums\InternshipStatus;
use App\Models\Course;
use App\Models\Internship;
use App\Models\InternshipAmendment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * Service responsável pelas queries analíticas do módulo de relatórios gerenciais.
 *
 * Extrai dados de auditoria da tabela activity_log (Spatie v5), da tabela
 * internship_amendments e agregações acadêmicas (por curso, notas, concedentes)
 * para gerar métricas de gestão de estágios.
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

    // =========================================================================
    // Fase 2: Queries Acadêmicas e de Curso
    // =========================================================================

    /**
     * Retorna a contagem de estágios por curso, agrupada por status.
     *
     * Usa agregação condicional (CASE WHEN) para retornar todos os status
     * em uma única query eficiente, sem múltiplas subqueries.
     *
     * @param  string|null  $startDate  Filtro por internships.start_date.
     * @param  string|null  $endDate  Filtro por internships.start_date.
     * @return \Illuminate\Support\Collection Coleção com course_name e contagens por status.
     */
    public function getInternshipsByCourse(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Course::query()
            ->select('courses.id', 'courses.name as course_name')
            ->leftJoin('internships', function ($join) use ($startDate, $endDate) {
                $join->on('courses.id', '=', 'internships.course_id')
                    ->whereNull('internships.deleted_at');

                if ($startDate) {
                    $join->where('internships.start_date', '>=', $startDate);
                }
                if ($endDate) {
                    $join->where('internships.start_date', '<=', $endDate);
                }
            });

        foreach (InternshipStatus::cases() as $status) {
            $query->selectRaw(
                "COUNT(CASE WHEN internships.status = ? THEN 1 END) as total_{$this->normalizeStatusKey($status)}",
                [$status->value]
            );
        }

        $query->selectRaw('COUNT(internships.id) as total_geral');

        return $query
            ->groupBy('courses.id', 'courses.name')
            ->orderBy('courses.name')
            ->get();
    }

    /**
     * Calcula a nota média de avaliação dos estágios agrupada por curso.
     *
     * Considera apenas estágios que possuem nota (evaluation_grade não nulo).
     * Também retorna a nota mínima, máxima e quantidade de estágios avaliados.
     *
     * @param  string|null  $startDate  Filtro por internships.start_date.
     * @param  string|null  $endDate  Filtro por internships.start_date.
     * @return \Illuminate\Support\Collection Coleção com course_name, avg, min, max e count.
     */
    public function getAverageGradesByCourse(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Course::query()
            ->select([
                'courses.id',
                'courses.name as course_name',
                DB::raw('ROUND(AVG(internships.evaluation_grade), 2) as nota_media'),
                DB::raw('MIN(internships.evaluation_grade) as nota_minima'),
                DB::raw('MAX(internships.evaluation_grade) as nota_maxima'),
                DB::raw('COUNT(internships.evaluation_grade) as total_avaliados'),
            ])
            ->leftJoin('internships', function ($join) use ($startDate, $endDate) {
                $join->on('courses.id', '=', 'internships.course_id')
                    ->whereNull('internships.deleted_at')
                    ->whereNotNull('internships.evaluation_grade');

                if ($startDate) {
                    $join->where('internships.start_date', '>=', $startDate);
                }
                if ($endDate) {
                    $join->where('internships.start_date', '<=', $endDate);
                }
            })
            ->groupBy('courses.id', 'courses.name')
            ->orderBy('courses.name');

        return $query->get();
    }

    /**
     * Lista as empresas concedentes com mais estagiários vinculados.
     *
     * Agrupa por company_legal_identifier (CNPJ/CPF) já que os dados da empresa
     * são denormalizados na tabela internships (sem FK para companies).
     *
     * @param  string|null  $startDate  Filtro por internships.start_date.
     * @param  string|null  $endDate  Filtro por internships.start_date.
     * @param  int  $limit  Número máximo de resultados (padrão: 20).
     * @return \Illuminate\Support\Collection Coleção com company_name, legal_identifier e total.
     */
    public function getTopCompanies(?string $startDate = null, ?string $endDate = null, int $limit = 20): Collection
    {
        $query = Internship::query()
            ->select([
                'company_legal_identifier',
                DB::raw('MAX(company_name) as company_name'),
                DB::raw('COUNT(*) as total_estagios'),
            ])
            ->whereNotNull('company_legal_identifier')
            ->where('company_legal_identifier', '!=', '');

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date', '<=', $endDate);
        }

        return $query
            ->groupBy('company_legal_identifier')
            ->orderByDesc('total_estagios')
            ->limit($limit)
            ->get();
    }

    /**
     * Normaliza o nome do status do enum para uso como chave de coluna.
     *
     * Ex: InternshipStatus::AWAITING_SIGNATURE ("Aguardando Assinatura") → "aguardando_assinatura"
     */
    private function normalizeStatusKey(InternshipStatus $status): string
    {
        return Str::slug($status->value, '_');
    }
}
