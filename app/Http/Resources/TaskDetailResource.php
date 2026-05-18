<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'task_id'          => $this->task_id,
            'task_status_id'   => $this->task_status_id,
            'person_id'        => $this->person_id,
            'prompt'           => $this->prompt,
            'resumo'           => $this->resumo,
            'started_at'       => $this->started_at,
            'finished_at'      => $this->finished_at,
            'duration_minutes' => $this->duration_minutes,
            'created_at'       => $this->created_at,

            'task'   => $this->whenLoaded('task'),
            'status' => $this->whenLoaded('status'),
            'person' => $this->whenLoaded('person'),
        ];
    }
}
