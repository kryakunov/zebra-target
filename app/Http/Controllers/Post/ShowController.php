<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;

class ShowController extends BaseController
{
    public function __invoke(Request $request)
    {
        $this->service->update($request);

        echo 'use';
    }
}