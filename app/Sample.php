<?php

namespace App;

use App\sample_work;
use App\WorkType;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function getWorks()
    {
        return $this->hasMany(sample_work::class, 'sample_id', 'id');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'vk_id', 'vk_id');
    }

}
