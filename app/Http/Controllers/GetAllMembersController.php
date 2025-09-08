<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class GetAllMembersController extends ExecController
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
			return view('getallmembers', ['work' => $work]);
		}

        return view('getallmembers');
    }


    public function store(Request $request)
    {
        $this->parentId = $request->get('parentId');

        if (!($this->parentId)) {
            $this->validate($request, [
                'groups' => 'required',
            ]);
        } else {

            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();
            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();

            $request[$work->WorkType->type_desc] = file_get_contents($file);

            // Если это моя задача и у меня бесплатный доступ, то обрезать результат
            if ($work->vk_id == session('id'))
            if (!Functions::isFullAccess())
            {
                if(isset($request['groups'])){
                    $request['groups'] = explode("\n", $request['groups']);
                    $request['groups'] = array_slice($request['groups'], 0, 5);
                }
            }
        }

        $pos = strpos(substr($request['groups'], 0, 15), "\r\n");

        if ($pos !== false) {
            $groups = explode("\r\n", $request['groups']);
        } else {
            $groups = explode("\n", $request['groups']);
        }

        $groups = array_unique($groups);

        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        $request['groups'] = Functions::clearGroupName($groups);

       if(count($groups) > 100000){
            return redirect()->back()->with('error', 'Лимит этого парсера - до 100 000 сообществ.')->withInput();
        }

        $this->howManyMembers($request['groups']);

       /* if ($this->membersCount > 3000000) {
            return redirect()->route('getallmembers')->with('error', 'Суммарное количество подписчиков групп не должно превышать 3 000 000')->withInput();
        }*/


        $this->streams = round($this->membersCount / 100000);
        if ($this->streams == 0) $this->streams = 1;

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор участников сообществ';

        if (count($this->data) < 1) {

            return redirect()->back()->with('error', 'Сообществ не найдено. Либо неверно указаны ID групп, либо участники сообществ скрыты.');

        };

        $this->data = array_unique($this->data);
        $request['groups']= implode("\r\n", $this->data);

        $this->setRequest($request->all());

        $data = $request['groups'];

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');

    }

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
    }

    public function howManyMembers($groups)
    {
        $i = 0;

        $groups = array_chunk($groups, 24);
        $temp = [];

        foreach($groups as $value)
        {
            $count = count($value);
            $ids = implode(',', $value);

            // Получаем ID групп
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getGroupsId?ids='.$ids.'&count='.$count.'&v=5.131&access_token='.session('token')), true);

            if(!$result or !isset($result['response'])) continue;

            $count = count($result['response']);
            $ids = implode(',', $result['response']);

            // Ищем только те группы где не скрыты участники
            $this->pause();
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.isHiddenMembers?ids='.$ids.'&count='.$count.'&v=5.131&access_token='.session('token')), true);

            if(!$result or !isset($result['response'])) continue;

            if(isset($result['response']))
            {
                $this->data = array_merge($this->data, $result['response']);

                $this->getCountMembers($result['response']);

                if ($this->membersCount > 3000000) {
                    return $this->membersCount;
                }
            }

        }
    }

    public function getCountMembers($data)
    {
        // Делаем запрос к АПИ и узнаем кол-во участников
        $ids = implode(",", $data);
        $this->pause();
        $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?v=5.126&fields=members_count,type&group_ids='.$ids.'&access_token='.session('token')), true);

        // Вычисляем общее кол-во участников групп
        if (isset($result['response'][0]['members_count'])) {
            foreach ($result['response'] as $value)
                $this->membersCount += $value['members_count'];
        }
    }
}
