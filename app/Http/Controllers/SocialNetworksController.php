<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class SocialNetworksController extends ExecController
{

    public function __construct()
    {
        $this->time = time();
        $this->type = 11;
        $this->execScriptName = 'execSocialNetworks';
    } 
    
    public function show()
    {
        if (!$this->isVkAuthenticated()) {
            return view('socialnetwork', ['request' => []]);
        }

        $work = $this->getWork();

		if ($work) {
			return view('socialnetwork', ['work' => $work]);
		}

        return view('socialnetwork', ['request' => []]);
    }


    public function handler(Request $request)
    {
        $this->parentId = $request->get('parentId');

        // Если данные пришли из формы (\r\n)
        if (!($this->parentId)) 
        {
            // Валидируем
            $this->validate($request, ['users' => 'required',]);

            // Принимает группы
            $users = explode("\r\n", $request['users']);
            $users = array_unique($users);

            if (count($users) > 5000000) {
                return redirect()->back()->with('error', 'Максимально 5 000 000 пользователей')->withInput();
            }

            $users = Functions::clearGroupName($users);

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
            
            $users = file_get_contents($file);
            $users = explode($res, $users);

            // Если это моя задача и у меня бесплатный доступ, то обрезать результат
            if ($work->vk_id == session('id'))
            if (!Functions::isFullAccess())
            { 
                $users = $this->readLine($file, 50);
            }

        }

        $this->setRequest($request->all());

        if(count($users) > 5000000){
            return redirect()->back()->with('error', 'Лимит этого парсера - до 5 000 000  пользователей.')->withInput();
        }

        $rusers = implode("\n", $users);
        
        $this->parentId = $request->get('parentId');
        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

       //$this->streams = round($this->membersCount / 100000);
       // if ($this->streams == 0) 

        $this->streams = 1;
        $this->membersCount = count($users);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор социальных сетей';

        $this->setRequest($request->all());

        $users = implode("\n", $users);

        // Сохраняем данные в файл
        file_put_contents($this->path, $users);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
    
        die;
        


        return view('socialnetwork', ['data' => $this->users, 'request' => $request->all()]);
    }

}
