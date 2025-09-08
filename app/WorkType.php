<?php

namespace App;

use App\mywork;
use Illuminate\Database\Eloquent\Model;

class WorkType extends Model
{
    public function mywork()
    {
        return $this->belongsTo(mywork::class);
    }
}
