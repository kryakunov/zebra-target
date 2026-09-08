<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetTopicsController extends ExecController
{

    public $sourceFile;
    public $path;
    public $streams;
    public $workId;

    public function __construct()
    {
        $this->time = time();
        $this->type = 2;
        $this->execScriptName = 'execGetTopics';
    }

    public function show()
    {
        if (!$this->isVkAuthenticated()) {
            return view('gettopics');
        }

        $work = $this->getWork();

		if ($work) {
			return view('gettopics', ['work' => $work]);
		}

		return view('gettopics');
	}

    public function handler(Request $request)
    {
/*
        $this->parentId = $request->get('parentId');

        if (!($this->parentId)) {
            $this->validate($request, ['groups' => 'required',]);
        } else {
            // Если задача подгружена из шаблона, ищем его
            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();
            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();

            $request[$work->WorkType->type_desc] = file_get_contents($file);

                // Если это моя задача и у меня бесплатный доступ, то обрезать результат
                if ($work->vk_id == session('id'))
                if (!Functions::isFullAccess())
                {
                    $request['groups'] = array_slice($request['groups'], 0, 15);
                }
        }

        // Записываем в базу настройки парсинга
        $this->setRequest($request->all());

        $pos = strpos(substr($request['groups'], 0, 15), "\r\n");

        if ($pos !== false) {
            $groups = explode("\r\n", $request['groups']);
        } else {
            $groups = explode("\n", $request['groups']);
        }

        $groups = array_unique($groups);

 // Отвалидировать
 // Записать реквест
 // Записать файл


        if (count($groups) > 10000) {
            return redirect()->back()->with('error', 'Максимально 10 000 сообществ')->withInput();
        }

        $groups = Functions::clearGroupName($groups);
        $groups = array_unique($groups);

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

        $this->parentId = $request->get('parentId');
        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

       //$this->streams = round($this->membersCount / 100000);
       // if ($this->streams == 0)

        $this->streams = 1;
        $this->membersCount = count($data);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Сбор активности в группах';

        unset($request['_token']);

        $request['groups'] = implode("\n", $data);

        $data = $request['groups'];

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');

        ////////////////////////////////////////////////////////////////////////
        die; */
            // Вычисляем процент выполнения задачи
          //  $percent = $percent + 10;
           // $this->setPercent($percent);
        $groups = $request->get('groups');
        $groups = explode("\r\n", $groups);
        dd($groups);

            foreach($groups as $group)
            {
                $request_params = array(
                    'v'            => '5.126',
                    'group_id'     => $group,
                    'count'       => 100,
                    'offset'      => 0,
                    'access_token' => $this->access_token
                );

                // Парсим 100 топиков
                do {
                    $result = $this->vkapi('board.getTopics', $request_params);
                    $request_params['offset'] = $request_params['offset'] + 100;

                    // Проходим по кадому топику
                    if ($result)
                    foreach ($result['items'] as $topic)
                    {
                        $request_params2 = array(
                            'v'            => '5.126',
                            'group_id'     => $group,
                            'topic_id'     => $topic['id'],
                            'count'        => 100,
                            'offset'       => 0,
                            'access_token' => $this->access_token
                        );

                        // Парсим 100 сообщений
                        do {
                            $result2 = $this->vkapi('board.getComments', $request_params2);
                            $request_params2['offset'] = $request_params2['offset'] + 100;

                            if ($result2['items'])
                                foreach($result2['items'] as $msg)
                                    if ($msg['from_id'] > 0)
                                        if ($msg['date'] >= $time_min and $msg['date'] <= $time_max)
                                            $topicsMsg[] = $msg['from_id'];

                        $this->pause();

                        } while ($request_params2['offset'] <= $result2['count']);

                        $this->pause();
                    }

                $this->pause();

                } while ($request_params['offset'] <= $result['count']);
            }

            // Вычисляем процент выполнения задачи
            $percent = $percent + 14;
            $this->setPercent($percent);

            $data = [];
            $data = array_merge( $topicsMsg, $topicsLikes);
            $temp = array();

            // Фильтр "от" и "до" активностей
            if (is_numeric($request['ot']) or is_numeric($request['do']))
            {
                $data = array_count_values($data);
                //arsort($data);

                if (is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot'] and $value <= $request['do']) $temp[] = $key;
                    }
                } else if (is_numeric($request['ot']) and !is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot']) $temp[] = $key;
                    }
                } else if (!is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value <= $request['do']) $temp[] = $key;
                    }
                }
                $data = $temp;
                unset($temp);
            } else {
                $data = array_unique($data);
            }

            if (count($data) > 0) {
                $data = implode("\n", $data);
                file_put_contents($this->file, $data. "\n", FILE_APPEND);
            }

            $topicsMsg = [];
            $topicsLikes = [];



    }
}
