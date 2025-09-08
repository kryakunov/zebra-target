<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';

class GetMembers extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $findedMembers = 0;
    public $limit = 0;

    public function pause()
    {
        if (++$this->i >= 1) { $this->i = 0; sleep(1); }
        $this->limit = $this->limit + 1;
    }

    public function parse($request)
    {
        // Открываем файл со списком групп которые будем парсить
        $res = $this->checkRN();
        $groups = explode($res, $this->getData());
        $iter = 0;

        $allCount = count($groups);

        foreach($groups as $group)
        {

            // Высчитываем процент
            $percent = round($iter / $allCount * 100);
            $iter++;
            if ($percent < 1) $percent = 1; elseif($percent >= 100) $percent = 99;
            $this->setPercent($percent);

            // Сперва узнаем сколько в группе участников и не скрыты ли они
            $this->pause();
            $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?group_id='.$group.'&offset=0&count=1&v=5.131&access_token='.$this->access_token), true);

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при попытке получить кол-во участников сообщества. Group: '. $group);

                // Если участники сообщества скрыты, продолжаем итерацию
                if (isset($result['error']['error_code']))
                    if ($result['error']['error_code'] == 15 or $result['error']['error_code'] == 125 or $result['error']['error_code'] == 100 or $result['error']['error_code'] == 203) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: ' . $msg . ' Продолжаю скрипт');
                        continue;
                    }

                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 15) {
                        $this->setLog('15 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($result['error'])) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result). "\n\n");

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $this->setLog('Сменил токен');
                    $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?group_id='.$group.'&offset=0&count=1&v=5.131&access_token='.$this->access_token), true);

                    if (isset($result['response'])) $error = false;

                    // Если участники сообщества скрыты, продолжаем итерацию
                    if (isset($result['error']['error_code']))
                    if ($result['error']['error_code'] == 15 or $result['error']['error_code'] == 125 or $result['error']['error_code'] == 100 or $result['error']['error_code'] == 203) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: ' . $msg . ' Продолжаю скрипт');
                        $error = false;
                    }

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $count = $result['response']['count'];

            // Парсим участников
            $users = $this->getMembersGroup($group, $count);

            // Пишем в файл
            $users = implode("\n", $users);
            file_put_contents($this->file, $users . "\n", FILE_APPEND);
        }



        // Если указано в скольких сообществах он должен минимально состоять
        if (isset($request['min']))
            if ($request['min'] > 1) {
                $this->setLog('Подсчитываю в скольких сообществах должен состоять');
                $this->countValues($request['min']);
            }
    }

    public function getMembersGroup($group, $count)
    {
        $offset = 0;
        $data = [];

        do {
            $this->pause();
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getMembersNew?group_id='.$group.'&offset='.$offset.'&v=5.131&access_token='.$this->access_token), true);

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при парсинге. Group: '. $group.'; offset: '.$offset);
                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 15) {
                        $this->setLog('15 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($result['error'])) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result). "\n\n");

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $this->setLog('Сменил токен');
                    $params = http_build_query($request_params);
                    $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getMembersNew?group_id='.$group.'&offset='.$offset.'&v=5.131&access_token='.$this->access_token), true);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $offset += 25000;
            $data = array_merge($data, $result['response']);

        } while($offset <= $count);

        return $data;
    }

}

$class = new GetMembers($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);

die;
