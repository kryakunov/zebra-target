<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';

class OpinionLiders extends Exec
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
    public $count;
    public $limit = 0;

    public function pause()
    {
        if (++$this->i >= 1) { $this->i = 0; sleep(1); }
        $this->limit = $this->limit + 1;
    }
    
    public function parse($request)
    { 
        $res = $this->checkRN();
        $users = explode($res, $this->getData());

        $i = 0;
        $this->writeFile($this->tempFile, []);
        $loading = 0;

        $usersChunk = array_chunk($users, 25);
        $countUsers = count($users);

        $continue = false;
        $lastId = $this->getLastId();
        if ($lastId) $continue = true;

        foreach($usersChunk as $users) 
        {

            $loading += 25; 

            if($continue) {
                if ($loading != $lastId) continue;
                
                $continue = false;
                $this->setLog('Стартуем сразу с итерации '. $loading);
            }

            $this->setLastId($loading);

            $percent = round(($loading / $countUsers) * 100);
            if($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $this->pause();
 
            $n = count($users) - 1; 

            $users = implode(',', $users);

            $request_params = array(
                'v'            => '5.131',
                'count'        => 10000,
                'n'            => $n,
                'users'        => $users,
                'access_token' => $this->access_token,
            );
    

            // Делаем запрос
            $result = $this->vkapi('execute.lidersGet', $request_params);
            
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
                    } elseif (isset($result['execute_errors']))
                    {
                        foreach($result['execute_errors'] as $value){
                            $this->setError($value['error_code'] . ' > ' . $result['error_msg']);
                            $this->setLog($value['error_code'] . ' > ' . $result['error_msg']);
                        }
                    } else {
                        $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result));
                    }

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $request_params['access_token'] = $this->access_token;
                    $this->setLog('Сменил токен');

                    // Делаем запрос
                    $result = $this->vkapi('execute.lidersGet', $request_params);

                    if (isset($result['response'])) $error = false;

                } while($error == true);
                
                $this->setLog('Ошибка устранена');
            }

            $data = [];

            // Собираем всех друзей
            foreach($result['response'] as $value)
            {
                $data = array_merge($data, $value['items']);
            }

            $data = array_count_values($data);

            $response = $this->openFile($this->tempFile);

            // Объединяем с предыдущими данными из темп файла и подсчитываем сколько нашлось повторяющихся ид
            if (is_array($response))
            foreach($response as $key=>$value) {
                if (key_exists($key, $data)) {
                    $data[$key] = $data[$key] + $value;
                } else $data[$key] = $value;
            }

            arsort($data);

            // Обрезаем массив, чтобы он не разростался бесконечно
            if (count($data) > 100000)
                $data = array_slice($data, 0, 100000, true);


            $this->writeFile($this->tempFile, $data);
        }

        $data = $this->openFile($this->tempFile);
        $data = array_slice($data, 0, 10000, true);

        // Если задан параметр - сколько минимум раз лидер мнений должен встречаться в друзьях у пользователей
        // И заодно меняем ключ/значение
        $temp = [];
        
        $cc = serialize($data);
        file_put_contents('bla.txt', $cc);
        
        if (isset($request['count'])) {
            foreach($data as $key => $value){
                if ($value >= $request['count']) {
                    $temp[] = $key;
                } else break;
            }
        } else {
            foreach($data as $key => $value){
                $temp[] = $key;
            }
        }

        $data = $temp;
        $data = array_slice($data, 0, 1000, true);

        $this->count = count($data);
        $data = implode("\n", $data);
        file_put_contents($this->file, $data);
        unlink($this->tempFile);
    }
}

$class = new OpinionLiders($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setCount($class->count);
die; 