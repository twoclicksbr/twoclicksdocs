<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 'status' is both a boolean column and a relation name on Task.
        // Eloquent's __get returns the column attribute before checking relations,
        // so whenLoaded('status') would return the boolean. We access the relation
        // directly via getRelation() to bypass the attribute/relation conflict.
        $taskStatus = $this->resource->relationLoaded('status')
            ? $this->resource->getRelation('status')
            : null;

        return [
            'id'                 => $this->id,
            'project_id'         => $this->project_id,
            'title'              => $this->title,
            'description'        => $this->description,
            'task_status_id'     => $this->task_status_id,
            'task_fase_id'       => $this->task_fase_id,
            'task_modulo_id'     => $this->task_modulo_id,
            'task_tipo_id'       => $this->task_tipo_id,
            'task_prioridade_id' => $this->task_prioridade_id,
            'order'              => $this->order,
            'status'             => $this->status,
            'priority_flag'      => $this->priority_flag,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'deleted_at'         => $this->deleted_at,

            'project'    => $this->whenLoaded('project'),
            'task_status' => $taskStatus,
            'fase'        => $this->whenLoaded('fase'),
            'modulo'      => $this->whenLoaded('modulo'),
            'tipo'        => $this->whenLoaded('tipo'),
            'prioridade'  => $this->whenLoaded('prioridade'),
            'details'     => $this->whenLoaded('details'),
        ];
    }
}
