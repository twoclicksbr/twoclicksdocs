<?php

namespace App\Http\Requests\DocumentBlock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentId = (int) $this->route('document');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('tc_doc.document_blocks', 'id')->where('document_id', $documentId),
            ],
            'content' => 'required|string',
            'order'   => 'nullable|integer',
            'status'  => 'nullable|boolean',
        ];
    }
}
