<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskFase extends Model
{
    use SoftDeletes;

    protected $connection = 'tc_doc';
    protected $table = 'task_fases';

    protected $fillable = [
        'name',
        'slug',
        'order',
        'status',
    ];
}
