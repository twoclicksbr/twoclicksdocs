<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'task_status_id'     => 'required|integer|exists:tc_doc.task_statuses,id',
            'task_fase_id'       => 'required|integer|exists:tc_doc.task_fases,id',
            'task_modulo_id'     => 'required|integer|exists:tc_doc.task_modulos,id',
            'task_tipo_id'       => 'required|integer|exists:tc_doc.task_tipos,id',
            'task_prioridade_id' => 'required|integer|exists:tc_doc.task_prioridades,id',
            'order'              => 'nullable|integer',
            'status'             => 'nullable|boolean',
        ];
    }
}
