<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';


class GetActivityGroups extends Exec
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

        $this->setLog('Старт');

        // Открываем файл со списком групп которые будем парсить
        $data = [];
        $res = $this->checkRN();
        $groups = explode($res, $this->getData());

        /*
        $groups = array_chunk($groups, 24);


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

        $groups = $data;
*/
        $allCount = count($groups);

        $this->setLog('Получил все группы: ' . $allCount);


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
                $this->setLog('Стартуем сразу с группы '. $group);
            }

            $this->setLastId($group);

            $this->setLog('Group: '.$group);

            // Высчитываем процент
            $percent = round($iter / $allCount * 100);
            if ($percent < 1) $percent = 1; elseif($percent > 99) $percent = 99;
            $this->setPercent($percent);


            $request_params['owner_id'] = '-' . $group;


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

            if (isset($request['authors']))  {
                $authors = array_pop($posts);
                $authors = array_diff($authors, [null, '-'.$group]);
            }

            // Собираем лайки
            if (isset($request['likes']))
                $likes = $this->getLikes($posts, $group);


            // Собираем комментарии
            if (isset($request['comments']))
                $comments = $this->getComments($posts, $group);


            // Собираем участников обсуждений
            if (isset($request['topics']))
                $topics = $this->getTopics($group);

            $data = array_merge($likes, $comments, $topics, $authors);

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

        $this->setLog('Собираю лайки');
        $posts = array_chunk($posts, 25);
        $temp = [];

        foreach($posts as $value)
        {
            $count = count($value);
            $ids = implode(',', $value);

            $request_params = array(
                'v'            => '5.131',
                'owner_id'     => '-'.$group,
                'offset'       => 0,
                'count'        => $count,
                'ids'          => $ids,
                'access_token' => $this->access_token,
            );

            $this->pause();
            $this->setLog('Собираю лайки с поста '.$value);

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
                $this->setLog('Собираю лайки c отдельного поста');
                $data = $this->getLikesFromPost($repeatParse, $likesCount, $group);
                $temp = array_merge($temp, $data);
            }
        }

        $this->setLog('Собрал '. count($temp) . ' лайков');
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
            $owner_id = '-' . $group;
            $item_id = $repeatParse[$i];
            $count = $likesCount[$i];
            $offset = 0;

            // Cобираем с каждого поста по 25 000 лайков за раз
            do {

                $this->pause();


                $request_params = array(
                    'v'            => '5.131',
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
                        $request_params['access_token'] = $this->access_token;
                        $this->setLog('Сменил токен');

                        // Запрашиваем лайки (макс может вернуть 25 000 за раз)
                        $result = $this->vkapi('execute.getLikes', $request_params);

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
        $this->setLog('Собираю комментарии');
        foreach($posts as $value)
        {
            $count = count($value);
            $ids = implode(',', $value);

            $request_params = array(
                'v'            => '5.131',
                'owner_id'     => '-'.$group,
                'offset'       => 0,
                'count'        => $count,
                'ids'          => $ids,
                'access_token' => $this->access_token,
            );

            $this->pause();
            $this->setLog('Собираю комментарии с поста ');

            // Запрашиваем лайки (макс может вернуть 25 000 за раз)
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
                    $this->access_token = $this->getToken();
                    $request_params['access_token'] = $this->access_token;
                    $this->setLog('Сменил токен');

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
        $this->setLog('Собираю комментарии с отельного поста');
        // Проходим по всем постам где > 1000 лайков
        for($i = 0; $i < count($repeatParse); $i++)
        {
            $owner_id = '-' . $group;
            $post_id = $repeatParse[$i];
            $count = $commentsCount[$i];
            $offset = 0;
            $this->setLog('Итерация '.$i);
            // Cобираем с каждого поста по 25 000 лайков за раз
            do {

                $this->pause();


                $request_params = array(
                    'v'            => '5.131',
                    'count'        => $count,
                    'offset'       => $offset,
                    'access_token' => $this->access_token,
                    'post_id'      => $post_id,
                    'owner_id'     => $owner_id,
                );


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
                        $request_params['access_token'] = $this->access_token;
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



    public function getTopics($group)
    {
        // Получаем id топиков (до 2500 за раз)
        $request_params = array(
            'v'            => '5.131',
            'offset'       => 0,
            'access_token' => $this->access_token,
            'group'        => $group,
        );


        $result = $this->vkapi('execute.getTopics', $request_params);

        $this->pause();
        $this->setLog('Собираю обсуждения');
        // Обрабатываем ошибки
        if (!isset($result['response']))
        {
            $this->setLog('Возникла ошибка при попытке собрать топики. Group: '. $group);
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
                } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result). "\n\n");

                $this->pause();
                $this->access_token = $this->getToken();
                $request_params['access_token'] = $this->access_token;
                $this->setLog('Сменил токен');

                $result = $this->vkapi('execute.getTopics', $request_params);

                if (isset($result['response'])) $error = false;

            } while($error == true);

            $this->setLog('Ошибка устранена');
        }

        $comments = array_pop($result['response']);
        $topics = array_pop($result['response']);

        $count = count($topics);
        $data = [];

        // Проходим по каждому топику и собираем всех участников
        for($i = 0; $i < $count; $i++)
        {
            if ($comments[$i] < 2) continue;
            $this->setLog('Собираю обсуждения в отдельном топике');
            $result = $this->getTopicComments($group, $topics[$i], $comments[$i]);

            $data = array_merge($data, $result);
        }

        // Убираем сообщения от группы
        $temp = [];
        foreach($data as $value){
            if ($value > 0) $temp[] = $value;
        }
        $data = $temp;

        return $data;
    }

    public function getTopicComments($group, $topicId, $countComments)
    {
        $offset = 0;
        $data = [];

        do {

            if ($countComments > 2500) $count = 2500 + $offset; else $count = $countComments;


            // Получаем id топиков (до 2500 за раз)
            $request_params = array(
                'v'            => '5.131',
                'offset'       => $offset,
                'access_token' => $this->access_token,
                'group_id'     => $group,
                'topic_id'     => $topicId,
                'topicComments'     => $count,
            );


            // Получаем участников каждого топика (за раз не более 2500)
            $result = $this->vkapi('execute.getTopicComments', $request_params);

            $this->pause();

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при попытке собрать участников топика. Topic: '. $topicId.'; Offset: ' . $offset);
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
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result). "\n\n");

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $request_params['access_token'] = $this->access_token;
                    $this->setLog('Сменил токен');

                    // Получаем участников каждого топика (за раз не более 2500)
                    $result = $this->vkapi('execute.getTopicComments', $request_params);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            $offset += 2500;

            $data = array_merge($data, $result['response']);

        }while($offset < $countComments);

        return $data;
    }

}

$class = new GetActivityGroups($id);

$request = $class->getRequest($id);

$data = $class->handler($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);

die;
////////////////////////////////////////////////////////////////////////////////////////////////



class GetActivityGroups extends Exec
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

    public function vkapi($method, $params)
    {
        $params = http_build_query($params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/'. $method .'?' . $params), true);

        if (isset($result['response']))
            return $result['response'];
    }


    public function parse($request)
    {

        $res = $this->checkRN();
        $groups = explode($res, $this->getData());

        $this->countGroups = count($groups);

        $data      = array();
        $errors    = array();
        $temp      = array();
        $likes = [];
        $comments = [];
        $authors   = array();
        $topicsLikes = array();
        $topicsMsg = array();
        $topicsComments = array();
        $marketComments = array();
        $percent = 0;

        // Значения времени по-умолчанию
        $time_min = 0;
        $time_max = time();

        // Принимаем дату и переводим в unix-формат
        if (strtotime($request['time_min']) > 0) $time_min = strtotime($request['time_min']);
        if ((strtotime($request['time_max']) > 0) and $request['time_max'] !== date('Y-m-d')) $time_max = strtotime($request['time_max']);

        $nPosts = $request['nPosts'];

        $currentPost = 0;
        $allCount = 1;

        if (isset($request['market']) or isset($request['market_comments'])) $allCount++;
        if (isset($request['topics'])) $allCount++;

    // Если стоит галочка лайки, или комменты, или авторы постов
    if (isset($request['likes']) or isset($request['comments']) or isset($request['author']))
    {

        // Сперва собираем N постов
        foreach($groups as $group)
        {

            if ($nPosts <= 100) $countPosts = $nPosts; else $countPosts = 100;

            $request_params = array(
                'v'            => '5.126',
                'owner_id'     => '-'.$group,
                'offset'       => 0,
                'count'        => $countPosts,
                'access_token' => $this->access_token
            );

            // Собираем посты и собираем в них активность
            do
            {
                sleep(1);
                $posts = $this->vkapi('wall.get', $request_params);

                if (empty($posts['items']) or !$posts) break;

                $request_params['offset'] += 100;
                if ($request['nPosts'] > $posts['count']) $nPosts = $posts['count'];

                // В цикле смотрим все посты и сверяем с условиями
                foreach ($posts['items'] as $post)
                {
                    // Вычисляем процент выполнения задачи
                    $percent = round((++$currentPost / $nPosts) * 100) / $this->work['source_count'];
                    $percent = round($percent / $allCount);
                    $this->setPercent($percent);

                    // Фильтр по дате поста
                    if (!($post['date'] >= $time_min and $post['date'] <= $time_max)) continue;

                    // Если стоит галочка "не учитывать закрепленный пост", проходим мимо
                    if (isset($request['pinned']) and isset($post['is_pinned'])) continue;

                    // Авторы постов
                    if (isset($request['author']))
                    {
                        if (isset($post['signer_id']))
                            $authors[] = $post['signer_id'];

                        if (isset($post['from_id']) and $post['from_id'] > 0)
                            $authors[] = $post['from_id'];
                    }

                    // Собираем лайки
                    if (isset($request['likes']))
                    {
                        $owner_id = $post['owner_id'];
                        $item_id = $post['id'];
                        $count = $post['likes']['count'];
                        $offset = 0;

                        do{
                            $this->pause();

                            // Запрашиваем лайки (макс может вернуть 25 000 за раз)
                            $result = json_decode(file_get_contents('https://api.vk.com/method/execute.getLikes?item_id='.$item_id.'&owner_id='.$owner_id.'&count='.$count.'&offset='.$offset.'&v=5.131&access_token='.$this->access_token), true);

                            $offset += 25000;

                            // Сохраняем лайки
                            if(isset($result['response']))
                            {
                                foreach($result['response'] as $value)
                                {
                                    if ($value['items']){
                                        $likes = array_merge($likes, $value['items']);
                                    }
                                }
                            } else {

                                $this->setError(' no likes ');
                                $this->getToken();

                            }

                        }while($offset <= $count);
                    }

                    // Собираем комментарии
                    if (isset($request['comments']))
                    {
                        $owner_id = $post['owner_id'];
                        $post_id = $post['id'];
                        $count = $post['comments']['count'];

                        $offset = 0;

                        do{
                            $this->pause();

                            // Запрашиваем лайки (макс может вернуть 2500 за раз)
                            $result = json_decode(file_get_contents('https://api.vk.com/method/execute.getComments?post_id='.$post_id.'&owner_id='.$owner_id.'&count='.$count.'&offset='.$offset.'&v=5.131&access_token='.$this->access_token), true);

                            $offset += 2500;

                            // Сохраняем лайки
                            if(isset($result['response']))
                            {
                                foreach($result['response'] as $value)
                                {
                                    if ($value['items'])
                                    foreach($value['items'] as $item)
                                    {
                                        // Если комментарий не от сообщества, сохраняем
                                        if($item['from_id'] > 0)
                                            $comments[] = $item['from_id'];


                                        // Комментарии > комментарии
                                        if ($item['thread']['count'] > 0 and isset($request['thread_comments']))
                                        {
                                            $request_params2 = $request_params;
                                            $request_params2['offset'] = 0;
                                            $request_params2['comment_id'] = $item['id'];

                                            do {
                                                $this->pause();

                                                $result2 = $this->vkapi('wall.getComments', $request_params2);
                                                $request_params2['offset'] = $request_params2['offset'] + 100;

                                                foreach ($result2['items'] as $comment2)
                                                if ($comment2['from_id'] > 0)
                                                    $comments[] = $comment2['from_id'];

                                            } while($request_params2['offset'] < $result2['count']);
                                        }
                                    }
                                }
                            } else {
                                $this->setError(' error n 505 no comments ');
                                $this->getToken();
                            }

                        } while($offset <= $count);
                    }
                }
            } while($request_params['offset'] < $nPosts);

            $data = array_merge($likes, $comments, $authors);
            $temp = array();

            // Фильтр "от" и "до" активностей
            if (is_numeric($request['ot']) or is_numeric($request['do']))
            {
                $data = array_count_values($data);
                //arsort($data);

                if (is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot'] and $value <= $request['do']) $temp[] = $key;
                    }
                } else if (is_numeric($request['ot']) and !is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot']) $temp[] = $key;
                    }
                } else if (!is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value <= $request['do']) $temp[] = $key;
                    }
                }
                $data = $temp;
                unset($temp);
            } else {
                $data = array_unique($data);
            }

            if (count($data) > 0) {
                $data = implode("\n", $data);
                file_put_contents($this->file, $data . "\n", FILE_APPEND);
            }

            $likes = [];
            $comments = [];
            $authors = [];
            $data = [];

        }
    }
        // Собираем обсуждения
        if (isset($request['topics']))
        {
            // Вычисляем процент выполнения задачи
            $percent = $percent + 10;
            $this->setPercent($percent);

            foreach($groups as $group)
            {
                $request_params = array(
                    'v'            => '5.126',
                    'group_id'     => $group,
                    'count'       => 100,
                    'offset'      => 0,
                    'access_token' => $this->access_token
                );

                // Парсим 100 топиков
                do {
                    $result = $this->vkapi('board.getTopics', $request_params);
                    $request_params['offset'] = $request_params['offset'] + 100;

                    // Проходим по кадому топику
                    if ($result)
                    foreach ($result['items'] as $topic)
                    {
                        $request_params2 = array(
                            'v'            => '5.126',
                            'group_id'     => $group,
                            'topic_id'     => $topic['id'],
                            'count'        => 100,
                            'offset'       => 0,
                            'access_token' => $this->access_token
                        );

                        // Парсим 100 сообщений
                        do {
                            $result2 = $this->vkapi('board.getComments', $request_params2);
                            $request_params2['offset'] = $request_params2['offset'] + 100;

                            if ($result2['items'])
                                foreach($result2['items'] as $msg)
                                    if ($msg['from_id'] > 0)
                                        if ($msg['date'] >= $time_min and $msg['date'] <= $time_max)
                                            $topicsMsg[] = $msg['from_id'];

                        $this->pause();

                        } while ($request_params2['offset'] <= $result2['count']);

                        $this->pause();
                    }

                $this->pause();

                } while ($request_params['offset'] <= $result['count']);
            }

             // Вычисляем процент выполнения задачи
             $percent = $percent + 14;
             $this->setPercent($percent);

             $data = [];
             $data = array_merge( $topicsMsg, $topicsLikes);
            $temp = array();

            // Фильтр "от" и "до" активностей
            if (is_numeric($request['ot']) or is_numeric($request['do']))
            {
                $data = array_count_values($data);
                //arsort($data);

                if (is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot'] and $value <= $request['do']) $temp[] = $key;
                    }
                } else if (is_numeric($request['ot']) and !is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot']) $temp[] = $key;
                    }
                } else if (!is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value <= $request['do']) $temp[] = $key;
                    }
                }
                $data = $temp;
                unset($temp);
            } else {
                $data = array_unique($data);
            }

            if (count($data) > 0) {
                $data = implode("\n", $data);
                file_put_contents($this->file, $data. "\n", FILE_APPEND);
            }

            $topicsMsg = [];
             $topicsLikes = [];


        } else {
            // Вычисляем процент выполнения задачи
            $percent = $percent + 24;
            $this->setPercent($percent);
        }


        // Собираем товары
        if (isset($request['market']) or isset($request['market_comments']))
        {
            // Вычисляем процент выполнения задачи
            $percent = $percent + 10;
            $this->setPercent($percent);

            foreach($groups as $group)
            {
                $request_params = array(
                    'v'            => '5.126',
                    'owner_id'     => '-' . $group,
                    'count'       => 200,
                    'offset'      => 0,
                    'access_token' => $this->access_token
                );

                // Парсим 200 товаров
                do {
                    $result = $this->vkapi('market.search', $request_params);
                    if ($result == null) break;
                    $request_params['offset'] = $request_params['offset'] + 200;

                    // Проходим по кадому товару
                    if ($result)
                    foreach ($result['items'] as $market)
                    {
                        $request_params2 = array(
                            'v'            => '5.126',
                            'owner_id'     => '-' . $group,
                            'item_id'      => $market['id'],
                            'type'         => 'market',
                            'count'        => 100,
                            'filte'        => 'likes',
                            'offset'       => 0,
                            'access_token' => $this->access_token
                        );

                        if (isset($request['market']))
                        // Парсим 100 лайков
                        do {
                            $result2 = $this->vkapi('likes.getList', $request_params2);
                            $request_params2['offset'] = $request_params2['offset'] + 100;

                            if ($result2['items'])
                                foreach($result2['items'] as $like_market)
                                    //if ($msg['from_id'] > 0)
                                        //if ($msg['date'] >= $time_min and $msg['date'] <= $time_max)
                                            $topicsLikes[] = $like_market;

                            $this->pause();

                        } while ($request_params2['offset'] <= $result2['count']);

                        $this->pause();

                        if (isset($request['market_comments']))
                        {
                            $request_params3 = $request_params2;
                            unset($request_params3['filte']);
                            unset($request_params3['type']);
                            $request_params3['sort'] = 'desc';
                            $request_params3['offset'] = 0;

                            // Парсим 100 комментариев
                            do {
                                $result3 = $this->vkapi('market.getComments', $request_params3);

                                $request_params3['offset'] = $request_params3['offset'] + 100;
                                if ($result3['count'] > 0)
                                    foreach($result3['items'] as $comment_market)
                                        if ($comment_market['from_id'] > 0)
                                            if ($comment_market['date'] >= $time_min and $comment_market['date'] <= $time_max)
                                                $marketComments[] = $comment_market['from_id'];

                                $this->pause();

                            } while ($request_params3['offset'] <= $result3['count']);
                        }

                        $this->pause();
                    }

                $this->pause();

                } while ($request_params['offset'] <= $result['count']);
            }

            // Вычисляем процент выполнения задачи
            $percent = $percent + 14;
            $this->setPercent($percent);

            $data = [];
            $data = array_merge( $marketComments, $topicsLikes);
            $temp = array();

            // Фильтр "от" и "до" активностей
            if (is_numeric($request['ot']) or is_numeric($request['do']))
            {
                $data = array_count_values($data);
                //arsort($data);

                if (is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot'] and $value <= $request['do']) $temp[] = $key;
                    }
                } else if (is_numeric($request['ot']) and !is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value >= $request['ot']) $temp[] = $key;
                    }
                } else if (!is_numeric($request['ot']) and is_numeric($request['do'])) {
                    foreach ($data as $key => $value) {
                        if ($value <= $request['do']) $temp[] = $key;
                    }
                }
                $data = $temp;
                unset($temp);
            } else {
                $data = array_unique($data);
            }

            if (count($data) > 0) {
                $data = implode("\n", $data);
                file_put_contents($this->file, $data. "\n", FILE_APPEND);
            }

            $marketComments = [];

        } else {
            // Вычисляем процент выполнения задачи
            $percent = $percent + 24;
            $this->setPercent($percent);
        }

        if (!file_exists($this->file)) {
            file_put_contents($this->file, '');
        }

    }

}

$class = new GetActivityGroups($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);

die;
