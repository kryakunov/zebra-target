<?php

namespace App;

use App\WorkType;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    public $guarded = [];

    public function getWorks()
    {
        return $this->hasMany(sample_work::class, 'sample_id', 'id');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'vk_id', 'vk_id');
    }

    public function WorkType() 
    {
        return $this->hasOne(WorkType::class, 'id', 'type_id');
    }
}
