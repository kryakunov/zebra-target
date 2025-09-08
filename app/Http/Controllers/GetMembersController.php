<?php

namespace App\Http\Controllers;

use App\Http\Exec\Process;
use App\stream;
use App\mywork;
use App\Functions;
use App\Cloud;
use Illuminate\Http\Request;

class GetMembersController extends ExecController
{
    public $data = [];
    public $temp = [];
    public $membersCount = 0;
    public $i = 0;
    
    public function __construct()
    {
        $this->time = time();
        $this->type = 1;
        $this->execScriptName = 'execGetAllMembers';
    } 

    public function show()
    {
        $work = $this->getWork();

		if ($work) {
			return view('getmembers', ['work' => $work]);
		}

        return view('getmembers');
    }


    public function pause()
    {
        if ($this->pause >= 2) {
            sleep(1);
            $this->pause = 0;
        }
    }

    public function handler(Request $request)
    {

            // Проверяем, откуда пришли данные. Из формы, либо из задачи
                // Если из формы, валидируем и сохраняем в файл
                // Если из задачи, копируем родителя 

            $this->parentId = $request->get('parentId');

            // Если данные пришли из формы (\r\n)
            if (!($this->parentId)) 
            {
                // Валидируем
                $this->validate($request, ['groups' => 'required', 'min' => 'nullable|integer']);
    
                // Принимает группы
                $groups = explode("\r\n", $request['groups']);
                $groups = array_unique($groups);
    
                $count = count($groups);
                if ($count > 100000) {
                    return redirect()->back()->with('error', 'Максимально 100 000 сообществ')->withInput();
                } elseif ($count < 8) {
                    $this->execScriptName = 'execGetMembers';
                }

                $groups = Functions::clearGroupName($groups);
    
            } else {
    
               // $work = $this->getParentWork($this->parentId);
            
                $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();
     
                // Для расшаренных задач
                if (!$work) {
                    $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
                    $this->parentId = null;
                }


                $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';
    
                if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();
    
                // Здесь находим какой разделитель в файле \n or \r\n
                $res = $this->checkRNnew($file);
                
                $groups = file_get_contents($file);
                $groups = explode($res, $groups);
    
                // Если это моя задача и у меня бесплатный доступ, то обрезать результат
                if ($work->vk_id == session('id'))
                if (!Functions::isFullAccess())
                { 
                    $groups = $this->readLine($file, 15);
                }
    
            }
    
            $this->setRequest($request->all());

            $this->sourceFile = session('id') . '_' . $this->time . '.txt';
            $this->path = '../storage/app/sourceworks/' . $this->sourceFile;
            
            $this->streams = 1;//count($groups);
    
            ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор учасников сообществ';
    
            // Сохраняем данные в файл
            $groups = implode("\n", $groups);
            file_put_contents($this->path, $groups);
    
            // Создаем очередь
            $work = $this->createWork($name);
    
            // Создаем потоки
            $this->createStream($this->streams);
    
            return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
        
    }



}
