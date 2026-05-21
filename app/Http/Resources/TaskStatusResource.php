<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'project_id'       => $this->project_id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'color'            => $this->color,
            'model'            => $this->model,
            'runtime_location' => $this->runtime_location,
            'webhook_url'      => $this->webhook_url,
            'code_prompt'      => $this->code_prompt,
            'order'            => $this->order,
            'status'           => $this->status,
        ];
    }
}
