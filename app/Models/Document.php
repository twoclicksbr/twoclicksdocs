<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes, Expandable;

    protected $connection = 'tc_doc';

    protected $fillable = [
        'project_id',
        'parent_id',
        'title',
        'slug',
        'order',
        'status',
    ];

    const EXPANDABLE = ['project', 'parent', 'blocks'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Document::class, 'parent_id');
    }

    public function blocks()
    {
        return $this->hasMany(DocumentBlock::class);
    }
}
