<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\YooMoneyController as s;
use App\Http\Controllers\Api\NewController;
Use App\User;
use App\Http\Requests\TestRequest;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/dd', function (TestRequest $request) {
    $a = $request->validated();

    dd($a);
});

Route::get('/test', function (){
    
    \DB::enableQueryLog();

    $users = User::where('id', '>', 5000)
        ->orderBy("id",'DESC')
        ->limit(5)
        ->get();

    dd(\DB::getQueryLog());

    dd($users);
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('/yoomoney', [App\Http\Controllers\Api\YooMoneyController::class, 'index']);
Route::get('/yoomoneysuccess', 'YooMoneyController@success');