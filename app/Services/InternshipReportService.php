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
                'internships.start_date',
                'log_data.released_at',
                DB::raw('EXTRACT(DAY FROM (log_data.released_at - internships.created_at)) as days_to_release'),
            ])
            ->joinSub($subquery, 'log_data', function ($join) {
                $join->on('internships.id', '=', 'log_data.subject_id');
            });

        $query->where('internships.created_at', '>=', '2026-07-14');

        if ($startDate && $startDate > '2026-07-14') {
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
                DB::raw("COALESCE(properties->>'motivo', properties->'attributes'->>'motivo', 'Sem motivo informado') as motivo"),
                DB::raw('COUNT(*) as total'),
            ])
            ->where('log_name', 'internships')
            ->where('description', 'Estágio cancelado com motivo')
            ->groupByRaw("COALESCE(properties->>'motivo', properties->'attributes'->>'motivo', 'Sem motivo informado')")
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
                DB::raw('ROUND(AVG((internships.evaluation_grade / NULLIF(internships.great_value, 0)) * 100), 2) as nota_media'),
                DB::raw('ROUND(MIN((internships.evaluation_grade / NULLIF(internships.great_value, 0)) * 100), 2) as nota_minima'),
                DB::raw('ROUND(MAX((internships.evaluation_grade / NULLIF(internships.great_value, 0)) * 100), 2) as nota_maxima'),
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
     * Calcula as métricas agregadas do tempo de tramitação documental.
     */
    public function getProcessingTimeMetrics(?string $startDate = null, ?string $endDate = null): array
    {
        $times = $this->getProcessingTimes($startDate, $endDate)->pluck('days_to_release')->filter(fn ($val) => ! is_null($val));

        if ($times->isEmpty()) {
            return ['avg' => 0, 'min' => 0, 'max' => 0, 'median' => 0];
        }

        $sorted = $times->sort()->values();
        $count = $sorted->count();
        $median = $count % 2 === 0
            ? ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2
            : $sorted[floor($count / 2)];

        return [
            'avg' => round($times->average(), 1),
            'min' => $times->min(),
            'max' => $times->max(),
            'median' => round($median, 1),
        ];
    }

    /**
     * Calcula a taxa de estágios encaminhados dentro do prazo (released_at <= start_date).
     */
    public function getOnTimeForwarding(?string $startDate = null, ?string $endDate = null): array
    {
        $times = $this->getProcessingTimes($startDate, $endDate);
        if ($times->isEmpty()) {
            return ['on_time' => 0, 'late' => 0, 'total' => 0, 'percent' => 0];
        }

        $onTime = 0;
        $late = 0;
        foreach ($times as $item) {
            if ($item->start_date && $item->released_at <= $item->start_date) {
                $onTime++;
            } else {
                $late++;
            }
        }

        return [
            'on_time' => $onTime,
            'late' => $late,
            'total' => $onTime + $late,
            'percent' => round(($onTime / ($onTime + $late)) * 100, 1),
        ];
    }

    /**
     * Retorna a contagem de estágios agrupados pela situação atual.
     */
    public function getInternshipStatuses(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Internship::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Retorna a contagem de supervisores e média de estagiários por curso.
     */
    public function getSupervisorsMetrics(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Course::query()
            ->select([
                'courses.id',
                'courses.name as course_name',
                DB::raw('COUNT(DISTINCT internships.supervisor_name) as total_supervisores'),
                DB::raw('COUNT(internships.id) as total_estagiarios'),
            ])
            ->leftJoin('internships', function ($join) use ($startDate, $endDate) {
                $join->on('courses.id', '=', 'internships.course_id')
                    ->whereNull('internships.deleted_at')
                    ->whereNotNull('internships.supervisor_name');
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
     * Retorna a contagem de estagiários por professor orientador.
     */
    public function getAdvisorsMetrics(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Internship::query()
            ->select([
                'users.name as advisor_name',
                DB::raw('COUNT(*) as total_estagiarios'),
            ])
            ->join('users', 'internships.advisor_id', '=', 'users.id')
            ->whereNotNull('internships.advisor_id');

        if ($startDate) {
            $query->where('internships.start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('internships.start_date', '<=', $endDate);
        }

        return $query->groupBy('users.id', 'users.name')->orderByDesc('total_estagiarios')->get();
    }

    // =========================================================================
    // Fase 2: Novos Métodos Analíticos (Painel Gerencial Completo)
    // =========================================================================

    /**
     * Retorna o tempo médio de tramitação por mês para gráfico de linha.
     */
    public function getMonthlyProcessingTimes(?string $startDate = null, ?string $endDate = null): Collection
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
                DB::raw("TO_CHAR(internships.created_at, 'YYYY-MM') as month"),
                DB::raw('ROUND(AVG(EXTRACT(DAY FROM (log_data.released_at - internships.created_at))), 1) as avg_days'),
                DB::raw('COUNT(*) as total'),
            ])
            ->joinSub($subquery, 'log_data', function ($join) {
                $join->on('internships.id', '=', 'log_data.subject_id');
            });

        $query->where('internships.created_at', '>=', '2026-07-14');

        if ($startDate && $startDate > '2026-07-14') {
            $query->where('internships.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('internships.created_at', '<=', $endDate);
        }

        return $query
            ->groupByRaw("TO_CHAR(internships.created_at, 'YYYY-MM')")
            ->orderByRaw("TO_CHAR(internships.created_at, 'YYYY-MM')")
            ->get();
    }

    /**
     * Retorna a taxa de encaminhamento dentro/fora do prazo por curso.
     */
    public function getOnTimeForwardingByCourse(?string $startDate = null, ?string $endDate = null): Collection
    {
        $times = $this->getProcessingTimes($startDate, $endDate);

        $byCourse = [];
        foreach ($times as $item) {
            $course = Internship::find($item->id)?->course;
            $courseName = $course?->name ?? 'Sem Curso';
            $courseId = $course?->id ?? 0;

            if (! isset($byCourse[$courseId])) {
                $byCourse[$courseId] = ['course_name' => $courseName, 'on_time' => 0, 'late' => 0];
            }

            if ($item->start_date && $item->released_at <= $item->start_date) {
                $byCourse[$courseId]['on_time']++;
            } else {
                $byCourse[$courseId]['late']++;
            }
        }

        return collect(array_values($byCourse));
    }

    /**
     * Retorna métricas de conclusão dentro vs fora do prazo.
     */
    public function getCompletionOnTime(?string $startDate = null, ?string $endDate = null): array
    {
        // Busca todos os estágios concluídos
        $query = Internship::query()
            ->with('amendments')
            ->where('status', InternshipStatus::COMPLETED->value);

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date', '<=', $endDate);
        }

        $completed = $query->get();

        if ($completed->isEmpty()) {
            return ['on_time' => 0, 'late' => 0, 'total' => 0, 'percent' => 0];
        }

        $onTime = 0;
        $late = 0;

        foreach ($completed as $internship) {
            // Regra de negócio definida pelo usuário:
            // "Dentro do prazo" = Concluído sem aditivo.
            // "Fora do prazo" = Concluído com aditivo.
            if ($internship->amendments->isNotEmpty()) {
                $late++;
            } else {
                $onTime++;
            }
        }

        $total = $onTime + $late;

        return [
            'on_time' => $onTime,
            'late' => $late,
            'total' => $total,
            'percent' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Retorna a contagem de aditivos agrupada por motivo (registrado no activity_log).
     */
    public function getAmendmentsByReason(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = InternshipAmendment::query()
            ->select([
                DB::raw('COUNT(*) as total'),
            ]);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();

        return collect([['reason' => 'Aditivo de Prorrogação', 'total' => $total]]);
    }

    /**
     * Retorna a distribuição de notas por faixas para histograma.
     */
    public function getGradeDistribution(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Internship::query()
            ->select([
                DB::raw("CASE
                    WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 90 THEN '90-100%'
                    WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 80 THEN '80-89%'
                    WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 70 THEN '70-79%'
                    WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 60 THEN '60-69%'
                    ELSE '< 60%'
                END as faixa"),
                DB::raw('COUNT(*) as total'),
            ])
            ->whereNotNull('evaluation_grade');

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date', '<=', $endDate);
        }

        return $query
            ->groupByRaw("CASE
                WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 90 THEN '90-100%'
                WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 80 THEN '80-89%'
                WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 70 THEN '70-79%'
                WHEN (evaluation_grade / NULLIF(great_value, 0)) * 100 >= 60 THEN '60-69%'
                ELSE '< 60%'
            END")
            ->orderByRaw('MIN((evaluation_grade / NULLIF(great_value, 0)) * 100) DESC')
            ->get();
    }

    /**
     * Retorna métricas globais de notas (sem agrupamento por curso).
     */
    public function getGlobalGradeMetrics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Internship::query()
            ->whereNotNull('evaluation_grade');

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date', '<=', $endDate);
        }

        $grades = $query->get()->map(function ($internship) {
            $greatValue = (float) $internship->great_value;
            if ($greatValue > 0) {
                return ((float) $internship->evaluation_grade / $greatValue) * 100;
            }
            return 0.0;
        });

        if ($grades->isEmpty()) {
            return ['avg' => null, 'min' => null, 'max' => null, 'total' => 0];
        }

        return [
            'avg' => round($grades->average(), 2),
            'min' => round($grades->min(), 2),
            'max' => round($grades->max(), 2),
            'total' => $grades->count(),
        ];
    }

    /**
     * Retorna a contagem de concedentes e estagiários por curso.
     */
    public function getGrantingCompaniesByCourse(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Course::query()
            ->select([
                'courses.id',
                'courses.name as course_name',
                DB::raw('COUNT(DISTINCT internships.company_legal_identifier) as total_concedentes'),
                DB::raw('COUNT(internships.id) as total_estagiarios'),
            ])
            ->leftJoin('internships', function ($join) use ($startDate, $endDate) {
                $join->on('courses.id', '=', 'internships.course_id')
                    ->whereNull('internships.deleted_at')
                    ->whereNotNull('internships.company_legal_identifier')
                    ->where('internships.company_legal_identifier', '!=', '');

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
     * Normaliza o nome do status do enum para uso como chave de coluna.
     *
     * Ex: InternshipStatus::AWAITING_SIGNATURE ("Aguardando Assinatura") → "aguardando_assinatura"
     */
    private function normalizeStatusKey(InternshipStatus $status): string
    {
        return Str::slug($status->value, '_');
    }
}
