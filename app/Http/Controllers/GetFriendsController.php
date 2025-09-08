<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class GetFriendsController extends ExecController
{

    public function __construct()
    {
        $this->time = time();
        $this->type = 6;
        $this->execScriptName = 'execGetFriends';
    } 


    public function show()
    {
        $work = $this->getWork();

		if ($work) {
			return view('getfriends', ['work' => $work]);
		}
/*
		$request = $this->checkWork();

		if ($request) {
			return view('getfriends', ['request' => $request]);
		}*/

        return view('getfriends');
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
                $this->validate($request, ['users' => 'required',]);
    
                // Принимает группы
                $users = explode("\r\n", $request['users']);
                $users = array_unique($users);
    
                if (count($users) > 1000000) {
                    return redirect()->back()->with('error', 'Максимально 1000 000 пользователей')->withInput();
                }

                if(count($users) < 300) {
                    $users = Functions::clearUserName($users);
                    $users = $this->getUserIds($users);
                }
    
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
                    $users = $this->readLine($file, 15);
                }
    
            }
    
            $this->setRequest($request->all());

        $users = array_unique($users);


        if(count($users) > 1000000){
            return redirect()->back()->with('error', 'Лимит этого парсера - до 1 000 000 пользователей.')->withInput();
        }

        //$this->streams = round($this->membersCount / 100000);
        //if ($this->streams == 0) 
        $this->streams = 1;
        $this->membersCount = count($users);

 
        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;
        
        
        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор друзей и подписчиков';
        
        $data = implode("\n", $users);

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
    
    }

    public function getUserIds($users)
    {
         // Разбиваем массив на части
         $temp = array_chunk($users, 300);

         $i = 0;
         $data = array();
 
         $request_params = array(
             'v'            => '5.126',
             'access_token' => session('token')
         );
 
         // В цикле собираем инф-цию о пользователях
         foreach($temp as $value) 
         {
             $ids = implode(",", $value);
             $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));
         
             $request_params['user_ids'] = $ids;
             $result = Functions::vkapi('users.get', $request_params);
 
             if (is_array($result)) {
                foreach($result as $value) {
                    $data[] = $value['id'];
                }
             }
 
             $i++; if ($i > 3) {sleep(1); $i = 0;} 
         }

         return $data;
    }

}
