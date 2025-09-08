<?php

namespace App;

use App\User;
use App\mywork;
use App\stream;
use App\WorkType;
use App\Http\Exec\Process;
use Illuminate\Database\Eloquent\Model;

class mywork extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    
    public function stream()
    {
        return $this->hasMany(stream::class, 'mywork_id', 'id');
    }

    public static function getPids($id)
    {
        $work = mywork::where('id', '=', $id)->first();
        
        $pids = [];
        foreach ($work->stream()->get() as $value){
            $pids[] = $value->pid;
        }

        return $pids;
    }


    public static function checkStatusWork($id) 
    {
        $pids = self::getPids($id);

        foreach($pids as $value){
            if (self::checkStatusStream($value)) {
                return true;
            }
        }

        return false;
    }



    public static function checkStatusStream($pid)
    {

        $status = Process::checkStatusById($pid);

        return $status;
    }

    public function WorkType()
    {
        return $this->hasOne(WorkType::class, 'id', 'type_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'vk_id', 'vk_id');
    }
}
