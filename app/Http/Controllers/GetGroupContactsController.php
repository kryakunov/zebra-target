<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use App\Cloud;
use Illuminate\Http\Request;

class GetGroupContactsController extends ExecController
{
        
    public function __construct()
    {
        $this->time = time();
        $this->type = 5;
        $this->execScriptName = 'execGetGroupContacts';
    } 


    public function show()
    {
        $work = $this->getWork();

        // Узнаем тип задачи
        $type = $this->getType();

		if ($work) {

			return view('getgroupcontacts', ['work' => $work, 'type' => $type]);
		}

        return view('getgroupcontacts');

    }


    public function store(Request $request)
    {
        $this->parentId = $request->get('parentId');
        $this->workType = $request->get('workType');

        if (!($this->parentId)) {
            $this->validate($request, [
                'groups' => 'required',
            ]);
        } else {
        
            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();
            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            // Для расшаренных задач
            if (!$work) {
                $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
                $this->parentId = null;
            }


            if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();

            $request[$work->WorkType->type_desc] = file_get_contents($file); 

        }


        $this->setRequest($request->all());
        
        $pos = strpos(substr($request['groups'], 0, 15), "\r\n");

        if ($pos !== false) {
            $groups = explode("\r\n", $request['groups']);
        } else {
            $groups = explode("\n", $request['groups']);
        }
        
        $groups = array_unique($groups);
        
        // Если это моя задача и у меня бесплатный доступ, то обрезать результат
        if ($work->vk_id == session('id'))
        if (!Functions::isFullAccess())
        { 
            $groups = array_slice($groups, 0, 15);
        }
        
        $this->membersCount = count($groups);

        if($this->membersCount > 5000000){
            return redirect()->back()->with('error', 'Лимит этого парсера - до 5 000 000  сообществ')->withInput();
        }

        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        if ($this->membersCount < 50)
            $request['groups'] = Functions::clearGroupName($groups);

        // $this->streams = round($this->membersCount / 100000);
       // if ($this->streams == 0) $this->streams = 1;
        $this->streams = 1;

        $this->setRequest($request->all());
        
        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор администраторов групп';

        $data = implode("\n", $groups);

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
    

        die;
        
    }

/*
        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 5 значений из ' . count($data));
            $data = array_slice($data, 0, 5);
        }

        return view('getgroupcontacts', ['items' => $data, 'exceptions' => $exceptions, 'request' => $request->all()]);

        */
    
}
