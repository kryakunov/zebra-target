<?php

namespace App\Http\Controllers;

use App\stream;
use App\mywork;
use App\Functions;
use App\Http\Exec\Process;
use Illuminate\Http\Request;

class opinionLiders extends ExecController
{

    public function __construct()
    {
        $this->time = time();
        $this->type = 9;
        $this->execScriptName = 'execOpinionLiders';
    } 


    public function show()
    { 
        $work = $this->getWork();

		if ($work) {
			return view('opinionliders', ['work' => $work]);
		}
        return view('opinionliders');
    }

    public function index(Request $request)
    {

        $this->parentId = $request->get('parentId');

        if (!($this->parentId)) {
            $this->validate($request, [
                'users' => 'required',
            ]);
        } else {
        
            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();

            // Для расшаренных задач
            if (!$work) {
                $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
                $this->parentId = null;
            }


            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();

            $request[$work->WorkType->type_desc] = file_get_contents($file); 
        }

        $pos = strpos(substr($request['users'], 0, 15), "\r\n");

        if ($pos !== false) {
            $users = explode("\r\n", $request['users']);
        } else {
            $users = explode("\n", $request['users']);
        }
        
        $users = array_unique($users);

        // Если это моя задача и у меня бесплатный доступ, то обрезать результат
        if ($work->vk_id == session('id'))
        if (!Functions::isFullAccess())
        { 
            $users = array_slice($users, 0, 50);
        }

        if(count($users) > 5000000){
            return redirect()->back()->with('error', 'Лимит этого парсера - до 5 000 000  пользователей.')->withInput();
        }

        $request['users'] = implode("\n", $users);
        
        $this->parentId = $request->get('parentId');
        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

       //$this->streams = round($this->membersCount / 100000);
       // if ($this->streams == 0) 

        $this->streams = 1;
        $this->membersCount = count($users);

        $this->setRequest($request->all());
        
        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Лидеры мнений';

        $data = $request['users'];

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
      
    }

}
