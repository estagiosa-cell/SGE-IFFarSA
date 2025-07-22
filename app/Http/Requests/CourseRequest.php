<?php

namespace App\Http\Requests;

use App\Enums\CourseLevel;
use App\Enums\CourseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
            ],
            'coordinator_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $levelValue = $data['level'] ?? null;
            $typeValue = $data['type'] ?? null;

            if ($levelValue && $typeValue) {
                $level = CourseLevel::tryFrom($levelValue);
                $type = CourseType::tryFrom($typeValue);

                if ($level && $type) {
                    if (!CourseType::isValidForLevel($type, $level)) {
                        $validator->errors()->add('type', "O tipo '{$type->label()}' não é válido para o nível '{$level->label()}'.");
                    }
                }
            }
        });
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
