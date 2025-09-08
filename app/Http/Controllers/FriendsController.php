<?php

namespace App\Http\Controllers;


use App\Newfriend;
use App\Functions;
use App\Cron\NewFriends;
use \VK\Client\VKApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FriendsController extends ExecController
{
    public function get($id){

        if (!is_numeric($user = trim($id))) return false; //$id . ': id is not integer';

        try {
            $vk = new VKApiClient();
            $response = $vk->friends()->get(session('token'), [
                'user_id' => $user,
                'count' => 10000,
            ]);

            return $response['items'];

        } catch (\Exception $exception) {
            //dd($exception->getMessage());
            return false;
            //return $id . ': ' . $exception->getMessage();
        }
    }


    public function store(Request $request){


        if (!Functions::isFullAccess()){
            return redirect()->route('NewFriendsShow')->with('pay', 'Функция недоступна на бесплатном доступе :(');
        }

        $this->validate($request, ['users' => 'required']);

        $users = explode("\n", $request->get('users'));

        $usersFromDatabase = Newfriend::where('vk_id', '=', session('id'))->count();
        $count = count($users) + $usersFromDatabase;

        switch (session('package')) {
            case null:
                return redirect()->route('NewFriendsShow')->with('pay', 'Функция недоступна на бесплатном доступе :(');
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
            return redirect()->route('NewFriendsCreate')->withInput()
            ->with('pay', 'В вашем тарифе вы можете отслеживать максимум '.$max_added.' пользователей.
                Сейчас вы уже отслеживаете '. $usersFromDatabase .' человек. Вы можете добавить не более '.$max_added - $usersFromDatabase.' пользователей');
        }



        $i = 0;
        $is_added = false;

        foreach($users as $user) {

            $user = Functions::clearUserName($user);
            $user = Functions::getUserId($user);

            if (!is_numeric($user = trim($user))) continue;

            if (++$i > 2) { sleep(1); $i = 0; }

            $exceptions = [];

            try {
                $vk = new VKApiClient();
                $result = $vk->friends()->get(session('token'), [
                    'user_id' => $user,
                    'count' => 10000,
                ]);

            } catch (\Exception $exception) {
                $exceptions[] = $user . ': ' . $exception->getMessage();
            }

            if (!isset($result) or $result == false) continue;

            if (Newfriend::where('vk_id', '=', session('id'))->where('track_id', '=', $user)->exists()) {
                continue;
            }

            $path = 'newfriends/' . session('id') . '_' . $user . '.txt';
            Storage::disk('local')->put($path, implode("\n", $result['items']));

            Newfriend::create([
                'vk_id' => session('id'),
                'track_id' => $user,
            ]);

            $is_added = true;
        }

        if ($is_added == true) {
            return redirect()->route('NewFriendsShow')->with('success', 'Пользователи успешно добавлены в отслеживание');
        }

        session()->flash('error', 'Проверьте правильность введенных id');
        return redirect()->route('NewFriendsShow');

    }

    public function destroy($user_id) {

        Storage::disk('local')->delete('newfriends/'. session('id') . '_' . $user_id . '.txt');
        Storage::disk('local')->delete('newfriends_new/'. session('id') . '_' . $user_id . '.txt');

        Newfriend::where('vk_id', '=', session('id'))->where('track_id', '=', $user_id)->delete();

        return redirect()->route('NewFriendsShow')->with('success', 'Пользователь удален');
    }

    public function update() {

        if (!Functions::isFullAccess()){
            return redirect()->route('NewFriendsShow')->with('pay', 'Функция недоступна на бесплатном доступе :(');
        }

        NewFriends::check();

        return redirect()->route('NewFriendsShow')->with('success', 'Успешно');
    }

    public function show(){

        if (!session('token')) {
            return view('newfriends2', ['users' => []]);
        }

        $users = Newfriend::where('vk_id', '=', session('id'))->get();

        $ids = $users->map(function($user) {
            return $user['track_id'];
        })->toArray();

        $ids = implode(',', $ids);

        try {
            $vk = new VKApiClient();
            $usersInfo = $vk->users()->get(session('token'), [
                'user_ids' => $ids,
                'fields' => 'photo'
            ]);
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }

        $users = $users->toArray();

        $new = [];
        foreach ($usersInfo as $userInfo) {
            foreach($users as $user) {
                if ($user['track_id'] == $userInfo['id']) {
                    $new[] = array_merge($user, $userInfo);
                    continue;
                }
            }
        }


            return response()
                ->view('newfriends2', ['userss' => $new])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function create()
    {

        $users = Newfriend::where('vk_id', '=', session('id'))->get();
        $count = count($users);

        switch (session('package')) {
            case null:
                return redirect()->route('NewFriendsShow')->with('pay', 'Функция недоступна на бесплатном доступе :(');
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

        if ($count >= $max_added) {
            return redirect()->route('NewFriendsShow')->with('error', 'Вы можете отслеживать максимум '. $max_added .' пользователей');
        }

        $request = $this->checkWork();

		if ($request) {
			return view('newfriendscreate', ['request' => $request]);
		}

        return view('newfriendscreate', ['request' => [] ]);
    }

    public function getNewFriends($id) {

        $path = 'newfriends_new/' . session('id') . '_' . $id . '.txt';
        $data = Storage::disk('local')->get($path);
        $data = explode("\n", $data);

        $ids = implode(',', $data);

        try {
            $vk = new VKApiClient();
            $users = $vk->users()->get(session('token'), [
                'user_ids' => $ids,
                'fields' => 'photo'
            ]);
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }


        //return redirect()->route('newfriendsget')->with('data', $data);
        //return view('newfriendsget', ['data' => $data]);
        return view ('shownewfriends', ['data' => $users]);
    }

    public function getDelFriends($id) {

        $path = 'deletefriends/' . session('id') . '_' . $id . '.txt';
        $data = Storage::disk('local')->get($path);
        $data = explode("\n", $data);

        $ids = implode(',', $data);

        try {
            $vk = new VKApiClient();
            $users = $vk->users()->get(session('token'), [
                'user_ids' => $ids,
                'fields' => 'photo'
            ]);
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }

        //return redirect()->route('newfriendsget')->with('data', $data);
        return view('shownewfriends', ['data' => $users]);
    }


}
