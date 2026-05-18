<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'tc_doc';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'project_id',
        'name',
        'token',
        'abilities',
        'expires_at',
    ];
}
