<?php

namespace App\Models;

use App\Traits\Expandable;
use Carbon\Carbon;
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
        // duration_minutes REMOVIDO — calculado automaticamente
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    const EXPANDABLE = ['task', 'status', 'person'];

    protected static function booted(): void
    {
        static::saving(function (TaskDetail $detail) {
            if ($detail->started_at && $detail->finished_at) {
                $start  = Carbon::parse($detail->started_at);
                $finish = Carbon::parse($detail->finished_at);
                $detail->duration_minutes = max(0, $start->diffInMinutes($finish));
            } else {
                $detail->duration_minutes = null;
            }
        });
    }

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
