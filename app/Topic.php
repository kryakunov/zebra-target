<?php

namespace App;

use App\support;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $guarded = [];

    public function questions()
    {
        return $this->hasMany(support::class);
    }
}
