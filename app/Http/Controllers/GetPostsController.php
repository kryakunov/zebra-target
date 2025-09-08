<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class GetPostsController extends ExecController
{

    public function __construct()
    {
        $this->time = time();
        $this->type = 14;
        $this->execScriptName = 'execGetPosts';
    } 


    public function show(){

        return view('getposts');
    }

    public function handler(Request $request)
    {
        $this->parentId = $request->get('parentId');

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Поиск постов';

        // Если собираем только ID авторов постов, то меняет тип задачи (чтобы на выходе были не посты, а люди)
        if(isset($request['when_posts']))
            if ($request['when_posts'] == 3) {
                $this->type = 12;
                ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Авторы постов';
            }

        $this->validate($request, ['q' => 'required']);

        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        $this->streams = 1;

        $this->setRequest($request->all());
        
        $data = $request['q'];
        
        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');

    }
}
