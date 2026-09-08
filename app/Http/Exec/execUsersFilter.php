<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';

class UsersFilter extends Exec
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

        $keywords = explode(",", $request['keywords']);
        $keywords = array_diff($keywords, array('', ' '));

        $res = $this->checkRN();
        $users = explode($res, $this->getData());

        // Высчитываем на сколько частей разбить массив
        $count = count($users);
        $streams = $this->work['streams'];

        $users = array_chunk($users, ceil($count / $streams));
        $users = $users[$this->stream['stream_id']];

        function calculate_age($birthday) {
            $birthday_timestamp = strtotime($birthday);
            if(!$birthday_timestamp) return 0;
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
                }
            return $age;
        }

        $sp = array();
        if (isset($request['relation1']) or isset($request['relation2']) or isset($request['relation3']) or isset($request['relation4']) or isset($request['relation5']) or isset($request['relation6']) or isset($request['relation7']) or isset($request['relation8'])){
            for($i = 1; $i < 9; $i++) {
                if (isset($request['relation'.$i]))
                $sp[] = $request['relation'.$i];
            }
        $sp = array_diff($sp, array(null));
        }

        $friend_status = [];
        if (isset($request['friend_status0']))
            $friend_status[] = 0;

        if (isset($request['friend_status1']))
            $friend_status[] = 1;

        if (isset($request['friend_status2']))
        $friend_status[] = 2;

        if (isset($request['friend_status3']))
            $friend_status[] = 3;

        if (!empty($friend_status) or isset($request['n_common'])) {
            $this->access_token = $this->getMyToken();
        }

        $condition = array();

        $users = array_chunk($users, 3000);

        $i = 0;
        $iter = 0;
        $count = count($users);

        $this->setLog('Начинаю парсинг');

        $continue = false;
        $lastId = $this->getLastId();
        if ($lastId) $continue = true;

        // Передаваемые параметры
        $request_params = array(
        'v'            => '5.131',
        'fields'       => 'sex,last_seen,has_photo,status,can_write_private_message,followers_count,is_closed,common_count,friend_status,bdate,online,relation',
        'access_token' => $this->access_token
    );

        foreach ($users as $key => $user)
        {
            $iter++;

            // Если указан последяя итерация, начинаем с нее
            if($continue) {
                if ($key != $lastId) continue;

                $continue = false;
                $this->setLog('Стартуем сразу с итерации '. $iter);
            }

            // Устанавливаем последнюю итерацию
            $this->setLastId($iter);

            // Формируем список id из 10 тыс человек
            $user = array_chunk($user, 1000);

            $res = '';
            foreach($user as $val){
                $ids = implode(',', $val);
                $res .= $ids . ':';
            }
            $res = trim($res, ':');

            // Высчитываем процент
            $percent =  round(($iter / $count) * 100);
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            // Отправляем запрос
            $request_params['user_ids'] = $res;
            $result = $this->vkapi('execute.usersGet', $request_params);

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
                    $request_params['access_token'] = $this->getToken();

                    // Отправляем запрос
                    $result = $this->vkapi('execute.usersGet', $request_params);

                    if (isset($result['response']))
                        $error = false;

                } while($error == true);

                $this->setLot('Ошибка устранена');
            }
            $i = 0;
            $n = 0;


            foreach($result['response'] as $value)
            {

                // Исключить заблокированных
                if (isset($request['dogs'])) {
                    if (isset($value['deactivated'])) continue;
                }


                // Фильтр по полу
                if ($request['sex'] != '0') {
                    if ($value['sex'] != $request['sex']) continue;
                }

                // Фильтр по семейному положению
                if (!empty($sp))
                if (isset($value['relation'])) {
                    if (!in_array($value['relation'], $sp)) continue;
                } else continue;

                // Статус дружбы с пользователем
                if (isset($value['friend_status']) && !empty($friend_status)) {
                    if (!in_array($value['friend_status'], $friend_status)) continue;
                }

                // Онлайн или нет
                if  ($value['online'] != '1' && $request['online'] == '1')  {
                    continue;
                } elseif  ($value['online'] != '0' && $request['online'] == '2')  {
                    continue;
                }

                // Фильтр по наличию аватарки
                if ($request['avatar'] != 'no') {
                    if ($value['has_photo'] != $request['avatar']) continue;
                }

                // Личка
                if ($request['ls'] != 'no') {
                    if ($value['can_write_private_message'] != $request['ls']) continue;
                }

                // Профиль открытый или закрытый
                if  ($value['is_closed'] != false && $request['profile'] == '1')  {
                    continue;
                } elseif  ($value['is_closed'] != true && $request['profile'] == '2')  {
                    continue;
                }


                // Фильтр по возрасту
                if ($request['age_ot'] != 0 or $request['age_do'] != 0)
                {
                    if (isset($value['bdate']))
                    {
                        ($request['age_ot'] != 0) ? $min = $request['age_ot'] : $min = 0;
                        ($request['age_do']!= 0) ? $max = $request['age_do'] : $max = 999;
                        $age = calculate_age( $value['bdate']);
                        if ($age == 0) continue;

                        if ($age < $min || $age > $max) continue;

                    } else continue;
                }


                // Общие друзья
                if (isset($request['n_common']) && $value['common_count'] < $request['n_common'])
                {
                    continue;
                }

                // Количество подписчиков
                if (isset($value['followers_count']) && ($request['n_followers_ot']))
                {
                    if ($value['followers_count'] < (int)$request['n_followers_ot']) {
                        continue;
                    }
                }

                // Количество подписчиков
                if (isset($value['followers_count']) && ($request['n_followers_do']))
                {
                    if ($value['followers_count'] > (int)$request['n_followers_do']) {
                        continue;
                    }
                }

                // Слова в статусе
                if (count($keywords) > 0)
                {
                    $finded = false;
                    if (isset($value['status']))
                    foreach($keywords as $key)
                        {
                            $k1 = mb_strtolower($value['status'], 'UTF-8');
                            $k2 = mb_strtolower($key, 'UTF-8');
                            $k1 = trim($k1);
                            $k2 = trim($k2);
                            $pos = stripos($k1, $k2);

                            if ($pos !== false) $finded = true;
                        }

                    if (!$finded) continue;

                }

                // Время последнего посещения
                if ($request['n_day_online'] && isset($value['last_seen']))
                {
                    $dayAgo = time() - 86400 * $request['n_day_online'];
                    $hasOnline = $value['last_seen']['time'];

                    if ($hasOnline < $dayAgo) {
                        continue;
                    }
                }


                // Пишем id в конечный файл
                file_put_contents($this->file, $value['id'] . "\n", FILE_APPEND);
            }

        }


    }

    public function extFilter($users, $request)
    {
        $i = 0;
        $loading = 0;
        $count = count($users);
        $data = $users;

        $subscriptions_min = (isset($request['subscriptions_min'])) ? $request['subscriptions_min'] : 0;
        $subscriptions_max = (isset($request['subscriptions_max'])) ? $request['subscriptions_max'] : 9999;
/*
        $videos_min = ($request['videos_min'] !== null) ? $request['videos_min'] : 0;
        $videos_max = ($request['videos_max'] !== null) ? $request['videos_max'] : 9999;

        $audios_min = ($request['audios_min'] !== null) ? $request['audios_min'] : 0;
        $audios_max = ($request['audios_max'] !== null) ? $request['audios_max'] : 9999;
*/
        $photos_min = (isset($request['photos_min'])) ? $request['photos_min'] : 0;
        $photos_max = (isset($request['photos_max'])) ? $request['photos_max'] : 9999;

        $friends_min = (isset($request['friends_min'])) ? $request['friends_min'] : 0;
        $friends_max = (isset($request['friends_max'])) ? $request['friends_max'] : 9999;

        $params = array(
            'subscriptions_min' => $subscriptions_min,
            'subscriptions_max' => $subscriptions_max,
            'photos_min' => $photos_min,
            'photos_max' => $photos_max,
            'friends_min' => $friends_min,
            'friends_max' => $friends_max,
        );

        $extFilter = false;
        foreach($params as $value){
            if ($value !== 0 && $value !== 9999)
                $extFilter = true;
        }

        if ($extFilter == false) return $users;

        foreach ($users as $user)
        {
            $percent = (++$loading / $count) * 100;
            if ($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $url = "https://api.vk.ru/method/users.get?user_id=".$user.",&fields=counters&count=300&v=5.89&access_token=".$this->access_token;
            $result = json_decode(file_get_contents($url),true);
            $this->pause();
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

    public function pause()
    {
        if ($this->i > 1) { sleep(1); $this->i =0; }
        $this->i = $this->i + 1;
        /*
        $n = 3;
        $slp = $this->work['streams'];
        if ($this->work['streams'] > 1) { $n = 1; }

        if (++$this->i >= $n) { sleep($slp); $i = 0; } */
    }
}

$class = new UsersFilter($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setStatus();
$class->setPercent(100);
$class->setLog('Завершаю работу. Все ок ');

die;



