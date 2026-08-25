<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateOrientationCertificateRequest;
use App\Models\Internship;
use App\Services\OrientationCertificateService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrientationCertificateController extends Controller
{
    public function __construct(private readonly OrientationCertificateService $certificateService) {}

    public function __invoke(
        GenerateOrientationCertificateRequest $request,
        Internship $internship
    ): BinaryFileResponse|RedirectResponse {
        $document = null;

        try {
            $document = $this->certificateService->generate($internship, $request->user());

            activity('internships')
                ->performedOn($internship)
                ->causedBy($request->user())
                ->withProperties([
                    'document_type' => 'atestado-orientacao',
                    'filename' => $document['filename'],
                ])
                ->log('Atestado de orientação gerado');

            return response()
                ->download($document['path'], $document['filename'], [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ])
                ->deleteFileAfterSend(true);
        } catch (\RuntimeException $exception) {
            $this->deleteGeneratedFile($document);

            return redirect()
                ->route('internship-view.show', $internship)
                ->with('message', $exception->getMessage())
                ->with('messageType', 'danger');
        } catch (\Throwable $exception) {
            $this->deleteGeneratedFile($document);
            report($exception);

            return redirect()
                ->route('internship-view.show', $internship)
                ->with('message', 'Não foi possível gerar o atestado de orientação. Tente novamente.')
                ->with('messageType', 'danger');
        }
    }

    /**
     * @param  array{path: string}|null  $document
     */
    private function deleteGeneratedFile(?array $document): void
    {
        if ($document !== null && is_file($document['path'])) {
            unlink($document['path']);
        }
    }
}
