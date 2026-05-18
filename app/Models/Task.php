<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes, Expandable;

    protected $connection = 'tc_doc';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'task_status_id',
        'task_fase_id',
        'task_modulo_id',
        'task_tipo_id',
        'task_prioridade_id',
        'order',
        'status',
    ];

    const EXPANDABLE = ['project', 'status', 'fase', 'modulo', 'tipo', 'prioridade', 'details'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function fase()
    {
        return $this->belongsTo(TaskFase::class, 'task_fase_id');
    }

    public function modulo()
    {
        return $this->belongsTo(TaskModulo::class, 'task_modulo_id');
    }

    public function tipo()
    {
        return $this->belongsTo(TaskTipo::class, 'task_tipo_id');
    }

    public function prioridade()
    {
        return $this->belongsTo(TaskPrioridade::class, 'task_prioridade_id');
    }

    public function details()
    {
        return $this->hasMany(TaskDetail::class);
    }
}
