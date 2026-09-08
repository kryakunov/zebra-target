<?php

namespace App\Http\Exec;

ini_set('max_execution_time', 0);

use App\Functions;
use App\Http\Exec\Exec;

$id = 310;
$top = 5;


class TopFollowers extends Exec
{
    public $file;
    public $workId;
    public $top;
    public $access_token;

    public $request_params_user = array(
        'user_id'      => '',
        'v'            => '5.130',
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

    public function __construct($workId, $top)
    {
        $this->workId = $workId;
        $this->top = $top;
    }

    public function parse($groups)
    {
        $i = 0;

        $loading = 0;
        $countGroups = count($groups);
        $data = array();

        $groups = Functions::clearGroupName($groups);

        $this->request_params_user['access_token'] = $this->access_token;
        $this->request_params_user['count'] = $this->top;

        foreach($groups as $group)
        {
            $groupId = Functions::getGroupId($group, $this->access_token);
            if(!$groupId) continue;

            $users = Functions::getMembersGroup($groupId, $this->access_token);

            if (!$users) continue;
            $countUsers = count($users['items']);

                foreach($users['items'] as $user)
                {
                    $percent = round(((++$loading / $countUsers) / $countGroups) * 100);
                    $this->setPercent($percent, $this->workId);

                    $this->request_params_user['user_id'] = $user;
                    $this->request_params_user['count'] = $this->top;

                    $i++; if ($i > 2) { sleep(1); $i = 0; }

                    // Делаем запрос к VK API
                    $get_params = http_build_query($this->request_params_user);
                    $response = json_decode(file_get_contents('https://api.vk.ru/method/groups.get?'. $get_params), true);

                    if (isset($response['error']) or !isset($response['response']['items'])) {
                        $this->checkError($response);
                        continue;
                    }

                    // Ищем в подписках пользователя нужных паблик
                    if (in_array($groupId, $response['response']['items']))
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


$class = new TopFollowers($id, $top);

$token = $class->getToken();
$class->setToken($token);

$class->changeStatus($token, 'busy');

if (!$token)
{
    $class->setError('end tokens', $id);
    die;
}

$class->changeStatus($token, 'busy');
$item = $class->getById($id);

$class->file = '../storage/app/'.$item['type'].'/'.$item['vk_id'].'__'.$item['date'] . '.txt';
$groups = $class->getUsers($id);

$class->parse($groups);

$class->changeStatus($token, 'free');

$class->setStatus($id);

$class->setPercent(100, $id);
