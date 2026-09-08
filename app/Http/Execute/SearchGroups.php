<?php

namespace App\Http\Execute;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

use App\Http\Execute\Execute;

$id = $argv[1];

require 'Execute.php';

class SearchGroups extends Execute
{
        public $file;
        public $id;
        public $access_token;
        public $countMembers;
        public $data = array();
        public $token;
        public $limit = 0;
        public $i = 0;

        public function pause()
        {
            if (++$this->i >= 2) { $this->i = 0; sleep(1); }
            $this->limit = $this->limit + 1;
        }

        public function parse($request)
        {
            if (file_exists($this->file)) { unlink($this->file); }

            $res = $this->checkRN();
            $q = explode($res, $this->getData());

            $groups = [];
            $request_params = [];
            if (isset($request['market'])) $request_params['market'] = 1;
            if (isset($request['sort'])) $request_params['sort'] = $request['sort'];
            if ($request['type'] !== 'all' and $request['type'] !== 'fevent') $request_params['type'] = $request['type'];
            elseif ($request['type'] == 'fevent') {
                $request_params['type']   = 'event';
                $request_params['future'] = 1;
            }

            // Удаляем пробелы и пустые элементы из массива
            $q = array_diff($q, array('',' '));
            $count = count($q);

            for($i = 0; $i < $count; $i++) {
                $q[$i] = trim($q[$i]);
                $q = array_diff($q, array('',' '));
            }

            if (!empty($request['stop_words'])) {
                $stop_words = explode("\n", $request['stop_words']);
                $stop_words = array_diff($stop_words, array('',' '));
                /*$count = count($stop_words);
                for($i = 0; $i < $count; $i++) {
                    //$stop_words[$i] = trim($stop_words[$i]);

                } */
            }

            $data = array();
            $temp = array();
            $itog = [];
            $groups = [];
            $iter = 0;
            $cities = 10;
            $allCount = count($q) * $cities;

            // В цикле делаем запрос по каждому ключевику
            foreach($q as $qq)
            {
                $this->setState('Собираю группы по ключу ' . $qq);

                $qq = str_replace(' ', '+', $qq);

                $slp = 0;
                for($i = 1; $i <= $cities; $i++)
                {
                    if (++$slp > 2) { $slp = 0; sleep(1); }

                    // Вычисляем процент
                    $percent = round((++$iter / $allCount) * 100);
                    if($percent < 1) $percent = 1; elseif($percent > 99) $percent = 99;
                    $this->setPercent($percent);

                    $params = http_build_query($request_params);
                    if ($i == 1) $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?q='.$qq.'&'.$params.'&v=5.126&count=1000&access_token='.$this->access_token), true);
                        else $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?q='.$qq.'&'.$params.'&city_id='.$i.'&v=5.126&count=1000&access_token='.$this->access_token), true);
                 //  без учета городво// $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?q='.$qq.'&'.$params.'&v=5.126&count=1000&access_token='.$this->access_token), true);

                    // Обрабатываем ошибки
                    if (!isset($result['response']))
                    {
                        $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                        $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                        $error = true;
                        $i = 0;
                        do {
                            if (++$i > 5) {
                                $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                                die;
                            }

                            if (isset($result['error'])) {
                                $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                                $this->setLog('Ошибка: '. $msg);
                            } else $this->setLog('Возникла ошибка, но error_msg не найдено ');

                            $this->pause();
                            $this->access_token = $this->getToken();
                            $this->setLog('Сменил токен');
                            if ($i == 1) $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?q='.$qq.'&'.$params.'&v=5.126&count=1000&access_token='.$this->access_token), true);
                                else $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?q='.$qq.'&'.$params.'&city_id='.$i.'&v=5.126&count=1000&access_token='.$this->access_token), true);
                        // без учета городов  $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.search?q='.$qq.'&'.$params.'&v=5.126&count=1000&access_token='.$this->access_token), true);

                            if (isset($result['response'])) $error = false;

                        } while($error == true);

                        $this->setLog('Ошибка устранена');
                    }

                    if (!$result) continue;

                    $result = $result['response']['items'];

                    // Точное вхождение фразы
                    if (isset($request['strong']))
                    {

                        $key = str_replace('+', ' ', $qq);
                        $temp = [];
                        foreach($result as $name) {

                            if (mb_strtolower($name['name']) == mb_strtolower($key))  {
                                $temp[] = $name;
                                echo '<hr>';
                                var_dump($name);
                            }
                        }

                        $result = $temp;
                        unset($temp);
                    }


                    // Стоп-слова
                    if (isset($stop_words))
                    {

                        $count = count($result);
                        for($ii = 0; $ii < $count; $ii++) {

                            foreach($stop_words as $stop)
                            {
                                $stop = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($stop));
                                if (isset($result[$ii]))
                                    $pos = strpos(mb_strtolower($result[$ii]['name']), mb_strtolower($stop));

                                    if ($pos !== false) {
                                    //$temp[] = $result[$i];
                                    if (isset($result[$ii]))
                                        unset($result[$ii]);

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
                    if ($request['closed'] == 1) {
                        $count = count($result);
                        for($ii = 0; $ii < $count; $ii++) {
                            if ($result[$ii]['is_closed'] == 1) unset($result[$ii]);
                        }
                    } elseif ($request['closed'] == 2) {
                        $count = count($result);
                        for($ii = 0; $ii < $count; $ii++) {
                            if ($result[$ii]['is_closed'] == 0) unset($result[$ii]);
                        }
                    }

                    foreach($result as $value){
                        $groups[] = $value['id'];

                    }
                }
            }

            $groups = array_unique($groups);
            file_put_contents($this->file, implode("\n", $groups), FILE_APPEND);

        }
    }

    $class = new SearchGroups($id);

    $class->setState('Запущено');

    $request = $class->getRequest($id);

    $data = $class->parse($request);

    //$data = $class->extFilter($data, $request);

    $class->changeStatus($class->access_token, 'free');

    //$class->writeFile($data);


    $class->setPercent(100);
    $class->setState('Завершено');
    $class->setStatus();


    die;
