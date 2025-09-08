<?php

namespace App\Http\Execute;


class More
{

    public $var = 'Andrey';

    public function sayHello(){
        return $this->var . ' hello!';
    }
}