<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payload'               => 'required|array',
            'payload.tab'           => 'nullable|string|in:documentacao,tarefas',
            'payload.resource'      => 'nullable|array',
            'payload.resource.type' => 'nullable|string|in:doc,task',
            'payload.resource.id'   => 'nullable|integer',
            'payload.filters'       => 'nullable|array',
            'expires_at'            => 'nullable|date|after:now',
        ];
    }
}
