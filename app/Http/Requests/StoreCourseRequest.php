<?php

namespace App\Http\Requests;

use App\Enums\CourseLevel;
use App\Enums\CourseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('is-admin');
    }


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', Rule::enum(CourseLevel::class)],
            'type' => [
                'required',
                Rule::enum(CourseType::class),
                function ($value, $fail) {
                    $levelValue = request('level');
                    $level = CourseLevel::tryFrom($levelValue);
                    $type = CourseType::tryFrom($value);

                    if ($level && $type) {
                        if (!CourseType::isValidForLevel($type, $level)) {
                            $fail("O tipo '{$type->label()}' não é válido para o nível '{$level->label()}'.");
                        }
                    }
                }
            ],
            'coordinator_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do curso é obrigatório.',
            'level.required' => 'O nível do curso é obrigatório.',
            'type.required' => 'O tipo do curso é obrigatório.',
            'coordinator_id.exists' => 'O coordenador selecionado não existe.',
        ];
    }
}
