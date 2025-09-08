<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use Illuminate\Http\Request;
use App\Http\Exec\Process;


class TopFollowersController extends Controller
{

    public $access_token;

    public $request_params = array(
        'user_id'      => '',
        'v'            => '5.130',
        'count'        => 4,
        'filter'       => 'publics',
        'offset'       => 0,
    //   'extended'     => 1,
        'fields'       => 'members_count',
        'access_token' => 'vk1.a.h3x7x17VGlqvt078G2-RyBhA80SFhVVH7IYV1h0bgg2oWzl3-ElvqGqSVdHt4NqPKFs0wJF0kUG3LNSMF15GJAFwM0ody81IEVZ7FvAwawj6v3VWPhyP129Xx_tJakoxVXgEgHAXBDxKXnV10iGoOqOJHQ2MybTPUQvbPYDgF1JzVsVPAeBnWAI0xD4oteZXLt2E2Hl0eRJewEk-zHhs2w ◀vk1.a.h3x7x17VGlqvt078G2-RyBhA80SFhVVH7IYV1h0bgg2oWzl3-ElvqGqSVdHt4NqPKFs0wJF0kUG3LNSMF15GJAFwM0ody81IEVZ7FvAwawj6v3VWPhyP129Xx_tJakoxVXgEgHAXBDxKXnV10iGoOqOJHQ',
    );

    public function show()
    {
        return view('topfollowers');
    }

    public function getMembersGroup($group)
    {
        // Передаваемые параметры
        $request_params = array(
            'group_id'     => $group,
            'offset'       => 0,
            'count'        => '1000',
            'v'            => '5.131',
            'access_token' => session('token'),
        );
        $data = array();
        $i = 0;

        // Собираем участников сообщества
        do {
            // Делаем запрос к VK API
            $get_params = http_build_query($request_params);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?'. $get_params), true);
            $request_params['offset'] = $request_params['offset'] + 1000;
            if (array_key_exists('error', $result)) break;
            $countUsers = $result['response']['count'];

            $i++; if ($i > 1) { sleep(1); $i = 0; }

            $data = array_merge($data, $result['response']['items']);

        } while ($request_params['offset'] <= $result['response']['count']);

        return $data;
    }


    public function handler(Request $request)
    {

        $this->validate($request, [
            'groups' => 'required',
        ]);

        if (session('access') < time()) {
            return redirect()->back()->with('pay', 'Функция недоступна на бесплатном доступе :(')->withInput($request->input());;
        }


        $time = time();

        $file = session('id') . '_' . time() . '.txt';
        $path = '../storage/app/topfollowers/' . $file;

        $groups = explode("\r\n", $request->get('groups'));
        $count = count($groups);

        ($request->top) ? $top = $request->top : $top = 5;

        $groups = Functions::clearGroupName($groups);

        $ids = [];
        foreach ($groups as $group) {
            $group = Functions::getGroupId($group);
            if(!$group) continue;
            $ids[] = $group;
        }

        sleep(1);
        $ids = implode(",", $ids);
        $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?v=5.126&fields=members_count,type&group_ids='.$ids.'&access_token='.session('token')), true);

        $membersCount = 0;
        $groups = [];

        if (isset($result['response']))
        foreach ($result['response'] as $value){
            if ($value['type'] !== 'page') continue;
            $membersCount += $value['members_count'];
            $groups[] = $value['id'];
        }

        if($membersCount > 30000) {
            return redirect()->back()->with('error', 'Суммарное количество участников не должно превышать 30000')->withInput();
        }

        $groups = implode("\r\n", $groups);


        file_put_contents($path, $groups);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Топ читатели';

        $work = mywork::create([
            'vk_id' => session('id'),
            'status' => 0,
            'percent' => 0,
            'name' => $name,
            'type' => 'topfollowers',
            'date' => $time,
            'count' => $count,
            'pid' => 0,
        ]);


        $command = 'php ../app/Http/Exec/execTopFollowers.php '.escapeshellarg($work['id']).' '.escapeshellarg($top);
    //exec('php ../app/Http/Exec/execTopFollowers.php '.escapeshellarg($work['id']).' '.escapeshellarg($top).' > loosoo.txt'); die;
       $process = new Process($command);
       $pid = $process->getPid();

       mywork::where('id', '=', $work['id'])->update(['pid' => $pid]);

       return redirect()->route('profile')->with('success', 'Задача успешно добавлена в работу');


       ////////////////////////////////////////////////////////////////

       die;

        return view('topfollowers', ['data' => $data, 'request' => $request->all()]);

    die;


		$groups = array();
		foreach($result['response'] as $value) {
			if ($value['type'] !== 'page') continue;
			$groups[] = $value['id'];
			$count = $count + $value['members_count'];
		}

		// Проходим по сообществам
		if ($groups)
		foreach($groups as $group)
		{
			// Передаваемые параметры



			// Собираем участников сообщества
			do {

				// Делаем запрос к VK API
				$get_params = http_build_query($request_params);
				$result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?'. $get_params), true);
				$request_params['offset'] = $request_params['offset'] + 1000;
				if (array_key_exists('error', $result)) break;
				$countUsers = $result['response']['count'];

					// Парсим ТОП читателей
					if ($result['response']['items'])
					foreach($result['response']['items'] as $user)
					{
						$loading++;
						$request_params2 = array(
							'user_id'      => $user,
							'v'            => '5.130',
							'count'        => $top,
							'filter'       => 'publics',
							'offset'       => 0,
							'extended'     => 1,
							'fields'       => 'members_count',
							'access_token' => $access_token
						);

						$i++; if ($i > 2) { sleep(1); $i = 0; }

						// Делаем запрос к VK API
						$get_params = http_build_query($request_params2);
						$result2 = json_decode(file_get_contents('https://api.vk.ru/method/groups.get?'. $get_params), true);

						if (count($result2['response']['items']) > 0)
						{
							$f = fopen($file, 'a');

							foreach($result2['response']['items'] as $public)
								if ($public['id'] == $request_params['group_id'])
									fwrite($f, $user . "\n");

							fclose($f);
							$procent = round(($loading / $count) * 100);
							$newfile = $dir . '/' . $time . '*=>' . $name . '*=>TopFollowers*=>process*=>'.$procent.'*=>' . '.txt';
							rename($file, $newfile);
							$file = $newfile;
						}

					}

				$i++; if ($i > 2) { sleep(1); $i = 0; }



			} while ($request_params['offset'] <= $result['response']['count']);

		}


    die;

        $time = time();

        $file = session('id') . '_' . $time . '.txt';
        $path = '../storage/app/liders/' . $file;

        $users = explode("\r\n", $request->get('users'));

        file_put_contents($path, $request->get('users'));

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия';

        $work = mywork::create([
            'vk_id' => session('id'),
            'status' => 0,
            'percent' => 0,
            'name' => $name,
            'type' => 'topfollowers',
            'date' => $time,
            'count' => count($users),
            'pid' => 0,
        ]);

       $command = 'php ../app/Http/Exec/execTopFollowers.php '.escapeshellarg($work['id']);

       $process = new Process($command);
       $pid = $process->getPid();

       mywork::where('id', '=', $work['id'])->update(['pid' => $pid]);

       return redirect()->route('profile')->with('success', 'Задача успешно добавлена в работу');

    }


    public function index(){
        dd('fd');
    }
}
