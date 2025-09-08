<?php

namespace App\Http\Controllers;

use App\Cloud;
use App\mywork;
use App\Functions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function checkWork()
    {
        if (isset($_GET['id'])) 
        {
            if (is_numeric($_GET['id'])) $id = $_GET['id'];

            $work = mywork::where('id', '=', $id)->first();
            
            if (!$work) {
                return $request = [];
            }

            // Смотрим, расшаренная ли это задача
            if($work->share !== 1) 
                $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

            if (!$work) return $request = [];

            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            if (!file_exists($file)) {
                //return redirect()->route('myworks')->with('error', 'Данные не найдены');
                echo '<br><br><center><a href="https://zebra-target.ru/myworks"><b><< Вернуться назад</b></a> <br>';
                dd('Данные не найдены!');
            }

            $request[$work->WorkType->type_desc] = file_get_contents($file); 

            // Находим родительскую задачу
            $request['parent_id'] = $id;

            // Если это моя задача и у меня бесплатный доступ, то обрезать результат
            if ($work->vk_id == session('id'))
            if (!Functions::isFullAccess())
            { 
                if(isset($request['groups'])){
                    $request['groups'] = explode("\n", $request['groups']);
                    $request['groups'] = array_slice($request['groups'], 0, 10);
                    $request['groups'] = implode("\n", $request['groups']);
                }

                if(isset($request['users'])){
                    $request['users'] = explode("\n", $request['users']);
                    $request['users'] = array_slice($request['users'], 0, 50);
                    $request['users'] = implode("\n", $request['users']);
                }

                if(isset($request['posts'])){
                    $request['posts'] = explode("\n", $request['posts']);
                    $request['posts'] = array_slice($request['posts'], 0, 15);
                    $request['posts'] = implode("\n", $request['posts']);
                }

                //$data = array_slice($data, 0, 100);
            }

            return $request;

        }
    }

    public function getWork()
    {

        if (isset($_GET['id'])) 
        {
            if (is_numeric($_GET['id'])) $id = $_GET['id'];

            $work = mywork::where('id', '=', $id)->where('vk_id', '=', session('id'))->first();
           
            if (!$work) {

                $work = mywork::where('id', '=', $id)->where('share', '=', 1)->first();
                
            }

            return $work;
        }elseif(isset($_GET['cloud_id'])) {
   
            if (is_numeric($_GET['cloud_id'])) $id = $_GET['cloud_id'];

            $work = Cloud::where('id', '=', $id)->where('vk_id', '=', session('id'))->first();

            if(!$work)
                return null;

            return $work;
            
        }
    }
}
