<?php

namespace App;

use App\WorkType;
use Illuminate\Database\Eloquent\Model;

class Cloud extends Model
{
    protected $guarded = [];
 
    public function WorkType()
    {
        return $this->hasOne(WorkType::class, 'id', 'type_id');
    }
}
