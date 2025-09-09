<?php

namespace App\Http\Controllers;

use App\Functions;
use App\mywork;
use App\Cloud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ToolsController extends Controller
{
    public function tool1()
    {
        $request = $this->checkWork();

        if ($request) {
            $users = explode("\n", $request['users']);
            if (count($users) > 500) {
                $users = array_slice($users, 0, 500);
               // session(['success' => 'Вы загрузили слишком много пользователей. Чтобы не перегружать страницу, я вам показал только первые 500 профилей.']);
            }

            // Убираем лишние символы
            $users = Functions::clearUserName($users);

            // Разбиваем массив на части
            $temp = array_chunk($users, 300);

            $i = 0;
            $data = array();

            $request_params = array(
                'v'            => '5.126',
                'fields'       => 'photo_50,followers_count,is_closed,status',
                'access_token' => session('token')
            );

            // В цикле собираем инф-цию о пользователях
            foreach($temp as $value) {
                $ids = implode(",", $value);
                $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

                $request_params['user_ids'] = $ids;
                $result = Functions::vkapi('users.get', $request_params);

                if (is_array($result)) {
                    $data = array_merge($data, $result);
                }

                $i++; if ($i > 3) {sleep(1); $i = 0;}
            }
            $request['format'] = 1;

            $work = $this->getWork();

            return view('tools.tool1', ['data' => $data, 'request' => $request, 'work' => $work]);
        }

        return view('tools.tool1');
    }

    public function tool1Post(Request $request) {

        $this->validate($request, ['users' => 'required']);

        // Переносим содержимое textarea в массив
        $users = explode("\n", $request->input('users'));

        if (count($users) > 500) {
            $users = array_slice($users, 0, 500);
          //  session(['success' => 'Вы загрузили слишком много пользователей. Чтобы не перегружать страницу, я вам показал только первые 500 профилей.']);
        }

        // Убираем лишние символы
        $users = Functions::clearUserName($users);

        // Разбиваем массив на части
        $temp = array_chunk($users, 300);

        $i = 0;
        $data = array();

        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'photo_50,followers_count,is_closed,status,contacts',
            'access_token' => session('token')
        );

        // В цикле собираем инф-цию о пользователях
        foreach($temp as $value) {
            $ids = implode(",", $value);
            $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

            $request_params['user_ids'] = $ids;
            $result = Functions::vkapi('users.get', $request_params);

            if (is_array($result)) {
                $data = array_merge($data, $result);
            }

            $i++; if ($i > 3) {sleep(1); $i = 0;}
        }

        return view('tools.tool1', ['data' => $data, 'request' => $request]);
    }

    public function tool2() {

        return view('tools.tool2');
    }

    public function tool2Post(Request $request) {

        $this->validate($request, [
            'users1' => 'required',
            'users2' => 'required',
        ]);

        // Переносим содержимое textarea в массив
        $users1 = explode("\r\n", $request->input('users1'));
        $users2 = explode("\r\n", $request->input('users2'));

        // Поиск общих элементов
        $data = array_intersect($users1, $users2);
        $data = array_diff($data, array(''));
        $data = array_unique($data);

        return view('tools.tool2', ['data' => $data, 'request' => $request]);
    }

    public function tool3() {

        return view('tools.tool3');
    }

    public function tool3Post(Request $request) {

        $this->validate($request, [
            'users1' => 'required',
            'users2' => 'required',
        ]);

        // Переносим содержимое textarea в массив
        $users1 = explode("\r\n", $request->input('users1'));
        $users2 = explode("\r\n", $request->input('users2'));

        $data = array_diff($users1, $users2, array(''));
        $data = array_unique($data);

        return view('tools.tool3', ['data' => $data, 'request' => $request]);
    }


    public function tool4() {

        return view('tools.tool4');
    }

    public function tool4Post(Request $request) {

        $this->validate($request, [
            'users' => 'required',
        ]);

        // Переносим содержимое textarea в массив
        $users = explode("\r\n", $request->input('users'));

        $data = array_diff($users, array(''));
        $data = array_unique($data);

        return view('tools.tool4', ['data' => $data, 'request' => $request]);
    }

    public function tool5() {

        return view('tools.tool5');
    }

    public function tool5Post(Request $request) {

        $this->validate($request, [
            'users' => 'required',
        ]);

       	// Переносим содержимое textarea в массив
        $users1 = explode("\r\n", $request->input('users'));

        // Работа с массивом
        $temp = array_diff($users1, array(''));
        $temp = array_count_values($temp);

        $ot = trim($request->input('ot'));
        $do = trim($request->input('do'));

        $data = array();

        foreach($temp as $key => $value) {
            if (($value >= $ot) and ($value <= $do))
                $data[] = $key;
        }


        return view('tools.tool5', ['data' => $data, 'request' => $request]);
    }

    public function showPosts(Request $request){

        $request = $this->checkWork();

        if ($request) {

            $posts = explode("\n", $request['posts']);
            if (count($posts) > 300) {
                $posts = array_slice($posts, 0, 300);
               // session(['success' => 'Вы загрузили слишком много пользователей. Чтобы не перегружать страницу, я вам показал только первые 500 профилей.']);
            }
            $posts = array_diff($posts,["", "\r\n", "\n"]); //


            // Разбиваем массив на части
            $temp = array_chunk($posts, 100);

            $i = 0;
            $data = array();

            $request_params = array(
                'v'            => '5.131',
                'access_token' => session('token')
            );


            // В цикле собираем инф-цию о пользователях
            foreach($temp as $value)
            {
                $ids = implode(",", $value);
                $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

                $request_params['posts'] = $ids;

                $params = http_build_query($request_params);
                $result = json_decode(file_get_contents('https://api.vk.com/method/execute.showPosts?' . $params), true);

                if(!isset($result)) continue;

                foreach($result['response']['items'] as $val){

                    $temp2 = [];

                    $temp2['post'] = $val['from_id'] . '_' .$val['id'];
                    $temp2['text'] = Str::limit($val['text'],500, '...');
                    $temp2['likes'] = $val['likes']['count'];
                    $temp2['comments'] = $val['comments']['count'];
                    $temp2['reposts'] = $val['reposts']['count'];
                    $temp2['date'] = $val['date'];
                    $temp2['from_id'] = $val['from_id'];

                    $data[] = $temp2;

                }

                $groups = [];
                foreach($result['response']['groups'] as $val){

                    $groups['-'.$val['id']]['name'] = $val['name'];
                    $groups['-'.$val['id']]['photo'] = $val['photo_50'];

                }

                $profiles = [];
                foreach($result['response']['profiles'] as $val){

                    $profiles[$val['id']]['name'] = $val['first_name'] . ' ' . $val['last_name'];
                    $profiles[$val['id']]['photo'] = $val['photo_50'];

                }


                $i++; if ($i > 3) {sleep(1); $i = 0;}
            }

            $work = $this->getWork();

            return view('tools.showPosts', [
                'data' => $data,
                'request' => $request,
                'work' => $work,
                'profiles' => $profiles,
                'groups' => $groups,
            ]);
        }

        return view('tools.showPosts');
    }




}
