<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';



class GetActivityUser extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $limit = 0;

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
        $this->limit = $this->limit + 1;
    }



    public function handler($request)
    {

        // Открываем файл со списком групп которые будем парсить
        $res = $this->checkRN();
        $groups = explode($res, $this->getData());

      //  $groups = array_chunk($groups, 24);
        $data = [];


/*
        foreach($groups as $group)
        {


            $count = count($group);
            $ids = implode(',', $group);

            // Получаем ID групп
            $this->pause();
            $result = json_decode(file_get_contents('https://api.vk.com/method/execute.getGroupsId?ids='.$ids.'&count='.$count.'&v=5.131&access_token='.$this->access_token), true);

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при попытке собрать ID групп');
                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 5) {
                        $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($result['error'])) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено ');

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $this->setLog('Сменил токен');
                    $result = json_decode(file_get_contents('https://api.vk.com/method/execute.getGroupsId?ids='.$ids.'&count='.$count.'&v=5.131&access_token='.$this->access_token), true);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $data = array_merge($data, $result['response']);
        }

        $groups = $data; */

        $allCount = count($groups);

        // Активности по-умолчанию
        (is_numeric($request['ot'])) ? $min = $request['ot'] : $min = 0;

        // Значения времени по-умолчанию
        $time_min = 0;
        $time_max = time();

        // Принимаем дату и переводим в unix-формат
        if (strtotime($request['time_min']) > 0) $time_min = strtotime($request['time_min']);
        if ((strtotime($request['time_max']) > 0) and $request['time_max'] !== date('Y-m-d')) $time_max = strtotime($request['time_max']);

        // Кол-во постов со стены с которых будем брать активность
        $nPosts = $request['nPosts'];

        // Получаем список постов группы (макс лимит процедуры - до 2500 постов за раз)
        $request_params = array(
            'v'            => '5.131',
            'offset'       => 0,
            'count'        => $nPosts,
            'time_min'     => $time_min,
            'time_max'     => $time_max,
            'access_token' => $this->access_token,
            //  'is_pinned'    => 'delete', // Удаляем закрепленный пост
            // 'owner_id'     => '-'.$group,
        );

        if (isset($request['pinned']))
            $request_params['is_pinned'] = 'delete';

        if (isset($request['authors']))
            $request_params['authors'] = 'true';

        $iter = 0;

        $continue = false;
        $lastId = $this->getLastId();
        if ($lastId) $continue = true;

        // В цикле проходим по каждой группе
        foreach($groups as $group)
        {
            $iter++;

            if($continue) {
                if ($group != $lastId) continue;

                $continue = false;
                $this->setLog('Стартуем сразу с пользователя '. $group);
            }

            $this->setLastId($group);

            // Высчитываем процент
            $percent = round($iter / $allCount * 100);
            if ($percent < 1) $percent = 1; elseif($percent > 99) $percent = 99;
            $this->setPercent($percent);

            $request_params['owner_id'] = $group;

            $this->pause();

            // Отправляем запрос
            $posts = $this->vkapi('execute.wallGet', $request_params);


            // Обрабатываем ошибки
            if (!isset($posts['response']))
            {
                $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 5) {
                        $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($posts['error'])) {
                        $msg = $posts['error']['error_code'] . ' > ' . $posts['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($posts));

                    $this->pause();
                    $request_params['access_token'] = $this->getToken();
                    $this->setLog('Сменил токен');

                    // Отправляем запрос
                    $posts = $this->vkapi('execute.wallGet', $request_params);

                    if (isset($posts['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $posts = $posts['response'];

            $likes = [];
            $comments = [];
            $topics = [];
            $authors = [];


            // Собираем лайки
            if (isset($request['likes']))
                $likes = $this->getLikes($posts, $group);

            // Собираем комментарии
            if (isset($request['comments']))
                $comments = $this->getComments($posts, $group);


            $data = array_merge($likes, $comments);

            // Фильтр по активностям
            if ($min !== 0)
            {
                $data = array_count_values($data);
                arsort($data);

                $itog = [];
                foreach($data as $key => $value){
                    if ($value >= $min){
                        $itog[] = $key;
                    } else break;
                }
                $data = $itog;
            }

            // Оставляем только уникальные значения
            $data = array_unique($data);

            // Пишем в файл
            if(count($data) > 0) {
                $data = implode("\n", $data);
                file_put_contents($this->file, $data . "\n", FILE_APPEND);
            }

        }
    }

    public function getLikes($posts, $group)
    {

        $posts = array_chunk($posts, 25);
        $temp = [];

        foreach($posts as $value)
        {
            $count = count($value);
            $ids = implode(',', $value);

            $request_params = array(
                'v'            => '5.131',
                'owner_id'     => $group,
                'offset'       => 0,
                'count'        => $count,
                'ids'          => $ids,
                'access_token' => $this->access_token,
            );

            $this->pause();

            // Отправляем запрос
            $data = $this->vkapi('execute.getLikesFromPosts', $request_params);


            // Обрабатываем ошибки
            if (!isset($data['response']))
            {
                $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 5) {
                        $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($data['error'])) {
                        $msg = $data['error']['error_code'] . ' > ' . $data['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                    $this->pause();
                    $request_params['access_token'] = $this->getToken();
                    $this->setLog('Сменил токен');
                    // Отправляем запрос
                    $data = $this->vkapi('execute.getLikesFromPosts', $request_params);

                    if (isset($data['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $data = $data['response'];

            $likesCount = array_pop($data); // Здесь хранятся те посты где более 1000 лайков, их парсить будем отдельно
            $repeatParse = array_pop($data); // Здесь хранятся те посты где более 1000 лайков, их парсить будем отдельно

            // Объединяем элементы массива в единый массив
            foreach($data as $value){
                if (is_array($value))
                    $temp = array_merge($temp, $value);
            }

            // Собираем лайки с каждого отдельного поста
            if (count($repeatParse) > 0) {
                $data = $this->getLikesFromPost($repeatParse, $likesCount, $group);
                $temp = array_merge($temp, $data);
            }
        }

        return $temp;
        // Тут можно подсчитать кто сколько лайков оставил

        // А тут пишем результат в файл
    }

    public function getLikesFromPost($repeatParse, $likesCount, $group)
    {
        $data = [];

        // Проходим по всем постам где > 1000 лайков
        for($i = 0; $i < count($repeatParse); $i++)
        {
            $owner_id = $group;
            $item_id = $repeatParse[$i];
            $count = $likesCount[$i];

            // Cобираем с каждого поста по 25 000 лайков за раз
            do {

                $offset = 0;

                $this->pause();

                $request_params = array(
                    'v'            => '5.131',
                    'owner_id'     => $group,
                    'offset'       => $offset,
                    'count'        => $count,
                    'access_token' => $this->access_token,
                    'item_id'      => $item_id,
                    'owner_id'      => $owner_id,

                );

                // Запрашиваем лайки (макс может вернуть 25 000 за раз)
                $result = $this->vkapi('execute.getLikes', $request_params);

                $offset += 25000;

                // Обрабатываем ошибки
                if (!isset($result['response']))
                {
                    $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                    $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                    $error = true;
                    $i = 0;
                    do {
                        if (++$i > 5) {
                            $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                            die;
                        }

                        if (isset($result['error'])) {
                            $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                            $this->setLog('Ошибка: '. $msg);
                        } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                        $this->pause();
                        $this->access_token = $this->getToken();
                        $this->setLog('Сменил токен');

                        // Запрашиваем лайки (макс может вернуть 25 000 за раз)
                        $data = $this->vkapi('execute.getLikes', $request_params);

                        if (isset($result['response'])) $error = false;

                    } while($error == true);

                    $this->setLog('Ошибка устранена');
                }

                // Объединяем массив в одно целое
                foreach($result['response'] as $value){
                    if (is_array($value))
                        $data = array_merge($data, $value);
                }

            }while($offset <= $count);

        }

        return $data;

    }


    public function getComments($posts, $group)
    {
        $posts = array_chunk($posts, 25);
        $temp = [];

        foreach($posts as $value)
        {
            $count = count($value);
            $ids = implode(',', $value);

            $request_params = array(
                'v'            => '5.131',
                'owner_id'     => $group,
                'offset'       => 0,
                'count'        => $count,
                'ids'          => $ids,
                'access_token' => $this->access_token,
            );

            $this->pause();

            // Отправляем запрос
            $data = $this->vkapi('execute.getCommentsFromPosts', $request_params);


            // Обрабатываем ошибки
            if (!isset($data['response']))
            {
                $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 5) {
                        $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($data['error'])) {
                        $msg = $data['error']['error_code'] . ' > ' . $data['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                    $this->pause();
                    $request_params['access_token'] = $this->getToken();
                    $this->setLog('Сменил токен');

                    // Отправляем запрос
                    $data = $this->vkapi('execute.getCommentsFromPosts', $request_params);

                    if (isset($data['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $data = $data['response'];

            $commentsCount = array_pop($data); // Здесь хранятся те посты где более 1000 лайков, их парсить будем отдельно
            $repeatParse = array_pop($data); // Здесь хранятся те посты где более 1000 лайков, их парсить будем отдельно

            // Перебираем массив
            foreach($data as $value){
                foreach($value as $id)
                    if ($id > 0)
                        $temp[] = $id;
            }

            // Собираем комментарии с каждого отдельного поста
            if (count($repeatParse) > 0) {
                $data = $this->getCommentsFromPost($repeatParse, $commentsCount, $group);
                $temp = array_merge($temp, $data);
            }

        }

        return $temp;

        // Тут можно подсчитать кто сколько лайков оставил

        // А тут пишем результат в файл
    }


    public function getCommentsFromPost($repeatParse, $commentsCount, $group)
    {
        $temp = [];

        // Проходим по всем постам где > 1000 лайков
        for($i = 0; $i < count($repeatParse); $i++)
        {
            $owner_id = $group;
            $post_id = $repeatParse[$i];
            $count = $commentsCount[$i];

            // Cобираем с каждого поста по 25 000 лайков за раз
            do {

                $offset = 0;
                $this->pause();

               $request_params = array(
                    'v'            => '5.131',
                    'offset'       => $offset,
                    'count'        => $count,
                    'access_token' => $this->access_token,
                    'post_id'      => $post_id,
                    'owner_id'      => $owner_id,
                );

                // Запрашиваем комментарии (макс может вернуть 2 500 за раз)
                $data = $this->vkapi('execute.getComments', $request_params);


                $offset += 2500;

                // Обрабатываем ошибки
                if (!isset($data['response']))
                {
                    $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                    $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                    $error = true;
                    $i = 0;
                    do {
                        if (++$i > 5) {
                            $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                            die;
                        }

                        if (isset($data['error'])) {
                            $msg = $data['error']['error_code'] . ' > ' . $data['error']['error_msg'];
                            $this->setLog('Ошибка: '. $msg);
                        } else $this->setLog('Возникла ошибка, но error_msg не найдено');

                        $this->pause();
                        $this->access_token = $this->getToken();
                        $this->setLog('Сменил токен');

                        $data = $this->vkapi('execute.getComments', $request_params);

                        if (isset($data['response'])) $error = false;

                    } while($error == true);

                    $this->setLog('Ошибка устранена');
                }

                $data = $data['response'];

                // Перебираем массив
                foreach($data as $value){
                    foreach($value as $id)
                        if ($id > 0)
                            $temp[] = $id;
                }

            } while($offset < $count);
        }
        return $temp;
    }

}

$class = new GetActivityUser($id);

$request = $class->getRequest($id);

$data = $class->handler($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);

die;
