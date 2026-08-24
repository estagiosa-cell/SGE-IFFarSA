<?php

namespace App\Http\Requests;

use App\Models\Internship;
use Illuminate\Foundation\Http\FormRequest;

class GenerateOrientationCertificateRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to generate the certificate.
     */
    public function authorize(): bool
    {
        $internship = $this->route('internship');

        return $internship instanceof Internship
            && $this->user()?->can('generateOrientationCertificate', $internship);
    }

    /**
     * There are no user-supplied fields for this action.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
