<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, Expandable;

    protected $connection = 'tc_doc';

    protected $fillable = [
        'name',
        'slug',
        'order',
        'status',
    ];

    const EXPANDABLE = [];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tokens()
    {
        return $this->hasMany(PersonalAccessToken::class);
    }
}
