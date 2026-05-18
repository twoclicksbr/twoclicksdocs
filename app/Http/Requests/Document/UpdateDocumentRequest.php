<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId  = (int) $this->attributes->get('project_id');
        $documentId = $this->route('document');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('tc_doc.documents', 'id')->where('project_id', $projectId),
            ],
            'title'  => 'sometimes|required|string|max:255',
            'slug'   => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('tc_doc.documents', 'slug')
                    ->where('project_id', $projectId)
                    ->ignore($documentId),
            ],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
