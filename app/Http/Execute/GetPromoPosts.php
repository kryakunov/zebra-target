<?php

namespace App\Http\Execute;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Execute.php';

class GetPromoPosts extends Execute
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $limit = 0;

    public function pause()
    {
        if (++$this->i >= 4) { $this->i = 0; sleep(1); }
        $this->limit = $this->limit + 1;
    }

    public function parse($request)
    {
        $res = $this->checkRN();
        $groups = explode($res, $this->getData());


        $i = 0;

        (isset($request['time_min'])) ? $timeMin = strtotime($request['time_min']) : $timeMin = strtotime('last month');
        (isset($request['likes_min'])) ? $likesMin = $request['likes_min'] : $likesMin = 0;

        $allCount = count($groups);
        $iter = 0;

        foreach($groups as $group)
        {

            // Высчитываем процент
            $percent = round(($iter / $allCount) * 100);
            if($percent < 1) $percent = 1; elseif($percent > 99) $percent = 99;
            $iter++;
            $this->setPercent($percent);

            if ($group == '') continue;

            // Узнаем ID группы
            if (strlen((int)$group) != strlen($group))
            {
                $this->pause();
                $result = json_decode(file_get_contents("https://api.vk.ru/method/execute.getGroupsId?ids=".$group."&count=1&v=5.131&access_token=".$this->access_token), true);

                // Обрабатываем ошибки
                if (!isset($result['response']))
                {
                    $this->setLog('Возникла ошибка при попытке получить ID группы '.$group);
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
                        } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result));

                        $this->pause();
                        $this->access_token = $this->getToken();
                        $this->setLog('Сменил токен');
                        $result = json_decode(file_get_contents("https://api.vk.ru/method/execute.getGroupsId?ids=".$group."&count=1&v=5.131&access_token=".$this->access_token), true);

                        if (isset($result['response'])) $error = false;

                    } while($error == true);

                    $this->setLog('Ошибка устранена');
                }

                $group = $result['response'][0];
            }

            $date = true;
            $data = [];
            $lastPost = 0;
            do {
                $this->pause();
                // Делаем запрос к процедуре которая смотрит 100 id постов (среди которых многие могут быть уже удаленные)
                $result = json_decode(file_get_contents("https://api.vk.ru/method/execute.getPromoPosts?group=".$group."&likes_min=".$likesMin."&time_min=".$timeMin."&last_post=".$lastPost."&v=5.131&access_token=".$this->access_token), true);

                if(is_array($result['response']))
                if(count($result['response']) < 2 && $result['response'][0] == 0)
                { $date = false; break; }


                // Обрабатываем ошибки
                if (!isset($result['response']))
                {
                    // Если сообщество закрытое
                    if ($result['error']['error_code'] == 13) { $date = false; break; }
                    $this->setLog('Возникла ошибка при попытке получить ID постов группы'.$group);
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
                        } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result));

                        $this->pause();
                        $this->access_token = $this->getToken();
                        $this->setLog('Сменил токен');
                        $result = json_decode(file_get_contents("https://api.vk.ru/method/execute.getPromoPosts?group=".$group."&likes_min=".$likesMin."&time_min=".$timeMin."&last_post=".$lastPost."&v=5.131&access_token=".$this->access_token), true);

                        if (isset($result['response'])) $error = false;

                    } while($error == true);

                    $this->setLog('Ошибка устранена');
                }

                $result = $result['response'];

                if ($result == 'stop')  { $date = false; break; }

                $lastPost = array_pop($result);
                $data = array_merge($data, $result);

            } while ($date == true);

            // Пишем в файл
            if (count($data) > 0) {
                $data = implode("\n", $data);
                file_put_contents($this->file, $data . "\n", FILE_APPEND);
            }
        }
    }
}

$class = new GetPromoPosts($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setState('Завершено');
$class->setStatus();
