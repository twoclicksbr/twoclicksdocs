<?php

namespace App\Models;

use App\Traits\Expandable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Expandable;

    const EXPANDABLE = ['person'];

    protected $connection = 'tc_doc';

    protected $fillable = [
        'person_id',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
