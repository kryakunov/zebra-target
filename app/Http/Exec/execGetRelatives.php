
<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';

class GetRelatives extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $countMembers;
    public $data = array();
    public $token;
    public $i = 0;

    public function parse($request)
    {
        $this->setLog('Получил данные');

        $res = $this->checkRN();
        $users = explode($res, $this->getData());

        // Высчитываем на сколько частей разбить массив
        $count = count($users);
        $streams = $this->work['streams'];

        $users = array_chunk($users, ceil($count / $streams));
        $users = $users[$this->stream['stream_id']];

        $users = array_chunk($users, 300);
 
        $i = 0;
        $iter = 0;
        $count = count($users);

        $this->setLog('Начинаю парсинг');

        foreach ($users as $user) 
        {
            $percent =  round((++$iter / $count) * 100);
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $ids = implode(",", $user);

            $request_params = array(
                'v'            => '5.89',
                'access_token' => $this->access_token,
                'fields'        => 'relatives,relation',
                'user_ids'        => $ids,
                'offset'        => 0,
                'count'        => 300,
            );

            // Делаем запрос
            $result = $this->vkapi('users.get', $request_params);
            
            $this->pause();

            if (isset($result['error']) or isset($result['execute_errors']))
            { 
                $this->setLog('Возникла ошибка при запросе к АПИ');
                $error = true;
                do {

                    if (isset($result['execute_errors'])){
                        foreach($result['execute_errors'] as $value){
                            $this->setError($value['error_code'] . ' > ' . $result['error_msg']);
                            $this->setLog('Ошибка ' . $result['error_msg']);
                        }
                    } elseif(isset($result['error']) ){
                        $this->setError($result['error']['error_code'] . ' > ' . $result['error']['error_msg']);
                        $this->setLog('Ошибка ' . $result['error']['error_msg']);
                    }
    
                    
                    $this->changeStatus($this->access_token, 'busy');
                    $this->setLog('Меняю токен');
                    sleep(1);
                    $this->access_token = $this->getToken();
                    $request_params['access_token'] = $this->access_token;

                    // Делаем запрос
                    $result = $this->vkapi('users.get', $request_params);
 
            
                    if (isset($result['response']['items'])) 
                        $error = false;
    
                } while($error == true);

                $this->setLot('Ошибка устранена');
            }

            $data = [];

            foreach($result['response'] as $value) 
            {

                // Ищем родственников
                if (isset($value['relatives'])) 
                {
                    foreach($value['relatives'] as $val) {
                        if(isset($request[$val['type']]))
                            if ($val['id'] > 0)
                                $data[] = $val['id'];
                    }
                } 


                // Ищем пары 
                if(isset($request['couples'])) 
                {
                    if (isset($value['relation_partner'])) {
                        $data[] = $value['relation_partner']['id'];
                    }
                } 


            }

            // Пишем id в конечный файл
            file_put_contents($this->file, implode("\n", $data) . "\n", FILE_APPEND);
        }
    }

    public function pause()
    {
        if ($this->i > 1) { sleep(1); $this->i =0; }
        $this->i = $this->i + 1;
    }

}

$class = new GetRelatives($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

$class->changeStatus($class->access_token, 'free');

$class->setPercent(100);

die; 