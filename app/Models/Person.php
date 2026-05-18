<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $connection = 'tc_doc';

    protected $fillable = [
        'first_name',
        'surname',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
