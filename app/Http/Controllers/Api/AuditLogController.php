<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends ApiController
{
    /**
     * GET /api/doc/audit-logs
     * Lista logs do projeto do token.
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->where('project_id', $this->projectId($request))
            ->expand($request);

        $this->applyFilters($query, $request, [
            'person_id',
            'token_name',
            'action',
            'table_name',
            'record_id',
        ]);

        $this->applyOrder($query, $request, 'created_at,desc');

        return AuditLogResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function show(Request $request, int $log): AuditLogResource
    {
        $model = AuditLog::query()
            ->where('project_id', $this->projectId($request))
            ->expand($request)
            ->findOrFail($log);

        return new AuditLogResource($model);
    }
}
