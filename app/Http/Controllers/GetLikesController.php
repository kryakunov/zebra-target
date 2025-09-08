<?php

namespace App\Http\Controllers;

use App\Functions;
use Illuminate\Http\Request;

class GetActivityController extends Controller
{
    public $access_token = '';
    public $i = 0;

    public function getactivity() {

        return view('getactivity');
    }


    public function getPosts($group, $offset = 0, $count = 100)
    {
        $posts = json_decode(file_get_contents('https://api.vk.ru/method/execute.wallget?count='.$count.'&offset='.$offset.'&owner_id=-'.$group.'&v=5.126&access_token='.$this->access_token));

        return $posts;
    }


    public function getactivityStore(Request $request)
    {

        $this->validate($request, [
            'groups' => 'required',
        ]);
if(session('id') !== 573204714) dd("Технические работы");
        // Очищаем список id
        $groups = explode("\r\n", $request->get('groups'));
        $groups = Functions::clearGroupName($groups);
        $groups = array_unique($groups);

        if (count($groups) > 100) {
            return redirect()->back()->with('error', 'Максимально 100 сообществ');
        }

        // Собираем только ID групп
        $groups = array_chunk($groups, 24);
        $data = [];
        foreach($groups as $group)
        {
            $count = count($group);
            $ids = implode(',', $group);

            // Получаем ID групп
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getGroupsId?ids='.$ids.'&count='.$count.'&v=5.131&access_token='.session('token')), true);

            if(!$result or !isset($result['response'])) continue;

            $data = array_merge($data, $result['response']);
        }

        $groups = $data;

        function vkapi($method, $params) {
            $params = http_build_query($params);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/'. $method .'?' . $params), true);

			if (isset($result['response']))
            	return $result['response'];
        }

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

        // Значения времени по-умолчанию
        $time_min = 0;
        $time_max = time();

    // Принимаем дату и переводим в unix-формат
    if (strtotime($request['time_min']) > 0) $time_min = strtotime($request['time_min']);
    if ((strtotime($request['time_max']) > 0) and $request['time_max'] !== date('Y-m-d')) $time_max = strtotime($request['time_max']);

    $nPosts = $request['nPosts'];

    // Если стоит галочка лайки, или комменты, или авторы постов
    if (isset($request['likes']) or isset($request['comments']) or isset($request['author']))
    {

        // Сперва собираем N постов
        foreach($groups as $group)
        {
            $postIds = [];

            if ($nPosts <= 100) $countPosts = $nPosts; else $countPosts = 100;

            $request_params = array(
                'v'            => '5.126',
                'owner_id'     => '-'.$group,
                'offset'       => 0,
                'count'        => $countPosts,
                'access_token' => session('token')
            );

            // Собираем посты и собираем в них активность
            do
            {
                sleep(1);
                $posts = vkapi('wall.get', $request_params);
                if (!$posts['items']) continue;
                $request_params['offset'] += 100;
                if ($request['nPosts'] > $posts['count']) $nPosts = $posts['count'];

                // В цикле смотрим все посты и сверяем с условиями
                foreach ($posts['items'] as $post)
                {
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
                            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getLikes?item_id='.$item_id.'&owner_id='.$owner_id.'&count='.$count.'&offset='.$offset.'&v=5.131&access_token='.session('token')), true);

                            $offset += 25000;

                            // Сохраняем лайки
                            if(isset($result['response']))
                            foreach($result['response'] as $value)
                            {
                                if ($value['items']){
                                    $likes = array_merge($likes, $value['items']);
                                }
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
                            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getComments?post_id='.$post_id.'&owner_id='.$owner_id.'&count='.$count.'&offset='.$offset.'&v=5.131&access_token='.session('token')), true);

                            $offset += 2500;

                            // Сохраняем лайки
                            if(isset($result['response']))
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

                                            $result2 = vkapi('wall.getComments', $request_params2);
                                            $request_params2['offset'] = $request_params2['offset'] + 100;

                                            foreach ($result2['items'] as $comment2)
                                            if ($comment2['from_id'] > 0)
                                                $comments[] = $comment2['from_id'];

                                        } while($request_params2['offset'] < $result2['count']);
                                    }
                                }
                            }

                        } while($offset <= $count);
                    }
                }
            } while($request_params['offset'] < $nPosts);
        }

    // Собираем обсуждения
    if (isset($request['topics']))
    {
        foreach($groups as $group)
        {
            $request_params = array(
                'v'            => '5.126',
                'group_id'     => $group,
                'count'       => 100,
                'offset'      => 0,
                'access_token' => session('token')
            );

            // Парсим 100 топиков
            do {
                $result = vkapi('board.getTopics', $request_params);
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
                        'access_token' => session('token')
                    );

                    // Парсим 100 сообщений
                    do {
                        $result2 = vkapi('board.getComments', $request_params2);
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
    }


    // Собираем товары
    if (isset($request['market']) or isset($request['market_comments']))
    {
        foreach($groups as $group)
        {
            $request_params = array(
                'v'            => '5.126',
                'owner_id'     => '-' . $group,
                'count'       => 200,
                'offset'      => 0,
                'access_token' => session('token')
            );

            // Парсим 200 товаров
            do {
                $result = vkapi('market.search', $request_params);
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
                        'access_token' => session('token')
                    );

                    if (isset($request['market']))
                    // Парсим 100 лайков
                    do {
                        $result2 = vkapi('likes.getList', $request_params2);
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
                            $result3 = vkapi('market.getComments', $request_params3);

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
    }

    $data = array_merge($likes, $comments, $authors, $topicsMsg, $topicsLikes, $marketComments);
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
    } else
        $data = array_unique($data);

        dd($data);

	return view('getactivity', ['data' => $data,  'request' => $request->all()]);

    }
}

public function pause()
{
    if (++$this->i >= 1)
    {
        $this->i = 0;
        sleep(1);
    }
}

}
