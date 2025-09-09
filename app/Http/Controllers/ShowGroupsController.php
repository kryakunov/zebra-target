<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShowGroupsController extends Controller
{
    public function show()
    {

        $request = $this->checkWork();

		if ($request)
        {

            $groups = explode("\n", $request['groups']);

            if (count($groups) > 500) {
                $groups = array_slice($groups, 0, 500);
                session(['success' => 'Вы загрузили слишком много сообществ. Чтобы не перегружать страницу, я вам показал только первые 500 групп.']);
            }

            $request_params = array(
                'v'            => '5.126',
                'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
                'access_token' => session('token')
            );

            $temp = array_chunk($groups, 300);
            $data = array();
            $i = 0;

            // В цикле собираем инф-цию о сообществах
            foreach($temp as $value)
            {
                $ids = implode(",", $value);
                $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

                $request_params['group_ids'] = $ids;
                $get_params = http_build_query($request_params);
                $result     = json_decode(file_get_contents('https://api.vk.com/method/groups.getById?' . $get_params), true);

                if (!isset($result['response']) ) continue;

                $result     = $result['response'];

                if ($result)
                $data = array_merge($data, $result);

                // Задержка
                $i++; if ($i > 2) { sleep(1); $i = 0; }
            }


            $work = $this->getWork();

            $request['status'] = 1;
            $request['members'] = 1;
            return view('showgroups', ['data' => $data, 'request' => $request, 'work' => $work]);

		}

        return view('showgroups');
    }

    public function handler(Request $request)
    {

        $this->validate($request, ['groups' => 'required']);

        $groups = explode("\n", $request['groups']);

        if (count($groups) > 500) {
            $groups = array_slice($groups, 0, 500);
            session(['success' => 'Вы загрузили слишком много сообществ. Чтобы не перегружать страницу, я вам показал только первые 500 групп.']);
        }

        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
            'access_token' => session('token')
        );

        $temp = array_chunk($groups, 300);
        $data = array();
        $i = 0;

        // В цикле собираем инф-цию о сообществах
        foreach($temp as $value)
        {
            $ids = implode(",", $value);
            $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

            $request_params['group_ids'] = $ids;
            $get_params = http_build_query($request_params);
            $result     = json_decode(file_get_contents('https://api.vk.com/method/groups.getById?' . $get_params), true);

            if (!isset($result['response']) ) continue;

            $result     = $result['response'];

            if ($result)
            $data = array_merge($data, $result);

            // Задержка
            $i++; if ($i > 2) { sleep(1); $i = 0; }
        }
        $request['status'] = 1;
        $request['members'] = 1;
        return view('showgroups', ['data' => $data, 'request' => $request->all()]);
    }
}
