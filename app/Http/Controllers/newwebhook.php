<?php

namespace App\Http\Controllers;

use App\Functions;
use App\Payment;
use App\User;
use Illuminate\Http\Request;

include '../Exec/CurlPost.php';


class newwebhook extends Controller
{
    // Пользователи с индивидуалными условиями рефералки
    public $users = [];//[100011689, 370003517, 301974853, 798707702, 797915585];

    public function index()
    {
        $s_key = '84b1baf737b2c0d4e185e9aa371befd0219635e8';
        $dop_key = '0183b610e68f9dd7540a76c028a2db5b6adff03e';

        $merchant = $_REQUEST['merchant']; // id вашего магазина
        $secret_word2 = 'AyEZSPbykfkXk34OgcHShTT2e5MWCrHx'; // секретный ключ 2

        $sign = md5($merchant.':'.$_REQUEST['amount'].':'.$secret_word2.':'.$_REQUEST['merchant_id']);

        if ($sign !== $_REQUEST['sign_2']) {
            die('bad sign!');
        }

        $request = explode('_', $_REQUEST['merchant_id']);
        $user_id = $request[0];
        $package = $request[2];

        // Проверяем, есть ли в базе эта оплата
        $result = Payment::where('transaction', '=', $_REQUEST['merchant_id'])->first();
        if ($result) die('Оплата уже прошла');

        $result = User::where('vk_id', '=', $user_id)->first();

        $ref = $result->ref;

        $access = $result->access;

		$reward = round($_REQUEST['amount'] * (30 / 100));

        // Проверяем, индивидуальные ли условия по рефке у человека
        if (in_array($ref, $this->users)){
            $reward = $_REQUEST['amount'];
        }

        if ($access < time()) {
            $access = time();
        }

        switch ($package) {
            case 1:
                $access = strtotime("+1 month", $access);
                break;
            case 2:
                $access = strtotime("+3 month", $access);
                break;
            case 3:
                $access = strtotime("+6 month", $access);
                break;
            case 4:
                $access = strtotime("+12 month", $access);
                break;
        }

        // Создаем оплату
        $result = Payment::create([
            'user_id' => $user_id,
            'amount' => $_REQUEST['amount'],
            'package' => $package,
            'transaction' => $_REQUEST['merchant_id'],
            'reward' => $reward,
            'ref' => $ref,
        ]);

        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', $user_id)->first();
        $user->update([
            'access' => $access,
            'package' => $package,
        ]);

        // Начисляем реферальные
        $user = User::where('vk_id', '=', $ref)->first();
        $user->update([
            'balance' => $user->balance + $reward,
        ]);

        echo 'Good ' . $user_id;

    }

    public function success(){

        $user = User::where('vk_id', '=', session('id'))->first();

        session([
            'access' => $user->access,
            'package' => $user->package,
        ]);

        return redirect()->route('/')->with('success', 'Оплата прошла успешно');
    }

    public function gift($id){



        $id = Functions::getUserId($id);
        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'photo_50',
            'count'        => 300,
            'access_token' => 'vk1.a.SCW9bdTjlPw_arJlkysKbg3ZRoTxhp32XRzC1ELfkaKiNRrv19cakh0yPPAy0r6nmdD6WceU6TK1KP7sKF16jqx8jTkC2GG2Qka9GVkHGn_esZix8U5FZePDGyUSIH28oA9Jrdi9K3j2eiQtIfu5xNoqOPu2jxwOlzU4OWaFe8qrnvEDWlCJ6U7EGfr6NY5HRLSRCmqxfBzI2XnBbhn7HA',
            'user_ids'     => $id,
        );

        $params = http_build_query($request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . $params), true);

        return view('gift', ['id' => $id, 'user' => $result['response'][0]]);
    }
}
