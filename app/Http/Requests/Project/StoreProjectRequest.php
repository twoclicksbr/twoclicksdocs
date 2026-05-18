<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:100',
            'slug'   => 'required|string|max:100|unique:tc_doc.projects,slug',
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
