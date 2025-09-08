<?php

namespace App\Http\Exec;


class Process
{
    private $pid;
    private $command;
    public $op;
    public $time;
    private $log;

    public function __construct($cl=false, $log = null){
        if ($cl != false){
            $this->command = $cl;
            $this->log = $log;
            $this->runCom();
        }
    }


    private function runCom(){
        $command = 'nohup '.$this->command.' > '.$this->log.' & echo $!';
        exec($command, $op, $res);
        $this->pid = (int)$op[0];
        $this->op = $op;
    }

    public function setPid($pid){
        $this->pid = $pid;
    }

    public function getPid(){
        return $this->pid;
    }

    public function status(){
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if (!isset($op[1]))return false;
        else return true;
    }

    public static function checkStatusById($pid){
        $command = 'ps -p '.$pid;
        exec($command,$op);
        if (!isset($op[1]))return false;
        else return true;
    }

    public function getStatusTime(){
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if(isset($op[1])) $this->time = $op[1];
    }

    public function start(){
        if ($this->command != '')$this->runCom();
        else return true;
    }

    public function stop(){
        $command = 'kill '.$this->pid;
        exec($command);
        if ($this->status() == false)return true;
        else return false;
    }

    public static function kill($pid){
        $command = 'kill '.$pid;
        exec($command);

        $command = 'ps -p '.$pid;
        exec($command,$op);
        if (!isset($op[1]))return false;
        else return true;

    }
}