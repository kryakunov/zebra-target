<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];
$ot = $argv[2];
$do = $argv[3];

require 'db.php';


class UsersGroups
{
    public $pdo;
    public $file;
    public $id;
    public $ot;
    public $do;
    public $workId;
    public $offset;
    public $streamId;
    public $tempFile;
    public $access_token;

    public $request_params = array(
        'user_id'      => '',
        'v'            => '5.130',
        'count'        => 1000,
        'offset'       => 0,
        'extended'     => 1,
        'fields'       => 'members_count',
        'access_token' => '',
    );


    

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

    public function getUsers($id)
    {
        $item = $this->getById($id);
        $path = '../storage/app/'.$item['type'].'/' . $item['vk_id'] . '_' . $item['date'] . '.txt';
        $file = file_get_contents($path);
        $users = explode("\r\n", $file);


        $count = count($users) / $this->offset;
        $users = array_chunk($users, $count);
        $users = $users[$this->streamId];

        return $users;
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
            $path = '../storage/app/ugroups/tempfiles/';
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



   
}



$class = new UsersGroups(new PDO($dsn, $db_user, $db_password, $options));
$class->id = $id;
$class->ot = $ot;
$class->do = $do;

$workId = $class->getWorkId($id);
$class->workId = $workId;

$offset = $class->getOffset($id);
$class->offset = $offset;

$streamId = $class->getStreamId($id);
$class->streamId = $streamId;

$class->tempFile = '../storage/app/ugroups/tempfiles/'.$workId.'_'.$id.'.txt';

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


$class->file = '../storage/app/ugroups/'.$item['vk_id'].'__'.$item['date'] . '.txt';
$users = $class->getUsers($workId);

$class->logs = '../storage/app/'.$item['type'].'/logs/'.$workId . '.txt';


$class->parse($users);

$class->setStatus($id);
$class->setPercent(100);
 
$class->changeStatus($token, 'free');
$class->checkResult();

die;
