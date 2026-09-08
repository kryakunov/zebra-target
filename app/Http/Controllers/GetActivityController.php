<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class GetActivityController extends ExecController
{

    public function __construct()
    {
        $this->time = time();
        $this->type = 8;
        $this->execScriptName = 'execGetActivityGroups';
    }


    public function getactivity()
    {

        $work = $this->getWork();

		if ($work) {
			return view('getactivity', ['work' => $work]);
		}


        return view('getactivity');
    }

    public function getactivityStore(Request $request)
    {

        // Проверяем, откуда пришли данные. Из формы, либо из задачи
            // Если из формы, валидируем и сохраняем в файл
            // Если из задачи, копируем родителя

        $this->parentId = $request->get('parentId');

        if($request['nPosts'] > 230)
            return redirect()->back()->with('error', 'Максимальное кол-во постов за раз - 230')->withInput();


        // Если данные пришли из формы (\r\n)
        if (!($this->parentId))
        {
            // Валидируем
            $this->validate($request, ['groups' => 'required',]);

            // Принимает группы
            $groups = explode("\r\n", $request['groups']);
            $groups = array_unique($groups);

            if (count($groups) > 100000) {
                return redirect()->back()->with('error', 'Максимально 100 000 сообществ')->withInput();
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

/*
        // Собираем только ID групп
        $groups = array_chunk($groups, 24);
        $data = [];
        foreach($groups as $group)
        {
            $count = count($group);
            $ids = implode(',', $group);

            // Получаем ID групп
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getGroupsId?ids='.$ids.'&count='.$count.'&v=5.131&access_token='.session('token')), true);

            if(!$result or !isset($result['response'])) continue;

            $data = array_merge($data, $result['response']);
        }
*/


       //$this->streams = round($this->membersCount / 100000);
       // if ($this->streams == 0)


        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        $this->streams = 1;
        $this->membersCount = count($groups);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор активности в группах';

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
