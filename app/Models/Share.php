<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Share extends Model
{
    use Expandable;

    protected $connection = 'tc_doc';

    protected $fillable = [
        'hash',
        'project_id',
        'payload',
        'created_by_token_id',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'datetime',
    ];

    const EXPANDABLE = ['project'];

    protected static function booted(): void
    {
        static::creating(function (Share $share) {
            if (empty($share->hash)) {
                do {
                    $candidate = Str::random(10);
                } while (static::where('hash', $candidate)->exists());
                $share->hash = $candidate;
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
