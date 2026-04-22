<?php

declare(strict_types=1);

namespace Src\Landing\Contact\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email:rfc', 'max:150'],
            'countryCode' => ['nullable', 'string', 'regex:/^\+?\d{1,4}$/'],
            'phone'       => ['required', 'string', 'regex:/^[\d\s\-]{6,20}$/'],
            'procedure'   => ['nullable', 'string', 'max:100'],
            'message'     => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'      => 'El email no es válido.',
            'phone.regex'      => 'El teléfono contiene caracteres no válidos.',
            'countryCode.regex'=> 'El código de país no es válido.',
        ];
    }
}
