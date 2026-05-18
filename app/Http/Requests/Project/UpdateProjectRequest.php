<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = $this->route('project');

        return [
            'name'   => 'sometimes|required|string|max:100',
            'slug'   => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('tc_doc.projects', 'slug')->ignore($projectId),
            ],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
