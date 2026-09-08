<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class GetActivityPostsController extends ExecController
{


    public function __construct()
    {
        $this->time = time();
        $this->type = 15;
        $this->execScriptName = 'execGetActivityPosts';
    } 

	public function show() 
    {
        if (!$this->isVkAuthenticated()) {
            return view('getactivityposts');
        }

        $work = $this->getWork();

		if ($work) {
			return view('getactivityposts', ['work' => $work]);
		}

		return view('getactivityposts');
	}

	public function handler(Request $request) 
    {
		
        $this->parentId = $request->get('parentId');

        if (!(isset($request['likes'])) && !(isset($request['comments'])) && !(isset($request['thread_comments']))) {
            return redirect()->back()->with('error', 'Какие активности собираем?')->withInput();
        }

         // Если данные пришли из формы (\r\n)
         if (!($this->parentId)) 
         {
             // Валидируем
             $this->validate($request, ['posts' => 'required',]);
 
             // Принимает группы
             $posts = explode("\r\n", $request['posts']);
             $posts = array_unique($posts);
 
             if (count($posts) > 5000000) {
                 return redirect()->back()->with('error', 'Максимально 5 000 000 пользователей')->withInput();
             }
 
             $posts = Functions::clearGroupName($posts);
 
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
             
             $posts = file_get_contents($file);
             $posts = explode($res, $posts);
 
             // Если это моя задача и у меня бесплатный доступ, то обрезать результат
             if ($work->vk_id == session('id'))
             if (!Functions::isFullAccess())
             { 
                 $posts = $this->readLine($file, 15);
             }
 
         }
 
         $this->setRequest($request->all());

        $posts = array_unique($posts);

        if(count($posts) > 100000){
            return redirect()->back()->with('error', 'Лимит этого парсера - до 100 тысяч постов.')->withInput();
        }

        $this->parentId = $request->get('parentId');
        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        
        //$this->streams = round($this->membersCount / 100000);
        //if ($this->streams == 0) 
        $this->streams = 1;
        $this->membersCount = count($posts);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор активности в постах';

        $posts = implode("\n", $posts);

        // Сохраняем данные в файл
        file_put_contents($this->path, $posts);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
    
    }
}

