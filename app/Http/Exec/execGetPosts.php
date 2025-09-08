<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';


class GetPosts extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $iter = 0;
    public $checked = 0;
    public $allCount = 0;

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
    }
    
    public function parse($request)
    {

        $likes_min = ($request['likes_min']) ? $request['likes_min'] : 0;
        $likes_max = ($request['likes_max']) ? $request['likes_max'] : 9999999999999;

        $reposts_min = ($request['reposts_min']) ? $request['reposts_min'] : 0;
        $reposts_max = ($request['reposts_max']) ? $request['reposts_max'] : 9999999999999;

        $views_min = ($request['views_min']) ? $request['views_min'] : 0;
        $views_max = ($request['views_max']) ? $request['views_max'] : 9999999999999;

        $comments_min = ($request['comments_min']) ? $request['comments_min'] : 0;
        $comments_max = ($request['comments_max']) ? $request['comments_max'] : 9999999999999;

        // Значения времени по-умолчанию
        $time_min = 0;
        $time_max = time();
        
        // Принимаем дату и переводим в unix-формат
        if (strtotime($request['time_min']) > 0) $time_min = strtotime($request['time_min']); 
        if ((strtotime($request['time_max']) > 0) and $request['time_max'] !== date('Y-m-d')) $time_max = strtotime($request['time_max']);

                
        $request_params = array(
            'v'            => '5.131',
            'offset'       => 0,
            'count'        => 200,
            'access_token' => $this->access_token,
        );


        $res = $this->checkRN();
        $q = explode($res, $this->getData());

        // Удаляем пробелы и пустые элементы из массива
        $q = array_diff($q, array('',' '));
        $count = count($q);
        $allCount = count($q);
        $allCount = (1 / $allCount);
        $allPercent = 0;

        for($i = 0; $i < $count; $i++) {	
            $q[$i] = trim($q[$i]);
            $q = array_diff($q, array('',' '));
        }

        $data = [];
        $i = 0;
        $iter = 0;

        foreach($q as $value)
        {
            $iter++;
            $request_params['q'] = $value;
            $request_params['offset'] = 0;

            do {

                // Делаем запрос
                $result = $this->vkapi('newsfeed.search', $request_params);
                
                if (++$i <= 2) { $i = 0; sleep(1); }

                if(!isset($result['response']['items'])) {
                    $count = 1;
                    continue;
                }

                if (isset($result['response']['next_from']))
                    $request_params['start_from'] = $result['response']['next_from'];
            
                $request_params['offset'] += 200;
                $count = $result['response']['count'];
                $result = $result['response']['items'];

                // Вычисляем процент
                $percent = round(($request_params['offset'] / $count) * 100);
                $percent = round($percent * $allCount) + $allPercent;
                if($percent < 1) $percent = 1; elseif($percent > 99) $percent = 99;
                $this->setPercent($percent); 
                
                foreach($result as $value)
                {

                    // Фильтр по дате поста
                    if (!($value['date'] >= $time_min and $value['date'] <= $time_max)) continue;

                    $from_id = (string)$value['from_id'];

                    if ($request['when_posts'] == '1' and $from_id[0] == '-')
                        continue;

                    if ($request['when_posts'] == '2' and $from_id[0] !== '-')
                        continue;


                    if (!isset($value['views']['count'])) $value['views']['count'] = 0;

                    if ($value['likes']['count'] >= $likes_min && $value['likes']['count'] <= $likes_max) {
                        if ($value['reposts']['count'] >= $reposts_min && $value['reposts']['count'] <= $reposts_max) {
                            if ($value['views']['count'] >= $views_min && $value['views']['count'] <= $views_max) {
                                if ($value['comments']['count'] >= $comments_min && $value['comments']['count'] <= $comments_max) {

                                    if ($request['when_posts'] == '3')
                                    {
                                        if ($from_id[0] !== '-') 
                                           // $data[] = $value['from_id'];
                                            file_put_contents($this->file, $value['from_id'] . "\n", FILE_APPEND);
                                    } else {
                                        $str =  $value['from_id'] . '_' . $value['id'];
                                        file_put_contents($this->file, $str . "\n", FILE_APPEND);
                                      //  $data[] = $value['from_id'] . '_' . $value['id'];
                                    }
                                    
                                }
                            }
                        }
                    }
                }

            } while($request_params['offset'] < $count);

            $allPercent += $percent;
        }
    }

}

$class = new GetPosts($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);

die;