<?php

namespace App\Mail;

use App\Models\Internship;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable enviado ao estagiário quando a sua avaliação é registada
 * e a carga horária do estágio foi integralmente cumprida.
 *
 * Contém informações sobre a nota atribuída, o tipo de estágio e o curso.
 */
class AvaliacaoEstagioConcluida extends Mailable
{
    use SerializesModels;

    /**
     * A instância do estágio avaliado.
     */
    public Internship $internship;

    /**
     * Cria uma nova instância do Mailable.
     *
     * @param  \App\Models\Internship  $internship  O estágio cuja avaliação foi concluída.
     */
    public function __construct(Internship $internship)
    {
        $this->internship = $internship;
    }

    /**
     * Define o envelope (assunto e metadados) do e-mail.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Avaliação de Estágio Concluída — '.config('app.name'),
        );
    }

    /**
     * Define o conteúdo do e-mail usando um template Markdown.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.estagios.avaliacao-concluida',
            with: [
                'studentName' => $this->internship->student_name,
                'evaluationGrade' => number_format((float) $this->internship->evaluation_grade, 2, ',', '.'),
                'courseName' => $this->internship->course?->name ?? 'N/A',
                'internshipType' => $this->internship->internship_type_name ?? 'N/A',
                'internshipWeight' => $this->internship->internship_type_weight ?? 'N/A',
            ],
        );
    }

    /**
     * Define os cabeçalhos do e-mail, incluindo metadados para auditoria/logs.
     */
    public function headers(): \Illuminate\Mail\Mailables\Headers
    {
        return new \Illuminate\Mail\Mailables\Headers(
            metadata: [
                'log_type' => 'Avaliação de Estágio Concluída',
                'internship_id' => $this->internship->id,
            ],
        );
    }
}
