<?php

namespace App\Http\Requests\TaskDetail;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_status_id' => 'required|integer|exists:tc_doc.task_statuses,id',
            'person_id'      => 'required|integer|exists:tc_doc.people,id',
            'prompt'         => 'required|string',
            'resumo'         => 'nullable|string',
            'started_at'     => 'nullable|date',
            'finished_at'    => 'nullable|date|after_or_equal:started_at',
        ];
    }
}
