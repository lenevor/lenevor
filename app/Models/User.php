<?php

namespace App\Models;

use Syscodes\Components\Database\Erostrine\Model;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     * 
     * @var string[] $fillable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     * 
     * @var array $hidden
     */
    protected $hidden = [
        'password',
    ];
}