<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = (int) $this->attributes->get('project_id');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('tc_doc.documents', 'id')->where('project_id', $projectId),
            ],
            'title'  => 'required|string|max:255',
            'slug'   => [
                'required',
                'string',
                'max:100',
                Rule::unique('tc_doc.documents', 'slug')->where('project_id', $projectId),
            ],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
