<?php

namespace App\Models;

use Syscodes\Components\Database\Erostrine\Model;

class User extends Model
{
    public function roles() 
    {
        return $this->hasOne(Role::class, 'role_id');
    }
}