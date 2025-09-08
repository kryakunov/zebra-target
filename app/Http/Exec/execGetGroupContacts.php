<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';

class GetGroupContacts extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
    }
    
    public function parse($request)
    { 
        $res = $this->checkRN();
        $groups = explode($res, $this->getData());

        $data = [];
        $exceptions = [];
        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'contacts,photo_50',
            'access_token' => $this->access_token,
        );

		$i = 0;

        $groups = array_chunk($groups, 500);
        $iter = 0;
        $count = count($groups);

        foreach($groups as $group) 
        {
            // Высчитываем  процент выполнения задачи
            $percent = round((++$iter / $count) * 100);
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $ids = implode(',', $group);

            $request_params['group_ids'] = $ids;
            $this->pause();

            // Делаем запрос
            $result = $this->vkapi('groups.getById', $request_params);

            
			if (!$result['response']) continue;

            foreach($result['response'] as $value){
                if (empty($value['contacts'])) continue;
    
                foreach($value['contacts'] as $item){
                    if (isset($item['user_id'])){
                        file_put_contents($this->file, $item['user_id'] . "\n", FILE_APPEND);
                    }
                }
            }
        }
    }
}

$class = new GetGroupContacts($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);