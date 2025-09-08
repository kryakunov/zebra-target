<?php

namespace App\Cron;

use App\Newfriend;
use App\Http\Controllers\FriendsController;
use \VK\Client\VKApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewFriends
{
  
    const PATH = 'newfriends';
    const PATH_NEW = 'newfriends_new';
    const PATH_DELETE = 'deletefriends';

    public static function check() 
    {
        
        $users = Newfriend::where('vk_id', '=', session('id'))->get();
        $i = 0;

        foreach ($users as $user) {

            if (++$i > 3) { sleep(1); $i = 0; }

            $friends = new FriendsController();
            $items = $friends->get($user->track_id);
            if(!$items) continue;

            $contents = Storage::disk('local')->get(self::PATH . '/' . $user->vk_id . '_' . $user->track_id . '.txt');
            $contents = explode("\n", $contents);

            $newFriends = array_diff($items, $contents);
            $delFriends = array_diff($contents, $items);

            Newfriend::where('id', '=', $user->id)->update(['new_friends' => count($newFriends), 'delete_friends' => count($delFriends)]);

            Storage::disk('local')->put(self::PATH_NEW . '/' . $user->vk_id . '_' . $user->track_id . '.txt', implode("\n", $newFriends));
            Storage::disk('local')->put(self::PATH_DELETE . '/' . $user->vk_id . '_' . $user->track_id . '.txt', implode("\n", $delFriends));
  
        }
    }

}