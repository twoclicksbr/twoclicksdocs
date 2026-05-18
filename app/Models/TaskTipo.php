<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTipo extends Model
{
    use SoftDeletes;

    protected $connection = 'tc_doc';
    protected $table = 'task_tipos';

    protected $fillable = [
        'name',
        'slug',
        'order',
        'status',
    ];
}
