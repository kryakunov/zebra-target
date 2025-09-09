<?php

namespace App\Http\Execute;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Execute.php';

class UsersGroups extends Execute
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
        $this->limit = 0;
    }

    public function parse($request)
    {

        $res = $this->checkRN();
        $users = explode($res, $this->getData());

        ($request['ot']) ? $min = $request['ot'] : $min= 0;
        ($request['do']) ? $max = $request['do'] : $max = 10000000000;

        $i = 0;
        $this->writeFile($this->tempFile, []);
        $this->writeFile($this->file, []);

        $loading = 0;
        $count = count($users);
        $data = [];

        $allCount = count($users);

        $usersChunk = array_chunk($users, 25);

        // Проходим по всем пользователям
        foreach($usersChunk as $users)
        {
            $n = count($users);
            $users = implode(',', $users);

            $this->setState('Собираю сообщества... ['.$loading.'/'.$count.']');
            $loading += 25;

            // Высчитываем процент
            $percent = round(($loading / $allCount) * 100);
            if($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $result = json_decode(file_get_contents('https://api.vk.com/method/execute.groupsGet?v=5.130&count=1000&offset=0&extended=1&n='.$n.'&users='.$users.'&access_token='.$this->access_token), true);

            $this->pause();

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
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result));

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $this->setLog('Сменил токен');
                    $params = http_build_query($request_params);
                    $result = json_decode(file_get_contents('https://api.vk.com/method/execute.groupsGet?v=5.130&count=1000&offset=0&extended=1&n='.$n.'&users='.$users.'&access_token='.$this->access_token), true);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $temp = array();

            $count = count($result['response'][0]);

            if ($min > 0 || $max < 10000000000) {
                for($i = 0; $i < $count; $i++){
                    if($result['response'][1][$i] == null) continue;
                    if ($result['response'][1][$i] >= $min  and $result['response'][1][$i] <= $max)  {
                        $temp[] = $result['response'][0][$i];
                    }
                }
            } else $temp = $result['response'][0];

            $temp = array_count_values($temp);

            $data = $this->openFile($this->tempFile);

            // Объединяем с предыдущими данными из темп файла и подсчитываем сколько нашлось повторяющихся ид
            if (is_array($temp))
            foreach($temp as $key=>$value) {
                if (key_exists($key, $data))
                    $data[$key] = $data[$key] + $value;
                else $data[$key] = $value;
            }

            arsort($data);

            // Обрезаем массив, чтобы он не разростался бесконечно
            if (count($data) > 100000)
                $data = array_slice($data, 0, 100000, true);

            $this->writeFile($this->tempFile, $data);

            unset($data);
        }

    }

    public function checkResult()
    {



                $data = $this->openFile($this->file);

                $temp = $this->openFile($this->tempFile);

                if (is_array($temp))
                foreach($temp as $key=>$value) {
                    if (key_exists($key, $data))
                        $data[$key] = $data[$key] + $value;
                    else $data[$key] = $value;
                }

                arsort($data);

                if (count($data) > 30000)
                    $data = array_slice($data, 0, 30000, true);

                $this->count = count($data);
                $this->writeFile($this->file, $data);

                unlink($this->tempFile);


            $data = $this->openFile($this->file);

            // Меняем ключ/значение
            $temp = [];
            foreach($data as $key => $value){
                $temp[] = $key;
            }
            $data = $temp;

            $this->count = count($data);
            $data = implode("\n", $data);

            file_put_contents($this->file, $data);

            $sql = 'UPDATE myworks SET status=:status, percent=:percent, count=:count WHERE id=:id';
            $values = array("status" => 1, "percent" => "100", "count" => $this->count, "id" => $this->work['id']);
            $statement = $this->PDO->prepare($sql);
            $statement->execute($values);


    }
}

$class = new UsersGroups($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setCount($class->count);
$class->checkResult();
$class->setState('Завершено');
$class->setStatus();
