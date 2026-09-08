<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

$driver = 'mysql'; // тип базы данных, с которой мы будем работать
$host = '127.0.0.1';// альтернатива '127.0.0.1' - адрес хоста, в нашем случае локального
$db_name = 'host1380688_zebranew'; // имя базы данных
$db_user = 'host1380688_root'; // имя пользователя для базы данных
$db_password = 'banlieve'; // пароль пользователя
$charset = 'UTF-8'; // кодировка по умолчанию
$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION); // массив с дополнительными настройками подключения. В данном примере мы установили отображение ошибок, связанных с базой данных, в виде исключений

$dsn = "$driver:host=$host;dbname=$db_name";

class FilterUsers
{
    public $pdo;
    public $file;
    public $id;
    public $access_token;
    public $countMembers;
    public $data = array();
    public $token;

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

        $this->changeStatus($token['token'], 'busy');

        $sql = 'UPDATE myworks SET token_id=:token_id WHERE id=:id';
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

    public function setToken($token)
    {
        $this->token = $token;
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
        unset($users[0]);

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
        $sql = 'UPDATE myworks SET percent=:percent WHERE id=:id';
        $values = array("percent" => $percent, "id" => $this->id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
    }

    public function setStatus($id)
    {
        $sql = 'UPDATE myworks SET status=:status WHERE id=:id';
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
        $sql = 'UPDATE myworks SET error=:error WHERE id=:id';
        $values = array("error" => $msg, "id" => $this->id);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);

        file_put_contents('logs.txt', $msg."\n", FILE_APPEND);
    }

    public function getParams($id)
    {
        $item = $this->getById($id);
        $file = '../storage/app/'.$item['type'].'/' . $item['vk_id'] . '_' . $item['date'] . '.txt';

        $data = file_get_contents($file);
        $data = explode("\r\n", $data);
        $params = $data[0];
        $params = unserialize($params);

        return $params;
    }

    public function parse($users, $params)
    {
        $i = 0;
        $data = $users;
        $loading = 0;

        foreach ($users as $user)
        {
            $percent = (++$loading / $this->countMembers) * 100;
            $this->setPercent($percent);

            $url = "https://api.vk.ru/method/users.get?user_id=".$user.",&fields=counters&count=300&v=5.89&access_token=".$this->token;
            $result = json_decode(file_get_contents($url),true);
            if (++$i >= 2) { sleep(1); $i = 0; }
            if (!isset($result['response'][0]['counters'])) continue;


            $value = $result['response'][0];

            if ($value['is_closed'] == true) continue;
            if (isset($value['deactivated'])) continue;

            // Фильтр по альбомам
            if (isset($value['counters']['photos'])) {
                if ($value['counters']['photos'] <= $params['photos_max'] and $value['counters']['photos'] >= $params['photos_min']){
                    $photos[] = $value['id'];
                }
            }

            // Фильтр по альбомам
            if (isset($value['counters']['subscriptions'])) {
                if ($value['counters']['subscriptions'] <= $params['subscriptions_max'] and $value['counters']['subscriptions'] >= $params['subscriptions_min']){
                    $subscriptions[] = $value['id'];
                }
            }

            // Фильтр по друзьям
            if (isset($value['counters']['friends'])) {
                if ($value['counters']['friends'] <= $params['friends_max'] and $value['counters']['friends'] >= $params['friends_min']){
                    $friends[] = $value['id'];
                }
            }

        }


        if (!empty($photos)) {
            $data = array_intersect($data, $photos);
            $done = true;
        }

        if (!empty($subscriptions)) {
            $data = array_intersect($data, $subscriptions);
            $done = true;
        }
        if (!empty($friends)) {
            $data = array_intersect($data, $friends);
            $done = true;
        }

        if ((count($data) == count($users)) and $done = true){
           $data = array();
        }

        return $data;
    }
}


$class = new FilterUsers(new PDO($dsn, $db_user, $db_password, $options));
$class->id = $id;

$token = $class->getToken();
$class->setToken($token);

if (!$token)
{
    $class->setError('end tokens');
    die;
}

$class->changeStatus($token, 'busy');
$item = $class->getById($id);
$class->countMembers = $item['count'];

$class->file = '../storage/app/'.$item['type'].'/'.$item['vk_id'].'__'.$item['date'] . '.txt';

$users = $class->getUsers($id);
$params = $class->getParams($id);

$data = $class->parse($users, $params);

$class->writeFile($class->file, $data);

$class->setStatus($id);
$class->setPercent(100);

$class->changeStatus($token, 'free');
