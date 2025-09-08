<?php

namespace App\Http\Controllers;

use App\Newmember;
use App\User;
use App\Cron\NewMembers;
use App\Functions;
use \VK\Client\VKApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class NewMembersController extends ExecController
{

    const PATH = 'newmembers';
    const PATH_NEW = 'newmembers_new';

    public $pause = 0;

    public function show()
    {

    if (!Functions::isFullAccess()){
        //
        }

    $groups = Newmember::where('vk_id', '=', session('id'))->get();

    if (!session('token') or (empty($groups->first())) ) {
            return view('newmembers', ['groups' => []]);
        }

    $ids = $groups->map(function($group) {
            return $group['track_id'];
        })->toArray();

    $ids = implode(',', $ids);

    try {
        $vk = new VKApiClient();
        $groupsInfo = $vk->groups()->getById(session('token'), [
            'group_ids' => $ids,
            'fields' => 'photo'
        ]);
    } catch (\Exception $exception) {

        return redirect()->back()->with('error', $exception->getMessage());
    }

    $groups = $groups->toArray();


    $new = [];
    foreach ($groupsInfo as $groupInfo) {

        foreach($groups as $group) {

            if ($group['track_id'] == $groupInfo['id']) {
                $new[] = array_merge($group, $groupInfo);
                continue;
            }
        }
    }

    return view('newmembers', ['groups' => $new]);


    }


    public function create()
    {

        if (!Functions::isFullAccess()){
            return redirect()->route('NewMembersShow')->with('pay', 'Функция недоступна на бесплатном доступе :(');
        }

        $groups = Newmember::where('vk_id', '=', session('id'))->get();
        $count = count($groups);

        switch (session('package')) {
            case 1:
                $max_added = 5;
                break;
            case 2:
                $max_added = 10;
                break;
            case 3:
                $max_added = 20;
                break;
            case 4:
                $max_added = 50;
                break;
        }

        if ($count >= $max_added) {
            return redirect()->route('NewMembersShow')->with('pay', 'Вы можете отслеживать максимум '. $max_added .' групп');
        }

        $request = $this->checkWork();

		if ($request) {
			return view('newmemberscreate', ['request' => $request]);
		}

        return view('newmemberscreate', ['request' => []]);
    }


    public function store(Request $request){

        $this->validate($request, ['groups' => 'required']);

        $groups = explode("\n", $request->get('groups'));

        $groupsFromDatabase = Newmember::where('vk_id', '=', session('id'))->count();
        $count = count($groups) + $groupsFromDatabase;

        switch (session('package')) {
            case null:
                return redirect()->route('NewMembersShow')->with('pay', 'Вам недоступна эта функция');
                break;
            case 1:
                $max_added = 5;
                break;
            case 2:
                $max_added = 10;
                break;
            case 3:
                $max_added = 20;
                break;
            case 4:
                $max_added = 50;
                break;
        }

        if ($count > $max_added) {
            return redirect()->route('NewMembersCreate')->withInput()
            ->with('error', 'В вашем тарифе вы можете отслеживать максимум '.$max_added.' сообществ.
                Сейчас вы уже отслеживаете '. $groupsFromDatabase .' сообществ. Вы можете добавить не более '.$max_added - $groupsFromDatabase.' сообществ');
        }

        $i = 0;
        $successes = [];
        $exceptions = [];
        $addedGroups = [];
        $alreadyTracked = [];

        foreach($groups as $group)
        {
            if (++$i > 2) { sleep(1); $i = 0; }
            if (empty($group)) continue;

            $group = Functions::clearGroupName($group);
            $group = Functions::getGroupId($group);
            if (!is_numeric($group)) continue;
            $result = Functions::getMembersGroup($group);

            if (!isset($result) or $result == false) continue;

            if (isset($result['exceptions'])){
                if($result['exceptions'][0] == "Access denied: group hide members")
                    $exceptions[] = 'В сообществе  vk.ru/club' . $group . ' скрыты участники';
                else
                    $exceptions = array_merge($exceptions, $result['exceptions']);

                continue;
            }

            if (Newmember::where('vk_id', '=', session('id'))->where('track_id', '=', $group)->exists()) {

                // Узнаем инфо о сообществе
                $alreadyTracked[$group] = $this->getGroupInfo($group);

                continue;
            }

            $path = 'newmembers/' . session('id') . '_' . $group . '.txt';
            Storage::disk('local')->put($path, implode("\n", $result['items']));

            Newmember::create([
                'vk_id' => session('id'),
                'track_id' => $group,
            ]);

            // Узнаем инфо о сообществе
            $addedGroups[$group] = $this->getGroupInfo($group);
        }


        return redirect()->route('NewMembersShow')->with('alreadyTracked', $alreadyTracked)->with('exceptions', $exceptions)->with('addedGroups', $addedGroups);

    }

    public function getGroupInfo($group)
    {
        if (++$this->pause > 2) { $this->pause = 0; sleep(1); }

        $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?group_id='.$group.'&v=5.131&access_token='.session('token')), true);

        $groups['name'] = $result['response'][0]['name'];
        $groups['photo'] = $result['response'][0]['photo_50'];

        return $groups;
    }


    public function destroy($group_id) {

        Storage::disk('local')->delete('newmembers/'. session('id') . '_' . $group_id . '.txt');
        Storage::disk('local')->delete('newmembers_new/'. session('id') . '_' . $group_id . '.txt');

        Newmember::where('vk_id', '=', session('id'))->where('track_id', '=', $group_id)->delete();

        return redirect()->route('NewMembersShow')->with('success', 'Группа удалена');
    }

    public function update($track_id) {


        if (!Functions::isFullAccess()){
            return redirect()->route('NewMembersShow')->with('pay', 'Функция недоступна на бесплатном доступе :(');
        }


        $newMembers = $this->check($track_id);

        $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?group_id='.$track_id.'&v=5.131&access_token='.session('token')), true);

        $group['name'] = $result['response'][0]['name'];
        $group['photo'] = $result['response'][0]['photo_50'];
        $group['newmembers'] = $newMembers;
        return redirect()->route('NewMembersShow')->with(['group' => $group]);
    }

    public static function check($track_id)
    {

        $group = Newmember::where('vk_id', '=', session('id'))->where('track_id', '=', $track_id)->first();
        $oldMembers = $group->new_members;

        $items = Functions::getMembersGroup($track_id);
        $items = $items['items'];

        $contents = Storage::disk('local')->get(self::PATH . '/' . $group->vk_id . '_' . $group->track_id . '.txt');
        $contents = explode("\n", $contents);

        $newMembers = array_diff($items, $contents);
        $updateMembers = count($newMembers) - $oldMembers;

        Newmember::where('id', '=', $group->id)->update(['new_members' => count($newMembers)]);

        Storage::disk('local')->put(self::PATH_NEW . '/' . $group->vk_id . '_' . $group->track_id . '.txt', implode("\n", $newMembers));

        return $updateMembers;

    }

    public function getNewMembers($id) {

        $path = 'newmembers_new/' . session('id') . '_' . $id . '.txt';
        $data = Storage::disk('local')->get($path);
        $data = explode("\n", $data);

        //return redirect()->route('newtsget')->with('data', $data);
        return view('newmembersget', ['data' => $data, 'groupId' => $id]);
    }

    public function newmembersshowdelete($id) {

        $path = 'newmembers/' . session('id') . '_' . $id . '.txt';
        $path_new = 'newmembers_new/' . session('id') . '_' . $id . '.txt';

        $data = Storage::disk('local')->get($path_new);

        // Обновляем файлы
        Storage::disk('local')->append($path, $data);
        Storage::disk('local')->put($path_new, '');

        // Обновляем базу
        $group = Newmember::where('vk_id', '=', session('id'))->where('track_id', '=', $id)->first();
        Newmember::where('id', '=', $group->id)->update(['new_members' => '0']);

        return redirect()->route('NewMembersShow')->with('success', 'Успешно');
    }




    public function getMembers($group, $token = '')
    {
    if ($token == '') $token = session('token');
    $exceptions = [];
    $data = [];
    $i = 0;
    $offset = 0;
    $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getMembers?group_id='.$group.'&offset='.$offset.'&count=1000&v=5.131&access_token='.$token), true);

    if (isset($result['error'])) {
        $data['exceptions'][0] = $result['error']['error_msg'];
        $data['items'] = [];

        return $data;
    }

    $count = $result['response']['count'];

    do{
        $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getMembersOld?group_id='.$group.'&offset='.$offset.'&count=1000&v=5.131&access_token='.$token), true);

        if (isset($result['error'])) {
            $data['exceptions'] = $result['error']['error_msg'];
            $data['items'] = [];

            return $data;
        }

        if(isset($result['response']))
           $data = array_merge($data, $result['response']);

        $offset += 25000;

        if ($i <= 5) { $i = 0; sleep(1); }

    }while($offset <= $count);

    $data['items'] = $data;

    return $data;
}


    public function cron(){

        $users = User::where('access', '>', time())->get()->toArray();
       /* $users = [0 => [
            'dsf'=>'fsdf',
            'vk_id'=>'649742020',]
        ]; */
        $admins = ['185466160', '573204714'];

        foreach ($users as $user) {

          // if ($user['vk_id'] == '185466160' or $user['vk_id'] == '573204714') continue;

            $groups = Newmember::where('vk_id', '=', $user['vk_id'])->get();
            if (count($groups) < 1) continue;

            foreach ($groups as $group) {

                $dt = strtotime($group['updated_at']);
                if((time() - $dt) < 86400) continue;

                $track_id = $group['track_id'];

                // Собираем участников сообщества
                $items = $this->getMembers($track_id);
              // $items = Functions::getMembersGroup($track_id);
                $items = $items['items'];
                if (!$items) {

                    $log =  $user['vk_id'] . ' > ' . $track_id . '  > ERROR; \n';
                    Storage::disk('local')->append('newmembers_log' . '/' . date('d-m-Y', time()) . '.txt', $log);
                    continue;
                }

                $contents = Storage::disk('local')->get('newmembers' . '/' . $user['vk_id'] . '_' . $track_id . '.txt');
                $contents = explode("\n", $contents);

                $newMembers = array_diff($items, $contents);

                Newmember::where('id', '=', $group['id'])->update(['new_members' => count($newMembers)]);

                Storage::disk('local')->put('newmembers_new' . '/' . $user['vk_id'] . '_' . $track_id . '.txt', implode("\n", $newMembers));

                $log =  $user['vk_id'] . ' > ' . $track_id . ' > ' .count($newMembers) . ' > ok; \n';
                echo $log . '<br>';
                Storage::disk('local')->append('newmembers_log' . '/' . date('d-m-Y', time()) . '.txt', $log);

            }
        }
    }
}
