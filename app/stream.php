<?php

namespace App;

use App\mywork;
use Illuminate\Database\Eloquent\Model;

class stream extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function mywork(){
        $this->belongsTo(mywork::class);
    }
}
