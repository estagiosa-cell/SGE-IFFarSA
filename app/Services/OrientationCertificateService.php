<?php

namespace App\Services;

use App\Models\Internship;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class OrientationCertificateService
{
    private const TEMPLATE = 'templates/atestado_orientacao_estagio_template.docx';

    private const TEMPORARY_DIRECTORY = 'temporary/orientation-certificates';

    /**
     * Generate a filled DOCX certificate from the institutional template.
     *
     * @return array{path: string, relative_path: string, filename: string}
     */
    public function generate(Internship $internship, User $coordinator): array
    {
        $internship->loadMissing(['advisor', 'course']);

        $values = $this->values($internship, $coordinator);
        $disk = Storage::disk('local');
        $disk->makeDirectory(self::TEMPORARY_DIRECTORY);

        $relativePath = self::TEMPORARY_DIRECTORY.'/'.Str::uuid().'.docx';
        $absolutePath = $disk->path($relativePath);

        try {
            $templateProcessor = new TemplateProcessor($this->templatePath());
            $templateProcessor->setValues($values);
            $templateProcessor->saveAs($absolutePath);
        } catch (\Throwable $exception) {
            $disk->delete($relativePath);

            throw $exception;
        }

        return [
            'path' => $absolutePath,
            'relative_path' => $relativePath,
            'filename' => $this->filename($internship->student_name),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function values(Internship $internship, User $coordinator): array
    {
        $values = [
            'ORIENTADOR' => $internship->advisor?->name,
            'ESTUDANTE' => $internship->student_name,
            'CURSO' => $internship->course?->name,
            'LOCAL' => $internship->company_name,
            'INICIO' => $this->formatDate($internship->start_date),
            'FIM' => $this->formatDate($internship->end_date),
            'DATA_EMISSAO' => $this->formatLongDate(now()),
            'COORDENADOR' => $coordinator->name,
        ];

        $missing = collect($values)
            ->filter(fn (?string $value): bool => blank($value))
            ->keys()
            ->implode(', ');

        if ($missing !== '') {
            throw new RuntimeException("Não é possível gerar o atestado. Faltam dados: {$missing}.");
        }

        return $values;
    }

    private function templatePath(): string
    {
        $path = resource_path(self::TEMPLATE);

        if (! is_file($path)) {
            throw new RuntimeException('O template do atestado de orientação não foi encontrado.');
        }

        return $path;
    }

    private function formatDate(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        if (! $value instanceof CarbonInterface) {
            $value = Carbon::parse($value);
        }

        return $value->format('d/m/Y');
    }

    private function formatLongDate(CarbonInterface $date): string
    {
        $months = [
            1 => 'janeiro',
            2 => 'fevereiro',
            3 => 'março',
            4 => 'abril',
            5 => 'maio',
            6 => 'junho',
            7 => 'julho',
            8 => 'agosto',
            9 => 'setembro',
            10 => 'outubro',
            11 => 'novembro',
            12 => 'dezembro',
        ];

        return sprintf('%d de %s de %d', $date->day, $months[$date->month], $date->year);
    }

    private function filename(string $studentName): string
    {
        $studentSlug = Str::of($studentName)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '-')
            ->trim('-')
            ->toString();

        return 'Atestado de Orientacao'.($studentSlug !== '' ? " - {$studentSlug}" : '').'.docx';
    }
}
