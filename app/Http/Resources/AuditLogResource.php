<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'person_id'   => $this->person_id,
            'project_id'  => $this->project_id,
            'token_name'  => $this->token_name,
            'action'      => $this->action,
            'table_name'  => $this->table_name,
            'record_id'   => $this->record_id,
            'old_values'  => $this->old_values,
            'new_values'  => $this->new_values,
            'ip_address'  => $this->ip_address,
            'created_at'  => $this->created_at,

            'person'  => $this->whenLoaded('person'),
            'project' => $this->whenLoaded('project'),
        ];
    }
}
