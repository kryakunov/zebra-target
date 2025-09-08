<?php

namespace App\Http\Controllers;

use App\authtoken;
use App\Token;
use App\User;
use App\utm;
use Illuminate\Http\Request;
use \VK\Client\VKApiClient;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $client_id = 7176370;
    protected $redirect_uri = 'https://zebra-target.ru/vk';


    public function index()
    {

        if (strlen($_SERVER['REQUEST_URI']) > 1){
            echo strlen($_SERVER['REQUEST_URI']);
die;
        } 

        if (session('id'))
        {
            $user = User::where('vk_id', '=', session('id'))->first();

            session([
                'access' => $user->access,
                'package' => $user->package,
            ]);

            return redirect()->route('myworks');
        }

        return view('home');
    }

    public function vkLogin()
    {
        $oauth = new \VK\OAuth\VKOAuth();
        $display = \VK\OAuth\VKOAuthDisplay::PAGE;
        $scope = [\VK\OAuth\Scopes\VKOAuthUserScope::WALL, \VK\OAuth\Scopes\VKOAuthUserScope::MARKET, \VK\OAuth\Scopes\VKOAuthUserScope::GROUPS, \VK\OAuth\Scopes\VKOAuthUserScope::OFFLINE];
        $state = '6L9G6LNPf2X3vuplAyEH';

        $browser_url = $oauth->getAuthorizeUrl(\VK\OAuth\VKOAuthResponseType::CODE, $this->client_id, $this->redirect_uri, $display, $scope, $state);
        
        return redirect($browser_url);
    }

    public function code()
    {
        $oauth = new \VK\OAuth\VKOAuth();
        $client_secret = '6L9G6LNPf2X3vuplAyEH';
        $code = $_GET['code'];

        $response = $oauth->getAccessToken($this->client_id, $client_secret, $this->redirect_uri, $code);
        $access_token = $response['access_token'];

        if ($access_token) 
        {
            $vk = new VKApiClient();
            $response = $vk->users()->get($access_token, [
                'fields'    => ['city', 'photo'],
            ]);
            $response = $response[0];

            session([
                'id' => $response['id'],
                'token' => $access_token,
                'photo' => $response['photo'],
                'first_name' => $response['first_name'],
                'last_name' => $response['last_name'],
            ]);

            // Смотрим куки, на случай если он чей-то реферал
            if (isset($_COOKIE['zebra-target']) and is_numeric($_COOKIE['zebra-target'])) 
            {
                $cookie = $_COOKIE['zebra-target'];
            } else 
                $cookie = null;

            // Получаем utm-метки
            (isset($_COOKIE['zebra-utm_source'])) ? $utm_source = $_COOKIE['zebra-utm_source'] : $utm_source = null;
            (isset($_COOKIE['zebra-utm_medium'])) ? $utm_medium = $_COOKIE['zebra-utm_medium'] : $utm_medium = null;
            (isset($_COOKIE['zebra-utm_campaign'])) ? $utm_campaign = $_COOKIE['zebra-utm_campaign'] : $utm_campaign = null;
            (isset($_COOKIE['zebra-utm_content'])) ? $utm_content = $_COOKIE['zebra-utm_content'] : $utm_content = null;
            (isset($_COOKIE['zebra-utm_term'])) ? $utm_term = $_COOKIE['zebra-utm_term'] : $utm_term = null;

            if (isset($utm_source) or isset($utm_medium) or isset($utm_campaign) or isset($utm_content)  or isset($utm_term) )
            {

                $utm = utm::firstOrCreate([
                    'vk_id' => $response['id']
                ],[
                    'vk_id' => $response['id'],
                    'utm_source' => $utm_source,
                    'utm_medium' => $utm_medium,
                    'utm_campaign' => $utm_campaign,
                    'utm_content' => $utm_content,
                    'utm_term' => $utm_term,
                ]);
            }

            $time = time();

            //$user = User::where('vk_id', '=', $response['id'])->first();
            $user = User::firstOrCreate([
                    'vk_id' => $response['id']
                ],
                [
                    'first_name' => $response['first_name'],
                    'last_name' => $response['last_name'],
                    'photo' => $response['photo'],
                    'ref' => $cookie,
                    'reg' => $time,
                    'last_seen' => $time,
                    'balance' => 0,
                ]);

            // Создаем токен чтобы записать в куки чтобы помнить авторизацию
            $token = hash('sha256', str::random(25));
            
            $authtoken = authtoken::firstOrCreate([
                'vk_id' => $response['id'],
                'user_agent' => $_SERVER["HTTP_USER_AGENT"],
            ],
            [
                'token' => $token,
                'vk_token' => $access_token,
                'user_agent' => $_SERVER["HTTP_USER_AGENT"],
            ]);

            $authtoken->update([
                'token' => $token,
                'vk_token' => $access_token,
                'user_agent' => $_SERVER["HTTP_USER_AGENT"],
            ]);


            // Устанавливаем куки на 10  дней
            setcookie("zebra_auth", $token, time() + 864000);

            Token::create([
                'vk_id' => $response['id'],
                'status' => 'free',
                'token' => $access_token,
            ]);
            
            if ($user) 
            {
                session([
                    'access' => $user['access'],
                    'package' => $user['package'],
                ]);

                $user->update([
                    'last_seen' => time(),
                    'photo' => $response['photo'],
                ]);

            }

            session()->flash('success', 'Вы успешно авторизовались');
  
           /*
            if ($user->reg == $time)
            if (session('access') < time())
                return redirect()->route('two-days');
                */
                
            return redirect()->route('/');
        }
    }


}
