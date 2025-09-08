<?php

namespace App\Http\Controllers;

use App\Functions;
use \VK\Client\VKApiClient;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function usersGroups() {

        return view('usersgroups');
    }

    public function usersGroupsHandler(Request $request) {

        $this->validate($request, [
            'users' => 'required',
        ]);

        $group = $request->input('group');
        $group = Functions::clearGroupName($group);
        $group = Functions::getGroupId($group);

        $ot = $request->input('ot');
        $do = $request->input('do');
        $data = [];

        $users = explode("\r\n", $request->get('users'));

        $users = Functions::clearUserName($users);
        $users = array_unique($users);

        $request_params = array(
            'user_id'      => '',
            'v'            => '5.130',
            'count'        => 1000,
            'offset'       => 0,
            'extended'     => 0,
            'filter'       => 'publics',
            'fields'       => 'members_count',
            'access_token' => session('token')
        );

		// Запрос к ВК АПИ
        $error = [];
        $i = 0;
        foreach ($users as $user) {

            $request_params['user_id'] = $user;
		    //$result = Functions::vkapi('groups.get', $request_params);

            $params = http_build_query($request_params);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.get?' . $params), true);

            if (isset($result['response'])) {
                $result = $result['response'];
            } else {
                $error[] = $result['error']['error_msg'];
                continue;
            }


            if (!$result) {  continue; }
            $result = collect($result['items'])->take($do)->contains($group);
            if ($result) {
                $data[] = $user;
            }

            $i++; if ($i = 1) {sleep(1); $i = 0;}
        }

        return view('usersgroups', ['request' => $request, 'data' => $data, 'error' => $error]);
    }
}
