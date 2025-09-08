<?php

namespace App\Cron;

use App\Newmember;
use App\User;
use App\Functions;
use \VK\Client\VKApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class NewMembers
{
  
    const PATH = 'newmembers';
    const PATH_NEW = 'newmembers_new';

    public static function check($track_id) {
        

        $users = User::where('access', '>', time())->all();

        dd($users);


        $group = Newmember::where('vk_id', '=', session('id'))->where('track_id', '=', $track_id)->first();

        $items = Functions::getMembersGroup($track_id);
        $items = $items['items'];

        $contents = Storage::disk('local')->get(self::PATH . '/' . $group->vk_id . '_' . $group->track_id . '.txt');
        $contents = explode("\n", $contents);

        $newMembers = array_diff($items, $contents);

        Newmember::where('id', '=', $group->id)->update(['new_members' => count($newMembers)]);

        Storage::disk('local')->put(self::PATH_NEW . '/' . $group->vk_id . '_' . $group->track_id . '.txt', implode("\n", $newMembers));

    }

}
