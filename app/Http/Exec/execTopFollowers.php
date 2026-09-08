<?php

ini_set('max_execution_time', 0);
use App\Functions;


// Принимаем аргументы
$workId = $argv[1];
$top = $argv[2];


require 'db.php';


class TopFollowers
{
    public $pdo;
    public $file;
    public $workId;
    public $top;
    public $access_token;

    public $request_params_user = array(
        'user_id'      => '',
       // 'v'            => '5.130',
        'count'        => '',
        'offset'       => 0,
      //  'extended'     => 1,
      //  'fields'       => 'members_count',
        'access_token' => '',
    );

    public $request_params_group = array(
        'user_id'      => '',
        'v'            => '5.130',
        'count'        => '',
        'filter'       => 'publics',
        'offset'       => 0,
       // 'extended'     => 1,
        'fields'       => 'members_count',
        'access_token' => ''
    );

    public function __construct($pdo, $workId = null, $top = null)
    {
        $this->pdo = $pdo;
        $this->workId = $workId;
        $this->top = $top;
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
        $users = explode("\n", $file);

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

    public function getMembersGroup($group)
    {
        // Передаваемые параметры
        $request_params = array(
            'group_id'     => $group,
            'offset'       => 0,
            'count'        => '1000',
            'v'            => '5.131',
            'access_token' => $this->access_token,
        );
        $data = array();
        $i = 0;

        // Собираем участников сообщества
        do {
            // Делаем запрос к VK API
            $get_params = http_build_query($request_params);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?'. $get_params), true);
            $request_params['offset'] = $request_params['offset'] + 1000;
            if (array_key_exists('error', $result)) break;
            $countUsers = $result['response']['count'];

            $i++; if ($i > 1) { sleep(1); $i = 0; }

            $data = array_merge($data, $result['response']['items']);

        } while ($request_params['offset'] <= $result['response']['count']);

        return $data;
    }

	public function parse($groups)
	{
        $i = 0;

        $loading = 0;
        $countGroups = count($groups);
        $data = array();

        $this->request_params_user['access_token'] = $this->access_token;
        $this->request_params_user['count'] = $this->top;

        foreach($groups as $group)
        {
            $users = $this->getMembersGroup($group);

            if (!$users) continue;
            $countUsers = count($users);

                foreach($users as $user)
                {
                    $percent = round(((++$loading / $countUsers) / $countGroups) * 100);
                    $this->setPercent($percent, $this->workId);

                    $this->request_params_user['user_id'] = $user;
                    $this->request_params_user['count'] = $this->top;

                    $i++; if ($i > 1) { sleep(1); $i = 0; }

                    // Делаем запрос к VK API
                    $get_params = http_build_query($this->request_params_user);
                    $response = json_decode(file_get_contents('https://api.vk.ru/method/groups.get?v=5.130&'. $get_params), true);

                    if (isset($response['error']) or !isset($response['response']['items'])) {
                        $this->checkError($response);
                        continue;
                    }

                    // Ищем в подписках пользователя нужных паблик
                    if (in_array($group, $response['response']['items']))
                    {
                        $data[] = $user;
                    }
                }
        }

        $data = array_unique($data);
        $this->writeFile($this->file, $data);

    }

    public function checkError($result)
    {
        if (isset($result['error']))
        {
            $this->setError($result['error']['error_code'] . ' > ' .$result['error']['error_msg'], $this->workId);

            if ($result['error']['error_code'] == 6 or $result['error']['error_code'] == 29)
            {
                $token = $this->getToken();
                if ($token)
                {
                    $this->request_params_user['access_token'] = $token;
                    $this->request_params_group['access_token'] = $token;
                } else
                {
                    $this->setError('End tokens', $this->workId);
                    die;
                }
            }
        }
    }
}



$class = new TopFollowers(new PDO($dsn, $db_user, $db_password, $options), $workId, $top);
$class->id = $workId;
$class->top = $top;

$token = $class->getToken();
$class->access_token = $token;

if (!$token)
{
    $class->setError('end tokens');
    die;
}

$class->changeStatus($token, 'busy');
$item = $class->getById($workId);

$class->file = '../storage/app/topfollowers/'.$item['vk_id'].'__'.$item['date'] . '.txt';

$users = $class->getUsers($workId);

$class->parse($users);

$class->setStatus($workId);
$class->setPercent(100);


$class->changeStatus($token, 'free');

die;

////////////////////




///////////////////

/*
$data = file_get_contents($file);
$data = unserialize($data);
$data = array_slice($data, 1, $top, true);
$data = serialize($data);
file_put_contents($file, $data);
rename($file, $successfile);
unlink('tempFiles/'.$argv[3].'.txt');

/*
foreach($data as $value) {

	$groups[] = $value['id'];
	$names[$value['id']] = $value;
	$value['name'] = preg_replace("/[^а-яёa-z,]/iu", ' ', $value['name']);
	$keys = array_merge($keys, explode(" ", $value['name']));
}

$temp = array();
$keys = array_count_values($keys);
arsort($keys);

foreach($keys as $key => $value) {
	if (mb_strlen($key,'UTF-8') < 3) continue;
	if ($value < 2) break;
	$temp[$key] = $value;
}

$keys = $temp;
$temp = array();

$groups = array_count_values($groups);
arsort($groups);

foreach($groups as $key => $value) {
	if ($value < 2) break;
	$temp[$key] = $value;
}
$groups = $temp;
unset($temp);



// Записывает результат в файл в формате [ ИМЯ / сколько человек состоит / процент ]
foreach($groups as $key => $value) {
	$str = $names[$key]['name'].'/=>'.$value.'/=>'.round(( $value / count($users)) * 100);

}
*/



?>
