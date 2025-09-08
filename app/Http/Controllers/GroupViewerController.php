<?php

namespace App\Http\Controllers;

use App\Functions;
use App\GroupViewer;
use Illuminate\Http\Request;

class GroupViewerController extends ExecController
{
    public function show($id = null)
    {
        $myGroups = GroupViewer::where('vk_id', '=', session('id'))->first();

        if(!$myGroups){
            return view('groupviewer.start');
        }

        if(!$id) {
            $db = GroupViewer::where('vk_id', '=', session('id'))->first();
            $id = $db->offset;
        }

        GroupViewer::where('vk_id', '=', session('id'))->update(['offset' => $id]);

        $params = [
            'owner_id' => $myGroups->owner_id,
            'count' => 1,
            'offset' => $id,
            'access_token' => session('token'),
            'v' => 5.199,
        ];

        $result = Functions::vkapi('wall.get', $params);

        $text = str_replace(array("\r","\n"),"<br>",$result['items'][0]['text']);
       // $text = $result['items'][0]['text'];

       // $text2 = str_replace(array("\r","\n"),"<br>",$result['items'][1]['text']);
        if (isset($result['items'][0]['copy_history'][0]['text'])){
            $repost = $this->getById($result['items'][0]['copy_history'][0]['from_id']);
            $repost['text'] = str_replace(array("\r","\n"),"<br>",$result['items'][0]['copy_history'][0]['text']);
        }
        else
            $repost = null;



        $date = $result['items'][0]['date'];
        $date = date('d.m.Y', $date);

        $link = 'https://vk.ru/wall' . $result['items'][0]['owner_id'] . '_' . $result['items'][0]['id'];

        return view('groupviewer.show', [
            'text' => $text,
            'repost' => $repost,
            'date' => $date,
            'link' => $link,
            'offset' => $id,
        ]);
    }

    public function getById($id)
    {
        $params = [
            'user_ids' => $id,
            'access_token' => session('token'),
            'fields' => 'photo_100',
            'v' => 5.199,
        ];

        $result = Functions::vkapi('users.get', $params);

        return $result[0];
    }
}
