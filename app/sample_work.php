<?php

namespace App;

use App\User;
use App\sample_work;
use App\sample;
use Illuminate\Database\Eloquent\Model;

class sample_work extends Model
{
    protected $guarded = [];
    public $timestamps = false;


    public function WorkType()
    {
        return $this->hasOne(WorkType::class, 'id', 'type_id');
    }


}
