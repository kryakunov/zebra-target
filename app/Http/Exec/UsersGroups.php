<?php


namespace App\Http\Exec;

ini_set('max_execution_time', 0);

use App\Http\Exec\Exec;


class UsersGroups extends Exec
{


}





die;



ini_set('max_execution_time', 0);



require 'db.php';

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

    public $request_params = array(
        'user_id'      => '',
        'v'            => '5.130',
        'count'        => 1000,
        'offset'       => 0,
        'extended'     => 1,
        'fields'       => 'members_count',
        'access_token' => '',
    );

    public function parse($users)
    {
        $i = 0;

        $loading = 0;
        $count = count($users);
        $data = array();

        // Проходим по всем пользователям
        foreach($users as $user)
        {
            $percent = round((++$loading / $count) * 100);
            $this->setPercent($percent);

            $this->request_params['user_id'] = $user;
            $this->request_params['offset'] = 0;

            // Собираем по 1000 сообществ за раз

                // Задержка
                $i++; if ($i > 3) {sleep(1); $i = 0;}

                $params = http_build_query($this->request_params);
                $result = json_decode(file_get_contents('https://api.vk.com/method/groups.get?' . $params), true);
                if (!$result) break;

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



                if ($result['response']['items'])
                {
                    $temp = array();
                    foreach($result['response']['items'] as $value)
                    {
                        if (!isset($value['members_count'])) continue;
                        if ($value['members_count'] > $this->ot  and $value['members_count'] < $this->do)  {
                            $temp[] = $value['id'];
                        }

                    }

                    $data = array_merge($data, $temp);

                }

               // $this->request_params['offset'] = $this->request_params['offset'] + 1000;

            //} while ($this->request_params['offset'] <= $result['response']['count']);
        }

        $data = array_count_values($data);
        arsort($data);

        if (count($data) > 300)
            $data = array_slice($data, 0, 300, true);

       $this->writeFile($this->file, $data);

    }
}



$class = new UsersGroups(new PDO($dsn, $db_user, $db_password, $options));
$class->id = $id;
$class->ot = $ot;
$class->do = $do;

$token = $class->getToken();

if (!$token)
{
    $class->setError('end tokens');
    die;
}


$class->changeStatus($token, 'busy');
$item = $class->getById($id);


$class->file = '../storage/app/ugroups/'.$item['vk_id'].'__'.$item['date'] . '.txt';
$users = $class->getUsers($id);

$class->parse($users);

$class->setStatus($id);
$class->setPercent(100);

$class->changeStatus($token, 'free');
///////////////


/*
$count = $data;

// Собираем информацию о сообществах

$request_params = array(
    'v'            => '5.126',
    'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
    'access_token' => session('token')
);

$i = 0;

//  собираем инф-цию о сообществах
$temp = [];
foreach($data as $key => $value){
    $temp[] = $key;
}
$ids = implode(",", $temp);
$ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

$request_params['group_ids'] = $ids;
$get_params = http_build_query($request_params);
$data = json_decode(file_get_contents('https://api.vk.com/method/groups.getById?' . $get_params), true);

$data = $data['response'];

if (!Functions::isFullAccess()){
    session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 3 значений из ' . count($data));
    $data = array_slice($data, 0, 3);
}
*/
