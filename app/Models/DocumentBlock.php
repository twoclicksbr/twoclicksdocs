<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentBlock extends Model
{
    use SoftDeletes, Expandable;

    protected $connection = 'tc_doc';
    protected $table = 'document_blocks';

    protected $fillable = [
        'document_id',
        'parent_id',
        'content',
        'order',
        'status',
    ];

    const EXPANDABLE = ['document', 'parent', 'children'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function parent()
    {
        return $this->belongsTo(DocumentBlock::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentBlock::class, 'parent_id');
    }
}
