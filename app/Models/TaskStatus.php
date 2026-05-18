<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskStatus extends Model
{
    use SoftDeletes;

    protected $connection = 'tc_doc';
    protected $table = 'task_statuses';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'order',
        'status',
    ];
}
