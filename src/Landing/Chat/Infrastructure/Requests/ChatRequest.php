<?php

declare(strict_types=1);

namespace Src\Landing\Chat\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chatInput' => ['required', 'string', 'min:1', 'max:1500'],
            'sessionId' => ['required', 'string', 'min:8', 'max:100', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'action'    => ['nullable', 'string', 'in:sendMessage'],
        ];
    }
}
