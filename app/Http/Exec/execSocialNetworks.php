<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';

class SocialNetworks extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $countMembers;
    public $data = array();
    public $token;
    
    public $request_params = array(
        'v'            => '5.126',
        'fields'       => 'connections,photo_50',
        'count'        => 300,
        'access_token' => '',
    );

    public function handler($request)
    {
        $res = $this->checkRN();
        $users = explode($res, $this->getData());

        $users = $this->parse($users);
        $this->getSocialNetworks($users, $request);
    }

    public function parse($users)
    {

        $temp = array_chunk($users, 300);
        $data = array();
        $i = 0;
        $iter = 0;

        foreach($temp as $value) 
        {
            // Высчитываем процент
            $iter += 300;
            $percent = round(($iter / $this->work['source_count']) * 100);
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $ids = implode(",", $value);
            $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));
        
            $this->request_params['user_ids'] = $ids;

            // Делаем запрос
            $result = $this->vkapi('users.get', $this->request_params);
            
            if (isset($result['response']))
                $result = $result['response'];
            else
                $result = null;

            if (is_array($result))
                $data = array_merge($data, $result);

            $i++; if ($i > 2) {sleep(1); $i = 0;} 

        }

        return $data;
    }


    public function getSocialNetworks($users, $request)
    {
        $data = [];
        foreach($users as $user) 
        {
            if (isset($user['instagram']) and $request['instagram'] == 1) $data['instagram'][] = $user['instagram'];
            if (isset($user['skype'])and $request['skype'] == 1)  $data['skype'][] = $user['skype'];
            if (isset($user['twitter']) and $request['twitter'] == 1)  $data['twitter'][] = $user['twitter'];
            if (isset($user['facebook']) and $request['facebook'] == 1)  $data['facebook'][] = $user['facebook'];
        }

        foreach($data as $value){
            $this->count += count($value);
        }
        
        $this->writeFile($this->file, $data);

    }
}

$class = new SocialNetworks($id);

$request = $class->getRequest($id);

$data = $class->handler($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setStatus();
$class->setPercent(100);
$class->setThisStatus();
$class->setCount($class->count);