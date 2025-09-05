<?php

namespace App\Utils;

use Carbon\Carbon;

class InternshipEndDate
{
    /**
     * Calcula os feriados brasileiros para um ano específico
     */
    public static function getBrazilianHolidays(int $year): array
    {
        $holidays = [];

        // Feriados fixos
        $holidays[] = Carbon::create($year, 1, 1);   // Ano Novo
        $holidays[] = Carbon::create($year, 4, 21);  // Tiradentes
        $holidays[] = Carbon::create($year, 5, 1);   // Dia do Trabalhador
        $holidays[] = Carbon::create($year, 9, 7);   // Independência do Brasil
        $holidays[] = Carbon::create($year, 10, 12); // Nossa Senhora Aparecida
        $holidays[] = Carbon::create($year, 11, 2);  // Finados
        $holidays[] = Carbon::create($year, 11, 15); // Proclamação da República
        $holidays[] = Carbon::create($year, 11, 20); // Consciência Negra
        $holidays[] = Carbon::create($year, 12, 25); // Natal

        // Feriados móveis (baseados na Páscoa)
        $easter = self::calculateEaster($year);
        $holidays[] = $easter->copy()->subDays(47); // Carnaval (segunda-feira)
        $holidays[] = $easter->copy()->subDays(46); // Carnaval (terça-feira)
        $holidays[] = $easter->copy()->subDays(2);  // Sexta-feira Santa
        $holidays[] = $easter;                      // Páscoa
        $holidays[] = $easter->copy()->addDays(60); // Corpus Christi

        return collect($holidays)->map(function ($holiday) {
            return $holiday->startOfDay();
        })->toArray();
    }

    /**
     * Calcula a data da Páscoa para um ano específico
     */
    public static function calculateEaster(int $year): Carbon
    {
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
     * Verifica se uma data é feriado
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
     * Calcula a data de fim do estágio baseado na data de início, horas semanais e horas necessárias
     */
    public static function calculateInternshipEndDate(Carbon $startDate, array $weeklyHours, int $requiredHours): Carbon
    {
        $totalWeeklyHours = array_sum($weeklyHours);

        if ($totalWeeklyHours > 30) {
            throw new \InvalidArgumentException('A carga horária semanal não pode exceder 30 horas.');
        }

        if ($totalWeeklyHours <= 0) {
            throw new \InvalidArgumentException('A carga horária semanal deve ser maior que zero.');
        }

        $currentDate = $startDate->copy();
        $accumulatedHours = 0;

        // Mapeia os dias da semana para os índices do array de horas
        $dayToHoursMap = [
            0 => $weeklyHours[0] ?? 0, // Domingo
            1 => $weeklyHours[1] ?? 0, // Segunda
            2 => $weeklyHours[2] ?? 0, // Terça
            3 => $weeklyHours[3] ?? 0, // Quarta
            4 => $weeklyHours[4] ?? 0, // Quinta
            5 => $weeklyHours[5] ?? 0, // Sexta
            6 => $weeklyHours[6] ?? 0, // Sábado
        ];

        while ($accumulatedHours < $requiredHours) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $hoursForDay = $dayToHoursMap[$dayOfWeek];

            // Só adiciona horas se não for feriado e tiver horas definidas para esse dia
            // Permite estágio em fins de semana se houver horas definidas no formulário
            if (!self::isHoliday($currentDate) && $hoursForDay > 0) {
                $accumulatedHours += $hoursForDay;
            }

            // Se ainda não completou as horas, avança para o próximo dia
            if ($accumulatedHours < $requiredHours) {
                $currentDate->addDay();
            }
        }

        // Adiciona uma semana extra como margem de segurança
        $endDate = $currentDate->copy()->addWeek();

        // Garante que termine em um dia com carga horária > 0 e que não seja feriado
        while (true) {
            $dayOfWeek = $endDate->dayOfWeek;
            $hoursForDay = $dayToHoursMap[$dayOfWeek];

            // Se o dia tem carga horária e não é feriado, pode terminar aqui
            if ($hoursForDay > 0 && !self::isHoliday($endDate)) {
                break;
            }

            // Senão, avança para o próximo dia
            $endDate->addDay();
        }

        return $endDate;
    }
}
