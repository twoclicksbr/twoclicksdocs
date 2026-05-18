<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'hash'       => $this->hash,
            'project_id' => $this->project_id,
            'payload'    => $this->payload,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,

            'project' => $this->whenLoaded('project'),
        ];
    }
}
