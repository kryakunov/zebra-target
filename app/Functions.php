<?php

namespace App;

use \VK\Client\VKApiClient;

class Functions
{
    public static function isFullAccess()
    {
        if (session('access') > time()){
            return true;
        }

        return false;
    }

    public static function clearGroupName($name)
    {
        $delete = [
            "/club",
            "vk.ru",
            "vk.ru",
            "http://",
            "https://",
            "/public",
            " ",
            "/"];

        $replace = "";

        // Делаем проверку на массив
        if (is_array($name))
        {
            $name = array_diff($name, array('',' '));

            foreach($name as $key => &$value){
                $value = str_replace($delete, $replace, $value);
            }

            return $name;
        }

        $name = str_replace($delete, $replace, $name);
        $name = trim($name);

        return $name;
    }

    public static function clearUserName($name)
    {
        $delete = [
            "vk.ru",
            "http://",
            "https://",
            " ",
            "id",
            "/"];

        $replace = "";

        // Делаем проверку на массив
        if (is_array($name))
        {

            $name = array_diff($name, array('',' '));

            foreach($name as $key => &$value){
                $value = str_replace($delete, $replace, $value);
            }

            return $name;
        }

        $name = str_replace($delete, $replace, $name);
        $name = trim($name);

        return $name;
    }

    public static function getMembersGroup($group, $token = ''){
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


    public static function getMembersGroupOld($group, $token = '')
    {
        if ($token == '') $token = session('token');
        $offset = 0;
        $items = [];
        $exceptions = [];
        $pause = 0;

        $vk = new VKApiClient();

        do {
            try {
                $response = $vk->groups()->getMembers($token, [
                    'group_id' => $group,
                    'offset' => $offset,
                ]);

            } catch (\Exception $exception) {
                $exceptions[] = $group . ': ' . $exception->getMessage();
                break;
            }

            $offset = $offset + 1000;
            $pause++;

            if ($pause >= 2) {
                sleep(1);
                $pause = 0;
            }

            $items = array_merge($response['items'], $items);

        } while($offset <= $response['count']);

        $result['items'] = $items;
        $result['exceptions'] = $exceptions;

        return $result;
    }

    public static function getGroupId($group, $token = '')
    {
        if ($token == '') $token = session('token');
        $exceptions = [];

        $vk = new VKApiClient();

        try {
            $response = $vk->groups()->getById($token, [
                'group_id' => $group,
            ]);

        } catch (\Exception $exception) {
            $exceptions[] = $group . ': ' . $exception->getMessage();
        }

        if (isset($response)) {
            return $response[0]['id'];
        }

        return null;

    }

    public static function getUserId($user_id)
    {
        $exceptions = [];

        $vk = new VKApiClient();

        try {
            $response = $vk->users()->get(session('token'), [
                'user_ids' => $user_id,
            ]);

        } catch (\Exception $exception) {
            $exceptions[] = $user_id . ': ' . $exception->getMessage();
        }

        if (isset($response)) {
            return $response[0]['id'];
        }

        return null;

    }

    public static function getGroupContacts($group)
    {
        $offset = 0;
        $items = [];
        $exceptions = [];
        $pause = 0;

        $vk = new VKApiClient();

        do {
            try {
                $response = $vk->groups()->getMembers(session('token'), [
                    'group_id' => $group,
                    'offset' => $offset,
                    'fields' => 'contacts, sex',
                ]);

            } catch (\Exception $exception) {
                $exceptions[] = $group . ': ' . $exception->getMessage();
                break;
            }

            $offset = $offset + 1000;
            $pause++;

            if ($pause >= 3) {
                sleep(1);
                $pause = 0;
            }
            dd($response);
            $items = array_merge($response['items'], $items);

        } while($offset <= $response['count']);

        $result['items'] = $items;
        $result['exceptions'] = $exceptions;

        return $result;
    }

    public static function vkapi($method, $params)
    {
        $ch = curl_init('https://api.vk.ru/method/'.$method.'?');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($html, true);

        if (isset($result['response'])) {
            return $result['response'];
        }

        return null;
    }

    public static function vkapOld($method, $params) {
        $params = http_build_query($params);
        $result = json_decode(file_get_contents('https://api.vk.ru/method/'. $method .'?' . $params), true);

        if (isset($result['response'])) {
            return $result['response'];
        }

        return null;
    }

    public static function getUserIds($users)
    {
         // Разбиваем массив на части
         $temp = array_chunk($users, 300);

         $i = 0;
         $data = array();

         $request_params = array(
             'v'            => '5.126',
             'access_token' => session('token')
         );

         // В цикле собираем инф-цию о пользователях
         foreach($temp as $value)
         {
             $ids = implode(",", $value);
             $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

             $request_params['user_ids'] = $ids;
             $result = Functions::vkapi('users.get', $request_params);

             if (is_array($result)) {
                foreach($result as $value) {
                    $data[] = $value['id'];
                }
             }

             $i++; if ($i > 3) {sleep(1); $i = 0;}
         }

         return $data;
    }
}

