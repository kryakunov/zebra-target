<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';


class UsersGroups extends Exec
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

        (isset($request['ot'])) ? $min = $request['ot'] : $min= 0;
        (isset($request['do'])) ? $max = $request['do'] : $max = 10000000000;

        $i = 0;
        $this->writeFile($this->tempFile, []);
        $this->writeFile($this->file, []);

        $loading = 0;
        $count = count($users);
        $data = [];

        // Вычисляем смещение 
        $part = $count / $this->work['streams'];
        $users = array_chunk($users, $part);
        $users = $users[$this->stream['stream_id']];
        $countUsers = count($users);

        $usersChunk = array_chunk($users, 25);

        $continue = false;
        $lastId = $this->getLastId();
        if ($lastId) $continue = true;

        // Проходим по всем пользователям
        foreach($usersChunk as $users)
        {
            $loading += 25; 
            
            if($continue) {
                if ($loading != $lastId) continue;
                
                $continue = false;
                $this->setLog('Стартуем сразу с итерации '. $loading);
            }

            $this->setLastId($loading);
        
            $n = count($users);
            $users = implode(',', $users);
      

            // Высчитываем процент
            $percent = round(($loading / $countUsers) * 100);
            if($percent > 99) $percent = 99;
            $this->setPercent($percent);
            

            $request_params = array(
                'v'            => '5.130',
                'count'        => 10000,
                'offset'       => 0,
                'extended'     => 1,
                'n'            => $n,
                'users'        => $users,
                'access_token' => $this->access_token,
            );


            // Делаем запрос
            $result = $this->vkapi('execute.groupsGet', $request_params);

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
                    $request_params['access_token'] = $this->access_token;
                    $this->setLog('Сменил токен');

                    // Делаем запрос
                    $result = $this->vkapi('execute.groupsGet', $request_params);

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
        
        $this->setLog('check result');
        $sql = "SELECT * FROM streams WHERE mywork_id = ?";
                        
        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $this->work['id']);
        $statement->execute();
        $res = $statement->fetchAll(PDO::FETCH_ASSOC);

        $result = false;
        foreach($res as $value){

            if ($value['status'] == 0) 
            {
                $result = false;
                break;
            }
            $result = true;
        }

        // Если все задачи успешно отработали
        if ($result == true)
        {
            $path = '../storage/app/'.self::TEMPFILE.'/';

            foreach($res as $value)
            {
                $data = $this->openFile($this->file);

                $tempFile = $path . $value['mywork_id'].'_'.$value['stream_id'].'.txt';
                $temp = $this->openFile($tempFile);

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

                unlink($tempFile);
            }

            $data = $this->openFile($this->file);

            // Меняем ключ/значение
            $temp = [];
            foreach($data as $key => $value){
                $temp[] = $key;
            }
            $data = $temp;
            $data = array_slice($data, 0, 1000, true);

            $this->count = count($data);
            $data = implode("\n", $data);
            file_put_contents($this->file, $data);

            $sql = 'UPDATE myworks SET status=:status, percent=:percent, count=:count WHERE id=:id';
            $values = array("status" => 1, "percent" => "100", "count" => $this->count, "id" => $this->work['id']);
            $statement = $this->PDO->prepare($sql);
            $statement->execute($values); 

        }
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
$class->setThisStatus();
$class->checkResult();