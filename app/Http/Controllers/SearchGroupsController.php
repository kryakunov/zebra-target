<?php

namespace App\Http\Controllers;

use App\Functions;
use Illuminate\Http\Request;

class SearchGroupsController extends ExecController
{

    public function __construct()
    {
        $this->time = time();
        $this->type = 3;
        $this->execScriptName = 'execSearchGroups';
    }

    public function show() {

        return view('searchgroups');
    }


    public function store(Request $request)
    {

        $this->validate($request, [
            'q' => 'required',
        ]);

        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        $this->setRequest($request->all());

        $this->streams = 1;
        $this->membersCount = count(explode("\r\n", $request->get('q')));

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Поиск сообществ';

        $data = $request['q'];

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
/*

        $request_params = array(
            'v'            => '5.126',
            'count'        => 1000,
            //'city_id'	   => $request->input('cities')
            'access_token' => session('token')
        );

        if (isset($_POST['market'])) $request_params['market'] = 1;
        if (isset($_POST['sort'])) $request_params['sort'] = $_POST['sort'];
        if ($_POST['type'] !== 'all' and $_POST['type'] !== 'fevent') $request_params['type'] = $_POST['type'];
        elseif ($_POST['type'] == 'fevent') {
            $request_params['type']   = 'event';
            $request_params['future'] = 1;
        }


        $q = explode("\n", $_POST['q']);

        // Удаляем пробелы и пустые элементы из массива
        $q = array_diff($q, array('',' '));
        $count = count($q);
        for($i = 0; $i < $count; $i++) {
            $q[$i] = trim($q[$i]);
            $q = array_diff($q, array('',' '));
        }

        if (!empty($_POST['stop_words'])) {
            $stop_words = explode("\n", $_POST['stop_words']);
            $stop_words = array_diff($stop_words, array('',' '));
            /*$count = count($stop_words);
            for($i = 0; $i < $count; $i++) {
                //$stop_words[$i] = trim($stop_words[$i]);

            }
        }

        $data = array();
        $temp = array();
        $itog = [];

        // В цикле делаем запрос по каждому ключевику
        foreach($q as $value)
        {
            $request_params['q'] = $value;
            $get_params = http_build_query($request_params);
            $result     = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?' . $get_params), true);
            $result     = $result['response']['items'];

            // Точное вхождение фразы
            if (isset($_POST['strong'])) {
                foreach($result as $name) {
                    if (mb_strtolower($name['name']) == mb_strtolower($value))
                        $temp[] = $name;
                }
            $result = $temp;
            $temp = array();
            }

            // Стоп-слова
            if (isset($stop_words)) {

                $count = count($result);
                for($i = 0; $i < $count; $i++) {
                    foreach($stop_words as $stop) {
                        $stop = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($stop));
                        $pos = strpos(mb_strtolower($result[$i]['name']), mb_strtolower($stop));
                        if ($pos !== false) {
                            //$temp[] = $result[$i];
                            unset($result[$i]);
                            break;
                        }

                    }
                }

            // Перебираем массив
            $temp = array();
            foreach($result as $value) $temp[] = $value;
            $result = $temp;
            $temp = array();

            }


            // Закрытые группы
            if ($_POST['closed'] == 1) {
                $count = count($result);
                for($i = 0; $i < $count; $i++) {
                    if ($result[$i]['is_closed'] == 1) unset($result[$i]);
                }
            } elseif ($_POST['closed'] == 2) {
                $count = count($result);
                for($i = 0; $i < $count; $i++) {
                    if ($result[$i]['is_closed'] == 0) unset($result[$i]);
                }
            }

            //$data = array_merge($data, $result);
            //$data = [];
            $result = collect($result);
            $data = $result->map(function ($name) {
                return $name['id'];
            })->toArray();

            $itog = array_merge($data, $itog);

        }

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 15 значений из ' . count($itog));
            $itog = array_slice($itog, 0, 15);
        }


        return view('searchgroups', ['data' => $itog, 'request' => $request]);
*/

    }
}
