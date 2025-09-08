<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];
$min = $argv[2];
$max = $argv[3];

$driver = 'mysql'; // тип базы данных, с которой мы будем работать
$host = '127.0.0.1';// альтернатива '127.0.0.1' - адрес хоста, в нашем случае локального
$db_name = 'host1380688_zebranew'; // имя базы данных
$db_user = 'host1380688_root'; // имя пользователя для базы данных
$db_password = 'banlieve'; // пароль пользователя
$charset = 'UTF-8'; // кодировка по умолчанию
$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION); // массив с дополнительными настройками подключения. В данном примере мы установили отображение ошибок, связанных с базой данных, в виде исключений

$dsn = "$driver:host=$host;dbname=$db_name";


class GetMembers
{
    public $pdo;
    public $file;
    public $id;
    public $access_token;
    public $countGroups;
    public $countMembers;
    public $data = array();
    public $min = 0;
    public $max = 1000;
    public $loading = 0;
    public $groupIds;
    public $workId;
    public $offset;
    public $streamId;

    public $request_params = array(
        'user_id'      => '',
        'v'            => '5.130',
        'count'        => 10000,
        'offset'       => 0,
        'access_token' => '',
    );

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getToken()
    {
        $sql = "SELECT * FROM tokens WHERE status = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, 'free');
        $statement->execute();
        $token = $statement->fetch(PDO::FETCH_ASSOC);

        $this->changeStatus($token['token'], 'busy');

        if (!$token) return false;
        sleep(1);
        // Проверяем токен на валидность
        $result = json_decode(file_get_contents('https://api.vk.ru/method/users.get?v=5.131&access_token='.$token['token']), true);
        if(!isset($result['response']))
        {
            $this->changeStatus($token['token'], 'no valid');
            $this->getToken();
            sleep(1);
        }

        $sql = 'UPDATE streams SET token_id=:token_id WHERE id=:id';
        $values = array("token_id" => $token['id'], "id" => $this->id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

        $sql = 'UPDATE tokens SET date=:date WHERE id=:id';
        $values = array("date" => time(), "id" => $token['id']);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

        $this->request_params['access_token'] = $token['token'];
        $this->access_token = $token['token'];

        return $token['token'];
    }


    public function changeStatus($token, $status)
    {
        $sql = 'UPDATE tokens SET status=:status WHERE token=:token';
        $values = array("status" => $status, "token" => $token);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM myworks WHERE id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res;
    }

    public function getUsers($id)
    {
        $item = $this->getById($id);
        $path = '../storage/app/'.$item['type'].'/' . $item['vk_id'] . '_' . $item['date'] . '.txt';
        $file = file_get_contents($path);
        $users = explode("\r\n", $file);

        return $users;
    }

    public function openFile($file)
    {
        $data = file_get_contents($file);
		$data = unserialize($data);

        return $data;
    }

    public function setPercent($percent)
    {
        $sql = 'UPDATE streams SET percent=:percent WHERE id=:id';
        $values = array("percent" => $percent, "id" => $this->id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
    }

    public function setPercentInMyworkTable()
    {
        $sql = "SELECT * FROM streams WHERE mywork_id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $this->workId);
        $statement->execute();
        $res = $statement->fetchAll(PDO::FETCH_ASSOC);

        $percent = 0;
        foreach($res as $value)
        {
            $percent += $value['percent'];
        }

        $percent = $percent / count($res);

        $sql = 'UPDATE myworks SET percent=:percent WHERE id=:id';
        $values = array("percent" => $percent, "id" => $this->workId);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

    }

    public function setStatus($id)
    {
        $sql = 'UPDATE streams SET status=:status WHERE id=:id';
        $values = array("status" => 1, "id" => $id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
    }

    public function writeFile($file, $data)
    {
        $data = serialize($data);
		file_put_contents($file, $data);
    }

    public function setError($msg)
    {
        $sql = 'UPDATE streams SET error=:error WHERE id=:id';
        $values = array("error" => $msg, "id" => $this->id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

        file_put_contents($this->logs, $msg . "\n", FILE_APPEND);
    }

    public function getMembersGroupNew($group, $membersCount, $offset, $offsetDo)
    {
        $data = array();
        $i = 0;
        $n = 24;
        do{

            $this->setPercentInMyworkTable();

            // Вычисляем count, чтобы парсил ровное число пользователей
            if (($offsetDo - $offset) < 25000)
            {
                $count = $offsetDo - $offset;
                if ($count > 1000){
                    $n = round($count / 1000);
                    $n++;
                    $count = $count % 1000;
                }else{
                    $n = 1;
                }
            } else
                $count = 1000;

            // Парсим
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getMembers?group_id='.$group.'&n='.$n.'&offset='.$offset.'&count='.$count.'&v=5.131&access_token='.$this->access_token), true);

            if ($i >= 3) { $i = 0; sleep(1); }

            // В случае ошибки
            if (isset($result['error']))
            {
                $error = $result['error']['error_code'] . ' > ' .$result['error']['error_msg'];
                $this->setError($error);

                if ($result['error']['error_code'] == 6 or $result['error']['error_code'] == 29 or $result['error']['error_code'] == 5)
                {
                    if ($this->getToken())
                    {
                        $this->access_token = $this->getToken();
                    } else
                    {
                        $this->setError('End tokens');

                        die;
                    }
                }
                continue;
            }

            if(!isset($result['response'])) continue;

            $offset += 25000;

            $this->loading += 10000;
            $percent = round(($this->loading / $this->countMembers) * 100);
            $this->setPercent($percent);

            $data = implode("\n", $result['response'])."\n";

            file_put_contents($this->file, $data, FILE_APPEND);

        }while($offset <= $offsetDo);

        file_put_contents('otchet.txt', "group: $group, offset: $offset, offsetDo: $offsetDo, count: $cc \n", FILE_APPEND);

    }

    public function getMembersGroup($group, $offset, $offsetDo)
    {
        // Передаваемые параметры
        $request_params = array(
            'group_id'     => $group,
            'offset'       => $offset,
            'count'        => '1000',
            'v'            => '5.131',
            'access_token' => $this->access_token,
        );
        $data = array();
        $i = 0;

        // Собираем участников сообщества
        do {

            $this->setPercentInMyworkTable();

            // Вычисляем count, чтобы парсил ровное число пользователей
            if (($offsetDo - $request_params['offset']) < 1000)
                $request_params['count'] = $offsetDo - $request_params['offset'];
            else
                $request_params['count'] = 1000;

            $i++; if ($i > 1) { sleep(1); $i = 0; }

            // Делаем запрос к VK API
            $get_params = http_build_query($request_params);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?'. $get_params), true);

            // В случае ошибки
            if (isset($result['error']))
            {
                $this->setError($result['error']['error_code'] . ' > ' .$result['error']['error_msg']);

                if ($result['error']['error_code'] == 6 or $result['error']['error_code'] == 29)
                {
                    if ($this->getToken())
                    {
                        $this->request_params['access_token'] = $this->getToken();
                    } else
                    {
                        $this->setError('End tokens');

                        die;
                    }
                }
                continue;
            }
            if (!isset($result['response']['count']) or !isset($result['response']['items'])) continue;

            $request_params['offset'] += 1000;
            $countUsers = $result['response']['count'];

            $this->loading += 1000;
            $percent = round(($this->loading / $this->countMembers) * 100);
            $this->setPercent($percent);


            $data = implode("\n", $result['response']['items'])."\n";

           // $this->data = array_merge($this->data,  $result['response']['items']);
            file_put_contents($this->file, $data, FILE_APPEND);

        } while ($request_params['offset'] <= $offsetDo);

    }

    public function parse($groups)
    {
        $i = 0;
        $loading = 0;
        $this->countGroups = count($groups);

        foreach($groups as $group)
        {
            // В этот файл будем писать всех спарсенных пользователей
            $this->tempFile = '../storage/app/getmembers/tempfiles/'.$this->workId.'_'.$group.'.txt';

            // Узнаем сколько участников в сообществе
            $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?group_id='.$group.'&offset=0&count=1000&v=5.131&access_token='.$this->access_token), true);

            // Если участники сообщества скрыты то прерывает итерацию цикла
            if (isset($result['error'])) {
                continue;
            }

            $membersCount = $result['response']['count'];

            // Вычисляем смещение при парсинге
            $part = $membersCount / $this->offset;
            $offset = (int)$part * $this->streamId;
            $offsetDo = (int)$part + $offset;

            // Парсим
            $this->getMembersGroupNew($group, $membersCount, $offset, $offsetDo);

        }

/*
        $this->data = array_count_values($this->data);
        arsort($this->data);

        $temp = array();
        foreach($this->data as $key => $value){
            if ($value <= $this->max and $value >= $this->min){
                $temp[] = $key;
            }
        }

        $this->writeFile($this->file, $temp);
*/
    }

    public function getStreamId($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res['stream_id'];
    }

    public function getOffset($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res['offset'];
    }

    public function getWorkId($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res['mywork_id'];
    }

    public function checkResult($workId)
    {
        $sql = "SELECT * FROM streams WHERE mywork_id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $workId);
        $statement->execute();
        $res = $statement->fetchAll(PDO::FETCH_ASSOC);

        $result = false;
        foreach($res as $value){
            if ($value['status'] == 0) {
                $result = false;
                break;
            }
            $result = true;
        }

        // Если все задачи успешно отработали
        if ($result == true)
        {
            /*
            $path = '../storage/app/getmembers/tempfiles/';
            $data = array();

            $groups = file_get_contents($this->sourceFile);
            $groups = explode("\r\n", $groups);

            foreach($groups as $group)
            {
                $file = $path . $workId.'_'.$group.'.txt';
                $users = file_get_contents($file);

                file_put_contents($this->file, $users, FILE_APPEND);
                //unlink($file);
            } */


            if ($this->min <> 0 or $this->max <> 999) {
                $data = file_get_contents($this->file);
                $data = explode("\n", $data);
                $data = array_count_values($data);
                arsort($data);
                unlink($this->file);

                foreach($data as $key => $value){
                    if ($value <= $this->max and $value >= $this->min){
                        file_put_contents($this->file, $key."\n", FILE_APPEND);
                    }
                }
            }

            $sql = 'UPDATE myworks SET status=:status, percent=:percent WHERE id=:id';
            $values = array("status" => 1, "percent" => "100", "id" => $this->workId);
            $statement = $this->pdo->prepare($sql);
            $statement->execute($values);

        }
    }


}


$class = new GetMembers(new PDO($dsn, $db_user, $db_password, $options));
$class->id = $id;
$class->min = $min;
$class->max = $max;

$workId = $class->getWorkId($id);
$class->workId = $workId;

$offset = $class->getOffset($id);
$class->offset = $offset;

$streamId = $class->getStreamId($id);
$class->streamId = $streamId;


sleep($streamId);

$token = $class->getToken();

if (!$token)
{
    $class->setError('end tokens');
    die;
}

$class->changeStatus($token, 'busy');
$item = $class->getById($workId);
$class->countMembers = $item['count'];

$class->file = '../storage/app/'.$item['type'].'/'.$item['vk_id'].'__'.$item['date'] . '.txt';
$class->sourceFile = '../storage/app/'.$item['type'].'/'.$item['vk_id'].'_'.$item['date'] . '.txt';
$class->logs = '../storage/app/'.$item['type'].'/logs/'.$workId . '.txt';


$users = $class->getUsers($workId);

$class->parse($users);

$class->setStatus($id);
$class->setPercent(100);

$class->changeStatus($token, 'free');

$class->checkResult($workId);
