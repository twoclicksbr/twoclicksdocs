<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTarefaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = (int) $this->input('project_id');

        return [
            'project_id'         => 'nullable|integer',
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'task_status_id'     => ['required', 'integer',
                Rule::exists('tc_doc.task_statuses', 'id')
                    ->where('project_id', $projectId)->whereNull('deleted_at')],
            'task_fase_id'       => ['required', 'integer',
                Rule::exists('tc_doc.task_fases', 'id')
                    ->where('project_id', $projectId)->whereNull('deleted_at')],
            'task_modulo_id'     => ['required', 'integer',
                Rule::exists('tc_doc.task_modulos', 'id')
                    ->where('project_id', $projectId)->whereNull('deleted_at')],
            'task_tipo_id'       => ['required', 'integer',
                Rule::exists('tc_doc.task_tipos', 'id')
                    ->where('project_id', $projectId)->whereNull('deleted_at')],
            'task_prioridade_id' => ['required', 'integer',
                Rule::exists('tc_doc.task_prioridades', 'id')
                    ->where('project_id', $projectId)->whereNull('deleted_at')],
            'order'              => 'nullable|integer',
            'status'             => 'nullable|boolean',
            'priority_flag'      => 'nullable|boolean',
        ];
    }
}
