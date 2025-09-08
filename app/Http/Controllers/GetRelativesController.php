<?php

namespace App\Http\Controllers;

use App\WorkType;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class GetRelativesController extends Controller
{
    public function show()
    {
        $route = Route::current()->uri();

        $page = WorkType::where('route', '=', $route)->first();

        return view('forms.layout', ['page' => $page]);
    }

    public function handler(Request $request){
        
        dd($request->all());
    }
}
