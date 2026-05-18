<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'tc_doc';
    protected $table = 'audit_logs';

    // Sem updated_at, sem softDeletes
    const UPDATED_AT = null;

    protected $fillable = [
        'person_id',
        'project_id',
        'token_name',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
