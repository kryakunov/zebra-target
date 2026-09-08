<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $guarded = [];

    public static function getToken()
    {
        $token = self::findToken();

        if (self::checkToken($token))
        {
            self::changeStatus($token, 'busy');

            return $token;
        }

        self::getToken();

    }

    public static function findToken()
    {
        $token = Token::where('status', '=', 'free')->first();

        if (!$token) {
            return session('token');
        }

        return $token->token;
    }

    public static function changeStatus($token, $status)
    {
        Token::where('token', '=', $token)
            ->update([
                'status' => $status,
                'date' => time(),
            ]);

        return true;
    }

    public function uploadImage(array $data)
    {
        return response()->json([
            'success' =>
        ]);
   }

    public static function checkToken($token)
    {
        $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.opinionLeaders?v=5.131&access_token='.$token), true);

        if(!isset($result['response']))
        {
            self::changeStatus($token, 'no valid');

            return false;
        }f

        return true;
    }


    public static function check()
    {
        $tokens = Token::where('status', '=', 'busy')->get()->toArray();

        foreach($tokens as $token)
        {
            sleep(1);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.opinionLeaders?v=5.131&access_token='.$token['token']), true);

            if(!isset($result['response']))
            {
                Token::changeStatus($token['token'], 'no valid');
                echo 'no valid <br>';
                continue;
            }

            Token::changeStatus($token['token'], 'free');

            echo 'free <br>';
        }
dd('use');
        $tokens = Token::where('status', '=', 'free')->get()->toArray();

        foreach($tokens as $token)
        {
            sleep(1);
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.opinionLeaders?v=5.131&access_token='.$token['token']), true);
            if(!isset($result['response']))
            {
                Token::changeStatus($token['token'], 'no valid');
                echo 'no valid <br>';
                continue;
            }

            Token::changeStatus($token['token'], 'free');

            echo 'free <br>';
        }
    echo 'ok';
    }
}
