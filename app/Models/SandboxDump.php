<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SandboxDump extends Model
{
    protected $connection = 'tc_doc';
    protected $table = 'sandbox_dumps';

    protected $fillable = [
        'executed_by_person_id',
        'started_at',
        'finished_at',
        'status',
        'error_message',
        'summary',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function executedBy()
    {
        return $this->belongsTo(Person::class, 'executed_by_person_id');
    }

    public function durationSeconds(): ?int
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }
        return $this->started_at->diffInSeconds($this->finished_at);
    }
}
