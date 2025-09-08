<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample_queue extends Model
{
    protected $table = 'sample_queue';
    protected $guarded = [];
    public $timestamps = false;
}
