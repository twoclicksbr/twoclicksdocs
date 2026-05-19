<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class TaskPrioridade extends Model
{
    use SoftDeletes;

    protected $connection = 'tc_doc';
    protected $table = 'task_prioridades';

    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'color',
        'order',
        'status',
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
