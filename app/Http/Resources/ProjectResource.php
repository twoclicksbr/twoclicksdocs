<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'order'      => $this->order,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relations expandidas (só aparecem se já foram carregadas via with)
            'documents'  => $this->whenLoaded('documents'),
            'tasks'      => $this->whenLoaded('tasks'),
            'tokens'     => $this->whenLoaded('tokens'),
        ];
    }
}
