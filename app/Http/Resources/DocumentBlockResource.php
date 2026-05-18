<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'document_id' => $this->document_id,
            'parent_id'   => $this->parent_id,
            'content'     => $this->content,
            'order'       => $this->order,
            'status'      => $this->status,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,

            'document' => $this->whenLoaded('document'),
            'parent'   => $this->whenLoaded('parent'),
            'children' => $this->whenLoaded('children'),
        ];
    }
}
