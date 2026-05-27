<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class TaskStatus extends Model
{
    use SoftDeletes;

    protected $connection = 'tc_doc';
    protected $table = 'task_statuses';

    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'color',
        'model',
        'runtime_location',
        'webhook_url',
        'code_prompt',
        'executor_type',
        'show_on_task',
        'auto_execute_default',
        'order',
        'status',
    ];

    protected $casts = [
        'show_on_task'         => 'boolean',
        'auto_execute_default' => 'boolean',
        'status'               => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeForProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }
}
