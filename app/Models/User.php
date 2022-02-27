<?php

namespace App\Models;

use Syscodes\Components\Database\Erostrine\Model;

class User extends Model
{
    protected $fillable = ['name', 'email'];
}