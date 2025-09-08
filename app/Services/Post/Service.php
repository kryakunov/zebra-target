<?php

namespace App\Services\Post;

class Service
{
    public function show($request)
    {
        var_dump($request->all());
    } 

    public function update($request)
    {
        echo 'update: ' ;
        var_dump( $request->all());
    }
}