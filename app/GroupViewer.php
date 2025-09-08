<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupViewer extends Model
{
    protected $guarded = [];
    protected $table = 'group_viewer';
    public $timestamps = false;
}