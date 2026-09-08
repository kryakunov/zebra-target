<?php

namespace App\Http\Execute;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Execute.php';


class GetFriends extends Execute
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $iter = 0;
    public $checked = 0;
    public $allCount = 0;
    public $limit = 0;
    public $count = 0;

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
        $this->limit = $this->limit + 1;
    }

    public function parse($request)
    {
        $res = $this->checkRN();
        $users = explode($res, $this->getData());
        $this->count = count($users);

        $data = [];

        if (isset($request['friends']))
            $this->checked += 1;

        if (isset($request['followers']))
            $this->checked += 1;

        if (isset($request['friends']))
        {

            $friends = $this->getFriends($users);
         //   $data = array_merge($data, $friends);
        }

        if (isset($request['followers']))
        {
            $followers = $this->getFollowers($users);
        //    $data = array_merge($data, $followers);
        }

        /*
        if (isset($request['uniq']))
        {
            $data = array_unique($data);
        }*/
    }


    public function getFriends($users)
    {
        $i = 0;
        $data  = [];

        $request_params = array(
            'v'            => '5.131',
            'access_token' => $this->access_token,
        );

        $users = array_chunk($users, 24);
        $cc = 0;
        foreach($users as $user)
        {
            $n = count($user);
            $this->allCount += $n;

            $this->setState('Собираю друзей ['.++$cc.'/'.$this->count.']');

            // Высчитываем процент выполнения
            $percent = round(($this->allCount / $this->count) * 100 / $this->checked) ;
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $ids = implode(',', $user);

            if (!$user) continue;

            $this->pause();

            $request_params['ids'] = $ids;
            $request_params['n'] = $n;
			$get_params = http_build_query($request_params);
            $this->pause();
			$result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getFriends?' . $get_params), true);

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при сборе друзей');
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
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                    $this->pause();
                    $request_params['access_token'] = $this->getToken();
                    $get_params = http_build_query($request_params);
                    $this->setLog('Сменил токен');
                    $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getFriends?' . $get_params), true);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

			if (!isset($result['response'])) continue;

			foreach($result['response'] as $value){
                if ($value['items']) {
                    $data = implode("\n", $value['items']);
                    file_put_contents($this->file, $data, FILE_APPEND);
                }
            }
        }

    }

    public function getFollowersAll($repeatParse, $followersCount)
    {
        $count = count($repeatParse);
        $data = [];
        for($i = 0; $i < $count; $i++)
        {
            $offset = 0;
            do {
                $this->pause();

                // Возвращает за раз до 25000 человек
                $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getFollowersUser?user='.$repeatParse[$i].'&offset='.$offset.'&count='.$followersCount[$i].'&v=5.131&access_token='.$this->access_token), true);
                $offset += 25000;

                // Обрабатываем ошибки
                if (!isset($result['response']))
                {
                    $this->setLog('Возникла ошибка при сборе всех подписчиков у пользователя '.$repeatParse[$i]);
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
                        } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                        $this->pause();
                        $this->access_token = $this->getToken();
                        $this->setLog('Сменил токен');
                        $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getFollowersUser?user='.$repeatParse[$i].'&offset='.$offset.'&count='.$followersCount[$i].'&v=5.131&access_token='.$this->access_token), true);

                        if (isset($result['response'])) $error = false;

                    } while($error == true);

                    $this->setLog('Ошибка устранена');
                }

                $data = array_merge($data, $result['response']);


            } while($offset <= $followersCount[$i]);
        }

        return $data;

    }


    public function getFollowers($users)
    {
        $i = 0;
        $data  = [];
        $extended = [];
        $request_params = array(
            'v'            => '5.131',
            'access_token' => $this->access_token,
        );

        $users = array_chunk($users, 24);
        $cc = 0;
        foreach($users as $user)
        {
            $n = count($user);
            $this->allCount += $n;

            $this->setState('Собираю подписчиков ['.++$cc.'/'.$this->count.']');

            // Высчитываем процент выполнения
            $percent = round(($this->allCount / $this->count) * 100 / $this->checked);
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $ids = implode(',', $user);
            $this->pause();

            $request_params['ids'] = $ids;
            $request_params['n'] = $n;
			$get_params = http_build_query($request_params);
            $this->pause();

            // получаем всех подписчиков где можно спарсить одним запросом (до 1000 штук)
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getFollowers?' . $get_params), true);

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при сборе подписчиков');
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
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $this->setLog('Сменил токен');
                    $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getFollowers?' . $get_params), true);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $allFollowers = [];

            if(count($result['response']) > 1) {

                $followersCount = array_pop($result['response']);
                $repeatParse = array_pop($result['response']);

                // Собираем всех подписчиков у тех, у кого их выше 1000
                $allFollowers = $this->getFollowersAll($repeatParse, $followersCount);
            }

            $data = $result['response'];

            $data = array_merge($allFollowers, $data);

            $data = implode("\n", $data);

            file_put_contents($this->file, $data, FILE_APPEND);
        }

    }
}

$class = new GetFriends($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setState('Завершено');
$class->setStatus();
die;

