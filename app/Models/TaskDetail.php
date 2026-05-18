<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Model;

class TaskDetail extends Model
{
    use Expandable;

    protected $connection = 'tc_doc';

    // Sem updated_at, sem softDeletes
    const UPDATED_AT = null;

    protected $fillable = [
        'task_id',
        'task_status_id',
        'person_id',
        'prompt',
        'resumo',
        'started_at',
        'finished_at',
        'duration_minutes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    const EXPANDABLE = ['task', 'status', 'person'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
