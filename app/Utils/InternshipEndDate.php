<?php

namespace App\Utils;

use Carbon\Carbon;

/**
 * Utilitário para calcular datas de término de estágios.
 *
 * Leva em consideração feriados brasileiros, carga horária semanal
 * e adiciona uma margem de segurança ao cálculo.
 */
class InternshipEndDate
{
    /**
     * Calcula os feriados brasileiros para um ano específico.
     *
     * Inclui feriados nacionais fixos e móveis (baseados na Páscoa).
     *
     * @param  int  $year  O ano para o qual calcular os feriados.
     * @return array Array de objetos Carbon representando os feriados.
     */
    public static function getBrazilianHolidays(int $year): array
    {
        $holidays = [];

        // Feriados nacionais fixos.
        $holidays[] = Carbon::create($year, 1, 1);   // Ano Novo
        $holidays[] = Carbon::create($year, 4, 21);  // Tiradentes
        $holidays[] = Carbon::create($year, 5, 1);   // Dia do Trabalhador
        $holidays[] = Carbon::create($year, 9, 7);   // Independência do Brasil
        $holidays[] = Carbon::create($year, 10, 12); // Nossa Senhora Aparecida
        $holidays[] = Carbon::create($year, 11, 2);  // Finados
        $holidays[] = Carbon::create($year, 11, 15); // Proclamação da República
        $holidays[] = Carbon::create($year, 11, 20); // Consciência Negra
        $holidays[] = Carbon::create($year, 12, 25); // Natal

        // Feriados móveis (baseados no cálculo da Páscoa).
        $easter = self::calculateEaster($year);
        $holidays[] = $easter->copy()->subDays(47); // Carnaval (segunda-feira)
        $holidays[] = $easter->copy()->subDays(46); // Carnaval (terça-feira)
        $holidays[] = $easter->copy()->subDays(2);  // Sexta-feira Santa
        $holidays[] = $easter;                      // Páscoa
        $holidays[] = $easter->copy()->addDays(60); // Corpus Christi

        // Normaliza todas as datas para o início do dia (00:00:00).
        return collect($holidays)->map(function ($holiday) {
            return $holiday->startOfDay();
        })->toArray();
    }

    /**
     * Calcula a data da Páscoa para um ano específico usando o algoritmo de Meeus/Jones/Butcher.
     *
     * @param  int  $year  O ano para o qual calcular a Páscoa.
     * @return \Carbon\Carbon A data da Páscoa.
     */
    public static function calculateEaster(int $year): Carbon
    {
        // Algoritmo de Meeus/Jones/Butcher para cálculo da data da Páscoa.
        $a = $year % 19;
        $b = intval($year / 100);
        $c = $year % 100;
        $d = intval($b / 4);
        $e = $b % 4;
        $f = intval(($b + 8) / 25);
        $g = intval(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intval($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intval(($a + 11 * $h + 22 * $l) / 451);
        $month = intval(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }

    /**
     * Verifica se uma data é feriado brasileiro.
     *
     * @param  \Carbon\Carbon  $date  A data a ser verificada.
     * @return bool True se for feriado, false caso contrário.
     */
    public static function isHoliday(Carbon $date): bool
    {
        $holidays = self::getBrazilianHolidays($date->year);

        foreach ($holidays as $holiday) {
            if ($date->isSameDay($holiday)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcula a data de término do estágio baseada na data de início, horas semanais e horas totais necessárias.
     *
     * O cálculo considera:
     * - Feriados brasileiros (não conta horas nesses dias)
     * - Carga horária semanal máxima de 30 horas
     * - Distribuição das horas por dia da semana
     * - Adiciona uma semana extra como margem de segurança
     *
     * @param  \Carbon\Carbon  $startDate  Data de início do estágio.
     * @param  array  $weeklyHours  Array com as horas por dia da semana [domingo, segunda, ..., sábado].
     * @param  int  $requiredHours  Total de horas necessárias para completar o estágio.
     * @return \Carbon\Carbon A data calculada de término do estágio.
     *
     * @throws \InvalidArgumentException Se a carga horária for inválida.
     */
    public static function calculateInternshipEndDate(Carbon $startDate, array $weeklyHours, int $requiredHours): Carbon
    {
        $totalWeeklyHours = array_sum($weeklyHours);

        // Valida a carga horária semanal.
        if ($totalWeeklyHours > 30) {
            throw new \InvalidArgumentException('A carga horária semanal não pode exceder 30 horas.');
        }

        if ($totalWeeklyHours <= 0) {
            throw new \InvalidArgumentException('A carga horária semanal deve ser maior que zero.');
        }

        $currentDate = $startDate->copy();
        $accumulatedHours = 0;

        // Mapeia os dias da semana (0=domingo, 6=sábado) para as horas correspondentes.
        $dayToHoursMap = [
            0 => $weeklyHours[0] ?? 0, // Domingo
            1 => $weeklyHours[1] ?? 0, // Segunda
            2 => $weeklyHours[2] ?? 0, // Terça
            3 => $weeklyHours[3] ?? 0, // Quarta
            4 => $weeklyHours[4] ?? 0, // Quinta
            5 => $weeklyHours[5] ?? 0, // Sexta
            6 => $weeklyHours[6] ?? 0, // Sábado
        ];

        // Itera dia a dia até acumular as horas necessárias.
        while ($accumulatedHours < $requiredHours) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $hoursForDay = $dayToHoursMap[$dayOfWeek];

            // Só adiciona horas se não for feriado e houver horas definidas para esse dia.
            // Permite estágio em fins de semana se houver horas configuradas.
            if (! self::isHoliday($currentDate) && $hoursForDay > 0) {
                $accumulatedHours += $hoursForDay;
            }

            // Se ainda não completou as horas necessárias, avança para o próximo dia.
            if ($accumulatedHours < $requiredHours) {
                $currentDate->addDay();
            }
        }

        // Adiciona uma semana extra como margem de segurança.
        $endDate = $currentDate->copy()->addWeek();

        // Garante que a data de término caia em um dia útil com carga horária definida.
        while (true) {
            $dayOfWeek = $endDate->dayOfWeek;
            $hoursForDay = $dayToHoursMap[$dayOfWeek];

            // Se o dia tem carga horária configurada e não é feriado, pode terminar aqui.
            if ($hoursForDay > 0 && ! self::isHoliday($endDate)) {
                break;
            }

            // Senão, avança para o próximo dia.
            $endDate->addDay();
        }

        return $endDate;
    }
}
