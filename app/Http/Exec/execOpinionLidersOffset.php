<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'db.php';


class OpinionLiders
{
    public $pdo;
    public $file;
    public $id;
    public $workId;
    public $offset;
    public $streamId;
    public $tempFile;

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
        $path = '../storage/app/liders/' . $item['vk_id'] . '_' . $item['date'] . '.txt';
        $file = file_get_contents($path);
        $users = explode("\r\n", $file);

        $count = count($users) / $this->offset;
        $users = array_chunk($users, $count);
        $users = $users[$this->streamId];

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

    public function checkResult()
    {
        $sql = "SELECT * FROM streams WHERE mywork_id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $this->workId);
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
            $path = '../storage/app/liders/tempfiles/';
            $data = array();

            foreach($res as $value)
            {
                $file = $path . $value['mywork_id'].'_'.$value['id'].'.txt';

                $temp = $this->openFile($file);

                if (is_array($temp))
                foreach($temp as $key=>$value) {
                    if (key_exists($key, $data))
                        $data[$key] = $data[$key] + $value;
                    else $data[$key] = $value;
                }

                arsort($data);

                if (count($data) > 1000)
                    $data = array_slice($data, 0, 1000, true);

                $this->writeFile($this->file, $data);

                unlink($file);
            }

            $sql = 'UPDATE myworks SET status=:status, percent=:percent WHERE id=:id';
            $values = array("status" => 1, "percent" => "100", "id" => $this->workId);
            $statement = $this->pdo->prepare($sql);
            $statement->execute($values);

        }
    }

    public function getOffset($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        file_put_contents('str.txt', $res['offset']."\n", FILE_APPEND);
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

    public function getStreamId($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res['stream_id'];
    }


    public function parse($users)
    {
        $i = 0;
        $this->writeFile($this->tempFile, array());
        $loading = 0;
        $count = count($users);

        $usersChunk = array_chunk($users, 25);

        foreach($usersChunk as $users)
        {
            $percent = round(($loading += 25 / $count) * 100);
            $this->setPercent($percent);
            $this->setPercentInMyworkTable();

            if (++$i > 2) { sleep(1); $i = 0; }

            $n = count($users) - 1;
            $users = implode(',', $users);

            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.lidersGet?v=5.131&count=10000&n='.$n.'&users='.$users.'&access_token='.$this->access_token), true);

            if (isset($result['error']))
            {

                $this->setError($result['error']['error_code'] . ' > ' .$result['error']['error_msg']);

                if ($result['error']['error_code'] == 6 or $result['error']['error_code'] == 29)
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

            $response = array_count_values($result['response']);

            $data = $this->openFile($this->tempFile);

            if (is_array($response))
            foreach($response as $key=>$value) {
                if (key_exists($key, $data))
                    $data[$key] = $data[$key] + $value;
                else $data[$key] = $value;
            }

            arsort($data);
            if (count($data) > 50000)
                $data = array_slice($data, 0, 50000, true);

            $this->writeFile($this->tempFile, $data);

        }
    }
}


$class = new OpinionLiders(new PDO($dsn, $db_user, $db_password, $options));
$class->id = $id;


$workId = $class->getWorkId($id);
$class->workId = $workId;

$offset = $class->getOffset($id);
$class->offset = $offset;

$streamId = $class->getStreamId($id);
$class->streamId = $streamId;

$class->tempFile = '../storage/app/liders/tempfiles/'.$workId.'_'.$id.'.txt';

sleep($streamId);

$token = $class->getToken();

if (!$token)
{
    $class->setError('end tokens');
    die;
}
$class->access_token = $token;
$class->changeStatus($token, 'busy');
$item = $class->getById($workId);

$class->file = '../storage/app/liders/'.$item['vk_id'].'__'.$item['date'] . '.txt';

$class->logs = '../storage/app/liders/logs/'.$workId . '.txt';

$users = $class->getUsers($workId);

$class->parse($users);

$class->setStatus($id);
$class->setPercent(100);


$class->changeStatus($token, 'free');
$class->checkResult();
die;
