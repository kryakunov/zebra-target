<?php


namespace App\Http\Controllers;


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

//if (!isset($_GET['i'])) dd('Технические работы');

use App\mywork;
use App\stream;
use App\Http\Exec\Process;
use App\Functions;
use Illuminate\Http\Request;

class FilterController extends ExecController
{
    public $sourceFile;
    public $path;
    public $streams;
    public $workId;

    public function __construct()
    {
        $this->time = time();
        $this->type = 2;
        $this->execScriptName = 'execUsersFilter';
    }

    public function filter()
    {

        $work = $this->getWork();

		if ($work) {
			return view('filter', ['work' => $work]);
		}

		return view('filter');
	}

    public function extfilter() {
        return view('extendedfilter');
    }
/*
    public function extfilterStore(Request $request) {

        if (session('access') < time()) {
            return redirect()->back()->with('pay', 'Функция недоступна на бесплатном доступе :(')->withInput($request->input());;
        }

        $this->validate($request, [
            'users' => 'required',
        ]);


        $subscriptions_min = ($request->input('subscriptions_min') !== null) ? $request->input('subscriptions_min') : 0;
        $subscriptions_max = ($request->input('subscriptions_max') !== null) ? $request->input('subscriptions_max') : 9999;

        $videos_min = ($request->input('videos_min') !== null) ? $request->input('videos_min') : 0;
        $videos_max = ($request->input('videos_max') !== null) ? $request->input('videos_max') : 999999;

        $audios_min = ($request->input('audios_min') !== null) ? $request->input('audios_min') : 0;
        $audios_max = ($request->input('audios_max') !== null) ? $request->input('audios_max') : 999;

        $photos_min = ($request->input('photos_min') !== null) ? $request->input('photos_min') : 0;
        $photos_max = ($request->input('photos_max') !== null) ? $request->input('photos_max') : 999999;

        $friends_min = ($request->input('friends_min') !== null) ? $request->input('friends_min') : 0;
        $friends_max = ($request->input('friends_max') !== null) ? $request->input('friends_max') : 999999;

        $data = array(
            'subscriptions_min' => $subscriptions_min,
            'subscriptions_max' => $subscriptions_max,
            'videos_min' => $videos_min,
            'videos_max' => $videos_max,
            'audios_min' => $audios_min,
            'audios_max' => $audios_max,
            'photos_min' => $photos_min,
            'photos_max' => $photos_max,
            'friends_min' => $friends_min,
            'friends_max' => $friends_max,
        );
        $data = serialize($data);

        $time = time();

        $file = session('id') . '_' . $time . '.txt';
        $path = '../storage/app/filter/' . $file;

        $users = $request->get('users');
        $count = explode("\r\n", $users);
        $count = count($count);
        file_put_contents($path, $data);
        file_put_contents($path, $users, FILE_APPEND);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Фильтр пользователей';

       return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');

    } */


    public function filterStore(Request $request)
    {

        $this->parentId = $request->get('parentId');

        if (!($this->parentId)) {
            $this->validate($request, [
                'users' => 'required',
            ]);
        } else {

            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();

            // Для расшаренных задач
            if (!$work) {
                 $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
                 $this->parentId = null;
            }

            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();

            $request[$work->WorkType->type_desc] = file_get_contents($file);

        }

        $pos = strpos(substr($request['users'], 0, 15), "\r\n");

        if ($pos !== false) {
            $users = explode("\r\n", $request['users']);
        } else {
            $users = explode("\n", $request['users']);
        }

        // Если это моя задача и у меня бесплатный доступ, то обрезать результат
       // if ($work->vk_id == session('id'))
        if (!Functions::isFullAccess())
        {
            $users = array_slice($users, 0, 50);
        }

        $this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        $users = array_unique($users);

        $this->membersCount = count($users);
        $this->streams = ceil($this->membersCount / 100000);
        if ($this->streams == 0) $this->streams = 1;

        if(isset($request['friend_status0'])
            or isset($request['friend_status1'])
            or isset($request['friend_status2'])
            or isset($request['friend_status3'])
            or ($request['n_common'] !== null)) {
                $this->streams = 1;
            }


        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Фильтр пользователей';

        $request['users'] = implode("\n", $users);

        $this->setRequest($request->all());

        $data = $request['users'];

        // Сохраняем данные в файл
        file_put_contents($this->path, $data);

        // Создаем очередь
        $work = $this->createWork($name, $this->parentId);

        // Создаем потоки
        $this->createStream($this->streams);

       return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');

        die;
    }
       ////////////////////////////////////////////////////////////////
/*
        $keywords = explode(",", $request->keywords);
        $keywords = array_diff($keywords, ['', ' ']);

        $users = Functions::clearUserName($request->input('users'));
        $users = explode("\r\n", $users);
        $data = [];
        foreach($users as $user) {
            $data[] = (int)$user;
        }


        function calculate_age($birthday) {
            $birthday_timestamp = strtotime($birthday);
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
                }
            return $age;
        }

        $users = array_chunk($users, 300);

        $sp = [];
        for($i = 1; $i < 9; $i++) {
            $sp[] = $request->input('relation'.$i);
        }
        $sp = array_diff($sp, [null]);

        $friend_status = [];
        for($i = 0; $i < 5; $i++) {
            $friend_status[] = $request->input('friend_status'.$i);
        }
        $friend_status = array_diff($friend_status, [null]);

        $condition = [];

        $i = 0;


        foreach ($users as $user)
        {
            $ids = implode(",", $user);
            $url = "https://api.vk.ru/method/users.get?user_ids=".$ids.",&fields=sex,last_seen,has_photo,status,can_write_private_message,followers_count,is_closed,common_count,friend_status,bdate,online,relation&offset=0&count=300&v=5.89&access_token=".session('token');
            $result = json_decode(file_get_contents($url),true);
            if (++$i >= 2) { sleep(1); $i = 0; }
            if (!$result['response']) continue;

            foreach($result['response'] as $value)
            {

                if (isset($value['deactivated'])) continue;

                // Фильтр по полу
                if ($request->input('sex') !== '0') {
                    if ($value['sex'] == $request->input('sex')) $dataSex[] = $value['id'];
                }

                // Фильтр по семейному положению
                if (isset($value['relation']) && !empty($sp)) {
                    if (in_array($value['relation'], $sp)) $dataRelation[] = $value['id'];
                }


                // Статус дружбы с пользователем
                if (isset($value['friend_status']) && !empty($friend_status)) {
                    if (in_array($value['friend_status'], $friend_status)) $dataFriendStatus[] = $value['id'];
                }


                // Онлайн или нет
                if  ($value['online'] == '1' && $request->input('online') == '1')  {
                    $dataOnline[] = $value['id'];
                } elseif  ($value['online'] == '0' && $request->input('online') == '2')  {
                    $dataOnline[] = $value['id'];
                }

                // Фильтр по наличию аватарки
                if ($request->input('avatar') !== 'no') {
                    if ($value['has_photo'] == $request->input('avatar')) $dataAvatar[] = $value['id'];
                }

                // Личка
                if ($request->input('ls') !== 'no') {
                    if ($value['can_write_private_message'] == $request->input('ls')) $dataLs[] = $value['id'];
                }

                // Профиль открытый или закрытый
                if  ($value['is_closed'] === false && $request->input('profile') == '1')  {
                    $dataProfile[] = $value['id'];
                } elseif  ($value['is_closed'] === true && $request->input('profile') == '2')  {
                    $dataProfile[] = $value['id'];
                }


                // Фильтр по возрасту
                if (isset($value['bdate']))
                {
                    if ($request->input('age_ot') !== '0' && $request->input('age_do') !== '0') {

                        if ( substr_count($value['bdate'], '.') > 1  )
                        {
                            $age = calculate_age( $value['bdate']);
                            if ($age >= $request->input('age_ot') && $age <= $request->input('age_do')) {
                                $dataAge[] = $value['id'];
                                }
                        }

                    } elseif ($request->input('age_ot') !== '0' && $request->input('age_do') == '0') {

                        if ( substr_count($value['bdate'], '.') > 1  )
                        {
                            $age = calculate_age($value['bdate']);

                            if ($age >= $request->input('age_ot')) {
                                $dataAge[] = $value['id'];
                                }
                        }

                    } elseif ($request->input('age_ot') == '0' && $request->input('age_do') !== '0') {

                        if ( substr_count($value['bdate'], '.') > 1  )
                        {
                            $age = calculate_age( $value['bdate']);

                            if ($age <= $request->input('age_do')) {
                                $dataAge[] = $value['id'];
                                }
                        }
                    }
                }

                // Общие друзья
                if ($request['n_common'] && $value['common_count'] >= $request['n_common'])
                {
                    $dataCommonFriend[] = $value['id'];
                } elseif ($request['n_common']) {
                    $dataCommonFriend[] = '';
                }

                // Количество подписчиков
                if (isset($value['followers_count']) && ($request['n_followers_ot'] or $request['n_followers_do']))
                {
                    ($request['n_followers_ot']) ? $min = $request['n_followers_ot'] : $min = 0;
                    ($request['n_followers_do']) ? $max = $request['n_followers_do'] : $max = 9999999999;

                    if ($value['followers_count'] >= $min && $value['followers_count'] <= $max) {
                        $dataFollowersCount[] = $value['id'];
                    } else
                        $dataFollowersCount[] = '';
                }

                // Слова в статусе
                if (count($keywords) > 0)
                {
                    if (isset($value['status']))
                    foreach($keywords as $key)
                        {
                            $k1 = mb_strtolower($value['status'], 'UTF-8');
                            $k2 = mb_strtolower($key, 'UTF-8');
                            $k1 = trim($k1);
                            $k2 = trim($k2);
                            $pos = stripos($k1, $k2);
                            if ($pos !== false)   $dataStatus[] =  $value['id'];
                        }
                }

                // Время последнего посещения
                if ($request->input('n_day_online') && isset($value['last_seen']))
                {
                    $dayAgo = time() - 86400 * $request->input('n_day_online');
                    $hasOnline = $value['last_seen']['time'];

                    if ($hasOnline >= $dayAgo) {
                        $dataOnline[] = $value['id'];
                    }
                }


            }

        }

        if (!empty($dataSex)) $data = array_intersect($data, $dataSex);
        if (!empty($dataRelation)) $data = array_intersect($data, $dataRelation);
        if (!empty($dataOnline)) $data = array_intersect($data, $dataOnline);
        if (!empty($dataProfile)) $data = array_intersect($data, $dataProfile);
        if (!empty($dataAvatar)) $data = array_intersect($data, $dataAvatar);
        if (!empty($dataLs)) $data = array_intersect($data, $dataLs);
        if (!empty($dataFriendStatus)) $data = array_intersect($data, $dataFriendStatus);
        if (!empty($dataAge)) $data = array_intersect($data, $dataAge);
        if (!empty($dataCommonFriend)) $data = array_intersect($data, $dataCommonFriend);
        if (!empty($dataFollowersCount)) $data = array_intersect($data, $dataFollowersCount);
        if (!empty($dataOnline)) $data = array_intersect($data, $dataOnline);
        if (!empty($dataStatus)) $data = array_intersect($data, $dataStatus);


        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 50 значений из ' . count($data));
            $data = array_slice($data, 0, 50);
        }

		return view('filter', ['request' => $request->all(), 'data' => $data]);
	}
*/
}
