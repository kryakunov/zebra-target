<?php

namespace App\Http\Controllers;

use App\Http\Exec\Process;
use App\Token;
use App\Functions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

date_default_timezone_set('Asia/Yekaterinburg');

class WhenOnlineController extends Controller
{
    public $token;
    public $users = [ '21257717','185466160', '484403797'];

    public function __construct()
    {
        $this->token = 'vk1.a.cuf9jk2W0tSKTi66_FdU2ZwvFraJdwQxj6T02CDNG-CMZzL9NyMmmduSAUDTgDMGVH_o0WTwtsdScPz0vJUht446tao3j1ZI-QMZTiM30b18EjP_xfz82Ul2HjhRswUrefSpAELQ3uFPQGPQiOD7oFtZp1G1A-J4yNzoEiN_-DqnK60PzF2UwxVvztq7uXYNf5d3-DgC6EI2hN94ji5DzA';
    }

    public function delete()
    {
        foreach($this->users as $user)
        {
            $file = 'whenonline/'.$user.'.txt';
            Storage::disk('local')->delete($file);

            echo "$file deleted <br>";
        }
    }



    public function show()
    {
        foreach($this->users as $user)
        {
            $data[$user] = $this->getAllLog($user);
        }


        return view('whenonline', compact('data'));
    }

    public function index()
    {
       // session('token') = Token::getToken();

        foreach($this->users as $user)
        {
            $result = $this->vkapi($user);

            $last_seen = $result['response'][0]['last_seen']['time'];
            $platform = $result['response'][0]['last_seen']['platform'];

            $log = date('d.M H:i', $last_seen) . '/' . $platform;

            $data = explode("/", $this->readLog($user));

            $last_seen_from_log = $data[0];

            // Если дата последнего захода не изменилась, ничего не делаем
            if ($last_seen_from_log == date('d.M H:i', $last_seen)) continue;

            // Иначе пишем лог
            $this->writeLog($user, $log);
        }

        echo $last_seen_from_log;
    }

    public function checkExistsFile($user)
    {
        $file = 'whenonline/'.$user.'.txt';

        if (Storage::disk('local')->missing($file)) {
            Storage::disk('local')->put($file, '');
            return;
        }
    }


    public function vkapi($user)
    {
        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'online,last_seen',
            'user_ids'     => $user,
            'access_token' => $this->token,
        );

        $params = http_build_query($request_params);
        $result = json_decode(file_get_contents('https://api.vk.ru/method/users.get?' . $params), true);

        return $result;
    }

    public function writeLog($user, $data)
    {
        $file = 'whenonline/'.$user.'.txt';

        $this->checkExistsFile($user);

        Storage::disk('local')->append($file, $data);
    }

    public function readLog($user)
    {
        $this->checkExistsFile($user);

        $data = Storage::disk('local')->get('whenonline/'.$user.'.txt');

        $data = explode("\n", $data);

        $count = count($data) - 1;

        return $data[$count];
    }


    public function getAllLog($user)
    {
        $this->checkExistsFile($user);

        $data = Storage::disk('local')->get('whenonline/'.$user.'.txt');

        $data = explode("\n", $data);

        return $data;
        $currentData = date('d.M');

        $newArr = [];

        foreach($data as $log) {

            $newArr[] = $log;
            $position = strpos($log, $currentData);
            if ($position !== false) {

               return $newArr;
            }
        }
        return $data;
    }
}
