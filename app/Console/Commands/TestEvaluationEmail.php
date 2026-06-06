<?php

namespace App\Console\Commands;

use App\Mail\AvaliacaoEstagioConcluida;
use App\Models\Course;
use App\Models\Internship;
use Illuminate\Console\Command;

/**
 * Comando temporário para pré-visualizar o e-mail de avaliação concluída.
 * Renderiza o HTML e guarda num ficheiro para visualização.
 * Apagar após o teste.
 */
class TestEvaluationEmail extends Command
{
    protected $signature = 'test:evaluation-email';

    protected $description = 'Renderiza o e-mail de avaliação concluída em HTML para pré-visualização';

    public function handle(): int
    {
        $internship = new Internship([
            'student_name' => 'João da Silva',
            'student_email' => 'hello@example.com',
            'evaluation_grade' => 8.75,
            'internship_type_name' => 'Estágio Curricular Obrigatório',
        ]);

        $internship->setRelation('course', new Course(['name' => 'Técnico em Agropecuária']));

        $mailable = new AvaliacaoEstagioConcluida($internship);
        $html = $mailable->render();

        $outputPath = storage_path('app/email-preview.html');
        file_put_contents($outputPath, $html);

        $this->info('✅ E-mail renderizado com sucesso!');
        $this->info("Ficheiro guardado em: {$outputPath}");
        $this->info('Abre este ficheiro no browser para ver o preview.');

        return self::SUCCESS;
    }
}
