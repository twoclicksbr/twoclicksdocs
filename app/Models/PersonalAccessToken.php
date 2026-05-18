<?php

namespace App\Models;

use App\Traits\Expandable;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use Expandable;

    protected $connection = 'tc_doc';

    const EXPANDABLE = ['project'];

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'project_id',
        'name',
        'token',
        'abilities',
        'expires_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
