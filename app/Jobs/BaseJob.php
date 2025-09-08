<?php

namespace App\Jobs;

use App\Token;
use App\mywork;
use App\stream;

class BaseJob
{

    public function getToken()
    {
        return Token::getToken();
    }

    public function getWorkById($id)
    {
        $work = mywork::where('id', '=', $id);

        return $work;
    }


    public function setPercent($percent, $id)
    {
        stream::where('id', '=', $id)->update(['percent' => $percent]);
    }

  
}