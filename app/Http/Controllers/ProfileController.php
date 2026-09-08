<?php

namespace App\Http\Controllers;

use App\Http\Exec\Process;
use App\mywork;
use App\stream;
use App\Token;
use App\Functions;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $works = mywork::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        return view('profile', ['data' => $works]);
    }

    public function kill($id)
    {
        $item = mywork::where('id', '=', $id)->first();

        if ($item->streams !== null){

            foreach($item->stream->toArray() as $value){
                $pid = $value['pid'];
                $tokenId = $value['token_id'];

                Token::where('id', '=', $tokenId)->update(['status' => 'free']);
                $d = Process::kill($pid);
                echo '<pre>'; var_dump($d); echo '</pre>'.$pid.'<br>';
            }
            mywork::where('id', '=', $id)->update(['status' => 9]);
        die;
            return redirect()->route('profile')->with('success', 'Остановлено');
        }



        return redirect()->route('profile')->with('success', 'Останss sовлено');
    }


    public function getWorkLiders($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $count = $data->count;
        $name = $data->name;

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/liders/'.$data->vk_id.'__'.$data->date.'.txt';
        $sourceFile = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/liders/'.$data->vk_id.'_'.$data->date.'.txt';
        $sourceData = file_get_contents($sourceFile);
        $sourceData = explode("\r\n", $sourceData);

        $file = file_get_contents($file);
        $data = unserialize($file);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        $temp = [];
        foreach($data as $key => $value)
        {
            $temp[] = $key;
        }

        $users = $this->parseLiders($temp);

        return view('liders', [
                'data' => $data,
                'count' => $count,
                'name' => $name,
                'users' => $users,
                'sourceData' => $sourceData,
            ]);
    }

    public function getWorkFilterUsers($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/'.$data->type.'/'.$data->vk_id.'__'.$data->date.'.txt';
        $file = file_get_contents($file);
        $data = unserialize($file);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        return view('workfilterusers', ['data' => $data]);

    }

    public function workUsersFilterRepeat($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/sourceworks/'.$data->vk_id.'_'.$data->date.'.txt';
        $request = (unserialize(file_get_contents($file)));

        return view('filter', ['request' => $request]);

    }

    public function getWorkUsersFilter($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/works/'.$data->vk_id.'_'.$data->date.'.txt';
        $request = unserialize(file_get_contents($_SERVER["DOCUMENT_ROOT"] . 'storage/app/sourceworks/'.$data->vk_id.'_'.$data->date.'.txt'));
        $file = file_get_contents($file);
        $data = explode("\n", $file);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        return view('workusersfilter', ['data' => $data, 'request' => $request, 'id' => $id]);

    }



    public function getWorkGetMembers($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/'.$data->type.'/'.$data->vk_id.'__'.$data->date.'.txt';
        $data = file_get_contents($file);
        $data = explode("\n", $data);
        //$data = unserialize($file);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        return view('workgetmembers', ['data' => $data]);
    }

    public function getWorkTopFollowers($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $count = $data->count;

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/'.$data->type.'/'.$data->vk_id.'__'.$data->date.'.txt';
        $file = file_get_contents($file);
        $data = unserialize($file);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        return view('topfollowersshow', ['data' => $data, 'count' => $count]);
    }

    public function getWorkUsersGroups($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $count = $data->count;
        $name = $data->name;

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/ugroups/'.$data->vk_id.'_'.$data->date.'.txt';
        $sourceData = file_get_contents($file);
        $sourceData = explode("\r\n", $sourceData);

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/ugroups/'.$data->vk_id.'__'.$data->date.'.txt';
        $data = file_get_contents($file);
        $data = unserialize($data);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        $count = $data;

        $users = $this->parseUsersGroups($data);
        $countUsers = count($users);

        return view('ugroups', [
            'data' => $users,
            'count' => $count,
            'name' => $name,
            'countUsers' => $countUsers,
            'sourceData' => $sourceData,
        ]);
    }

    public function parseLiders($users)
    {
         // Разбиваем массив на части
         $temp = array_chunk($users, 500);

         $i = 0;
         $data = array();

        $token = Token::getToken();
        Token::changeStatus($token, 'busy');

         $request_params = array(
             'v'            => '5.126',
             'fields'       => 'photo_50,followers_count,is_closed,status',
             'count'        => 500,
             'access_token' => $token,
         );

         // В цикле собираем инф-цию о пользователях
         foreach($temp as $value)
         {
             $ids = implode(",", $value);
             $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

             $request_params['user_ids'] = $ids;
             $result = Functions::vkapi('users.get', $request_params);

             if (is_array($result)) {
                 $data = array_merge($data, $result);
             }

         }

        Token::changeStatus($token, 'free');

         return $data;
    }


    public function parseUsersGroups($data)
    {

        $token = Token::getToken();
        Token::changeStatus($token, 'busy');

        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
            'access_token' => $token,
        );

        $i = 0;


        //  собираем инф-цию о сообществах
        $temp = [];
        foreach($data as $key => $value){
            $temp[] = $key;
        }

        $data = array_chunk($temp, 500);
        $groups = [];

        foreach($data as $groupsChunk)
        {
            $ids = implode(",", $groupsChunk);
            $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

            $request_params['group_ids'] = $ids;
            $get_params = http_build_query($request_params);
            $data = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?' . $get_params), true);

            if (isset($data['response']))
                $groups = array_merge($groups, $data['response']);
        }

        Token::changeStatus($token, 'free');

        return $groups;
        /*
        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 3 значений из ' . count($data));
            $data = array_slice($data, 0, 3);
        }*/


    }

    public function deletework($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        if ($data->streams !== null)
        {
            $workId = $data->stream->first();
            stream::where('mywork_id', '=', $workId['mywork_id'])->delete();
        }

        $type = $data->type;
        mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->delete();

        if ($type == 'usersgroups') $path = 'ugroups';
        if ($type == 'ugroups') $path = 'ugroups';
        if ($type == 'liders') $path = 'liders';
        if ($type == 'topfollowers') $path = 'topfollowers';
        if ($type == 'getmembers') $path = 'getmembers';
        if ($type == 'filter') $path = 'filter';

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/'.$path.'/'.$data->vk_id.'__'.$data->date.'.txt';

        if(file_exists($file)) {
            unlink($file);
        }

        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/'.$path.'/'.$data->vk_id.'_'.$data->date.'.txt';

        if(file_exists($file)) {
            unlink($file);
        }


        return redirect()->route('profile')->with('success', 'Успешно удалено');
    }
}
