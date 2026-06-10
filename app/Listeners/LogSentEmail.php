<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

class LogSentEmail
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $headers = $message->getHeaders();

        // Extrai os metadados definidos no Mailable (Laravel adiciona o prefixo X-Metadata-)
        $logTypeHeader = $headers->get('X-Metadata-log_type');
        $internshipIdHeader = $headers->get('X-Metadata-internship_id');

        // Só registramos se o Mailable tiver definido o metadata 'log_type'
        if ($logTypeHeader) {
            $logType = $logTypeHeader->getBodyAsString();
            $internshipId = $internshipIdHeader ? $internshipIdHeader->getBodyAsString() : null;

            $recipients = $message->getTo();
            $recipientEmail = count($recipients) > 0 ? $recipients[0]->getAddress() : 'unknown';

            EmailLog::create([
                'internship_id' => $internshipId,
                'recipient' => $recipientEmail,
                'subject_type' => $logType,
                'status' => true,
            ]);
        }
    }
}
