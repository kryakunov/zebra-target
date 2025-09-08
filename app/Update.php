<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    protected $guarded = [];

    public function author()
    {
        return $this->hasOne(User::class, 'vk_id', 'vk_id');
    }
}
