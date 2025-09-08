<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';


class UsersTargetGroups extends Exec
{
    public $file;
    public $id;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $iter = 0;
    public $checked = 0;
    public $allCount = 0;
    public $count = 0;
    public $limit = 0;
    public $itogCount = 0;

    public function pause()
    {
        if (++$this->i >= 1) { $this->i = 0; sleep(1); }
        $this->limit = 0;
    }

    public function parse($request)
    {
 
        $res = $this->checkRN();
        $users = explode($res, $this->getData());

        $i = 0;
        file_put_contents($this->file, []);

        $loading = 0;
        (isset($request['count'])) ? $count = $request['count'] : $count = 50;

        $data = [];
        $allCount = count($users);

        $keywords = explode(',', $request['keywords']);


        $continue = false;
        $lastId = $this->getLastId();
        if ($lastId) $continue = true;

        // Проходим по всем пользователям
        foreach($users as $user)
        {

            if($continue) {
                if ($loading != $lastId) continue;
                
                $continue = false;
                $this->setLog('Стартуем сразу с итерации '. $loading);
            }

            $this->setLastId($loading);
            
            // Высчитываем процент
            $percent = round((++$loading / $allCount) * 100);
            if($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $request_params = array(
                'v'            => '5.131',
                'count'        => $count,
                'offset'       => 0,
                'extended'     => 1,
                'user_id'      => $user,
                'access_token' => $this->access_token,
            );

            // Делаем запрос
            $result = $this->vkapi('groups.get', $request_params);

            $this->pause();

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                continue;
            }

            $temp = array();

            foreach($result['response']['items'] as $group)
            {
                // Фильтр по типу сообщества
                if (isset($request['type']))
                    if ($group['type'] <> $request['type']) continue;

                // Фильтр по ключевым словам
                if (count($keywords) > 0)
                {
                    foreach($keywords as $key)
                    {
                        $k1 = mb_strtolower($group['name'], 'UTF-8');
                        $k2 = mb_strtolower($key, 'UTF-8');
                        $pos = stripos($k1, $k2);

                        if ($pos !== false) {
                            $this->count += 1;
                            file_put_contents($this->file, $user . "\n", FILE_APPEND);
                            break;
                        }
                    }
                }
            }
        }
    }


}

$class = new UsersTargetGroups($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setCount($class->count);
$class->setThisStatus();