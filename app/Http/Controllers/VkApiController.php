<?php

namespace App\Http\Controllers;

use App\Newfriend;
use App\User;
use App\Http\Controllers\FriendsController;
use App\Withdraw;
use App\Payment;
use App\utm;
use App\Functions;
use \VK\Client\VKApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class VkApiController extends Controller
{


    public function logout() {
		setcookie("zebra_auth", '', time() - 864000);
        session()->flush();
        return redirect('/');
    }


	public function promo(Request $request) {

		$ORDER_AMOUNT1  = 199;
		$ORDER_AMOUNT2  = 399;
		$ORDER_AMOUNT3  = 599;
		$ORDER_AMOUNT4  = 1299;


		if (mb_strtolower($request->promo) == mb_strtolower('zero')) {
			$ORDER_AMOUNT1  = 0;
			$ORDER_AMOUNT2  = 0;
			$ORDER_AMOUNT3  = 0;
			$ORDER_AMOUNT4  = 0;

			return redirect()->route('zero-pay');
		}



		if (mb_strtolower($request->promo) == mb_strtolower('ZaharyanRulet')) {
			$ORDER_AMOUNT1  = 0;
			$ORDER_AMOUNT2  = 0;
			$ORDER_AMOUNT3  = 0;
			$ORDER_AMOUNT4  = 0;

			return redirect()->route('zero-pay');
		}


		if (mb_strtolower($request->promo) == mb_strtolower('2days')) {
			$ORDER_AMOUNT1  = 0;
			$ORDER_AMOUNT2  = 0;
			$ORDER_AMOUNT3  = 0;
			$ORDER_AMOUNT4  = 0;

			return redirect()->route('two-days');
		}

		if (mb_strtolower($request->promo) == mb_strtolower('mlm2023')) {
			$ORDER_AMOUNT1  =160;
			$ORDER_AMOUNT2  = 320;
			$ORDER_AMOUNT3  = 480;
			$ORDER_AMOUNT4  = 1040;

			session()->flash('success', 'Вы супешно применили промокод!');
		}


		return view('price', [
			'ORDER_AMOUNT1' => $ORDER_AMOUNT1,
			'ORDER_AMOUNT2' => $ORDER_AMOUNT2,
			'ORDER_AMOUNT3' => $ORDER_AMOUNT3,
			'ORDER_AMOUNT4' => $ORDER_AMOUNT4,
		]);
	}

	public function price(){

		return view('price', [
			'ORDER_AMOUNT1' => 299,
			'ORDER_AMOUNT2' => 499,
			'ORDER_AMOUNT3' => 799,
			'ORDER_AMOUNT4' => 1299,
		]);
	}

	public function pricenew()
	{
		return view('price', [
			'ORDER_AMOUNT1' => 199,
			'ORDER_AMOUNT2' => 399,
			'ORDER_AMOUNT3' => 599,
			'ORDER_AMOUNT4' => 1249,
		]);
	}




	public function zero(){
		return view('zero');
	}

	public function zeroPay()
	{
		$id = session('id');
		$transaction = $id.'_'.time().'_1';

		// Проверяем, есть ли в базе эта оплата
		$result = Payment::where('user_id', '=', session('id'))->where('amount', '=', 0)->first();
		if ($result)
			return redirect()->route('price')->with('error', 'Вы уже воспользовались этим купоном');

		$result = User::where('vk_id', '=', $id)->first();
		$access = $result->access;

		if ($access < time()) {
            $access = time();
        }

         $access = strtotime("+1 month", $access);

		 // Создаем оплату
		 $result = Payment::create([
            'user_id' => $id,
            'amount' => 0,
            'package' => 1,
            'transaction' => $transaction,
            'reward' => 0,
            'ref' => NULL,
        ]);

        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', $id)->first();
        $user->update([
            'access' => $access,
            'package' => 1,
        ]);

		utm::where('vk_id', '=', session('id'))->update(['utm_source' => 'zaharyan']);

        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', session('id'))->first();
        session([
            'access' => $user->access,
            'package' => 1,
        ]);

		return view('promo-success');

		//return redirect()->route('myworks')->with('success', 'Успешно');

	}

	public function twodays()
	{

		$id = session('id');
		$transaction = $id.'_'.time().'_1';

		// Проверяем, есть ли в базе эта оплата
		if (session('access') > time())
			return redirect()->route('price')->with('error', 'Для вас недоступен данный купон');

         $access = strtotime("+2 day");

		 // Создаем оплату
		 $result = Payment::create([
            'user_id' => $id,
            'amount' => 0,
            'package' => 1,
            'transaction' => $transaction,
            'reward' => 0,
            'ref' => NULL,
        ]);

        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', $id)->first();
        $user->update([
            'access' => $access,
            'package' => 1,
        ]);

		return redirect()->route('two-day-success');

	}

	public function twodayssuccess()
	{


        // Обновляем данные пользователя
        $user = User::where('vk_id', '=', session('id'))->first();
        session([
            'access' => $user->access,
            'package' => 1,
        ]);

		return view('twodays-success');

	}

    public function profile(){
        $user = User::where('vk_id', '=', session('id'))->first();
        $myRef = User::where('vk_id', '=', $user['ref'])->first();

        return view('profile', ['myRef' => $myRef]);
    }




	public function getActivityPosts() {

		return view('getactivityposts');
	}

	public function postActivityPosts(Request $request) {

		$this->validate($request, ['posts' => 'required']);

		$posts = explode("\r\n", $request->input('posts'));
		$data  = array();
		$i = 0;


        function vkapi($method, $params) {
            $params = http_build_query($params);
            $result = json_decode(file_get_contents('https://api.vk.com/method/'. $method .'?' . $params), true);

			if (isset($result['response'])) {
            	return $result['response'];
			}
        }

		if ($posts)
		{
			foreach($posts as $post)
			{
				// Чистим ссылки. Находим owner id и item id
				$post = strstr($post, 'wall');
				$post = str_replace('wall', '', $post);
				$post = str_replace('%2Fall', '', $post);

				//$post_arr = preg_split("/\||\_/m",$post_id);   - альтернативный способ
				$post_arr = explode('_', $post);

				if (!$post) continue;

				// Получаем лайки
				if (isset($_POST['likes']))
				{
					$request_params = array(
						'type'         => 'post',
						'owner_id'     => $post_arr[0],
						'item_id'      => $post_arr[1],
						'offset'       => 0,
						'count'        => 1000,
						'v'            => '5.130',
						'access_token' => session('token')
					);


					do {
						if (++$i > 2) { sleep(1); $i = 0; }
						$result = vkapi('likes.getList', $request_params);

						$request_params['offset'] = $request_params['offset'] + 1000;

						if (isset($result['items'])) {
							$data = array_merge($data, $result['items']);
						} else break;

					} while($request_params['offset'] <= $result['count']);
				}

				// Собираем комментарии
				if (isset($_POST['comments']))
				{

					$request_params = array(
						'type'         => 'post',
						'owner_id'     =>  $post_arr[0],
						'post_id'      =>  $post_arr[1],
						'offset'       => 0,
						'count'        => 100,
						'v'            => '5.130',
						'access_token' => session('token')
					);

					do {
						if (++$i > 2) { sleep(1); $i = 0; }
						$result = vkapi('wall.getComments', $request_params);
						$request_params['offset'] = $request_params['offset'] + 100;
						if (!isset($result['count'])) break;

						if ($result)
						foreach ($result['items'] as $id)
						{
							if ($id['from_id'] > 0)
								$data[] = $id['from_id'];

							// Комментарии > комментарии
							if ($id['thread'] > 0 and isset($_POST['thread_comments']))
							{
								$request_params2 = $request_params;
								$request_params2['offset'] = 0;
								$request_params2['comment_id'] = $id['id'];

								do {
									$i++;
									if ($i > 1) { sleep(1); $i = 0; }

									$result2 = vkapi('wall.getComments', $request_params2);
									$request_params2['offset'] = $request_params2['offset'] + 100;

									foreach ($result2['items'] as $comment2)
									if ($comment2['from_id'] > 0)
										$data[] = $comment2['from_id'];

								} while($request_params2['offset'] < $result2['count']);
							}

						}

						$i++;
						if ($i > 1) { sleep(1); $i = 0; }

					} while ($request_params['offset'] <= $result['count']);
				}
			}

			// Если стоит галочка ТОЛЬКО УНИКАЛНЫЕ значения
			if (isset($_POST['uniq']))
				$data = array_unique($data);

				if (!Functions::isFullAccess()){
					session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 30 значений из ' . count($data));
					$data = array_slice($data, 0, 30);
				}

			return view('getactivityposts', ['data' => $data, 'request' => $request->all()]);
		}

}


	public function analiz() {

		return view('analiz');
	}

	public function analizStore(Request $request)
	{

		$this->validate($request, ['group' => 'required']);

		$user_id = Functions::clearGroupName($request->input('group'));

		// Формируем ссылку для запроса к ВК АПИ
		$url = "https://api.vk.com/method/groups.getMembers?group_id=" . $user_id .
		"&offset=0&fields=sex,has_photo,can_write_private_message,relation,is_closed,bdate&v=5.131&access_token=" . session('token');

		$result = json_decode(file_get_contents($url),true); // Делаем запрос к ВК АПИ

		// Если запрос неудачный
		if (isset($result['error'])) {
			return redirect()->back()->with('error', 'Неверный ID сообщества');
		}

		$people = $result['response']['count'];  // В переменную reople заносим сколько всего человек состоит в группе
		$result = $result['response']['items'];  // В переменную result получаем весь массив данных о пользователях

		function calculate_age($birthday) {
			$birthday_timestamp = strtotime($birthday);
			$age = date('Y') - date('Y', $birthday_timestamp);
			if (date('md', $birthday_timestamp) > date('md')) {
				$age--;
				}
			return $age;
		}


		// Вспомогательные переменные для работы скрипта
		$slp       = 0; // Эта переменная будет отсчитывать кол-во итераций и через каждые 3 будет делать паузу между запросами к ВК
		$total 	   = 0; // Сколько всего людей
		$offset    = 0; // Сдвижение списка при парсинге ВК

		// В этих переменных будем хранить сегментацию
		$ban_count     = 0;
		$mans          = 0;
		$womens        = 0;
		$ls_close      = 0;
		$array_age     = array();

		$array_age['0-14'][] = 0;
		$array_age['15-24'][] = 0;
		$array_age['25-34'][] = 0;
		$array_age['35-44'][] = 0;
		$array_age['45-54'][] = 0;
		$array_age['55-64'][] = 0;
		$array_age['65'][] = 0;

		$array_age['0'] = 0;
		$array_age['1'] = 0;
		$array_age['2'] = 0;
		$array_age['3'] = 0;
		$array_age['4'] = 0;
		$array_age['5'] = 0;
		$array_age['6'] = 0;
		$array_age['7'] = 0;
		$array_age['8'] = 0;

	while ($offset <= $people)
	{
		foreach($result as $value)
		{

			// Если находим собачку, то прерываем итерацию
			if (isset($value['deactivated'])) {
				$ban_count++;
				continue;
			}

			// Вычисляем возраст
			if ( isset($value['bdate']))
			if ( substr_count($value['bdate'], '.') > 1  )
			{
				$age = calculate_age( $value['bdate']);

				if ( $age > 0 && $age <= 14  ) {	$array_age['0-14'][]  = $value['id']; }
				elseif ( $age >= 15 && $age <= 24 ) {	$array_age['15-24'][] = $value['id']; }
				elseif ( $age >= 25 && $age <= 34 ) {	$array_age['25-34'][] = $value['id']; }
				elseif ( $age >= 35 && $age <= 44 ) {	$array_age['35-44'][] = $value['id']; }
				elseif ( $age >= 45 && $age <= 54 ) {	$array_age['45-54'][] = $value['id']; }
				elseif ( $age >= 55 && $age <= 64 ) {	$array_age['55-64'][] = $value['id']; }
				elseif (     $age >= 65   ) {	$array_age['65'][]    = $value['id']; }
			}


			// Семейное положение
			if ( isset($value['relation']) )
			{

				if ( $value['relation'] == '1'  ) {	$array['1'][]  = $value['id']; }     // Не женат, не замужем
				elseif ( $value['relation'] == '2'  ) {	$array['2'][]  = $value['id']; } // есть друг/есть подруга;
				elseif ( $value['relation'] == '3'  ) {	$array['3'][]  = $value['id']; } // помолвлен/помолвлена;
				elseif ( $value['relation'] == '4'  ) {	$array['4'][]  = $value['id']; } // женат/замужем;
				elseif ( $value['relation'] == '5'  ) {	$array['5'][]  = $value['id']; } // всё сложно;
				elseif ( $value['relation'] == '6'  ) {	$array['6'][]  = $value['id']; } // в активном поиске;
				elseif ( $value['relation'] == '7'  ) {	$array['7'][]  = $value['id']; } // влюблён/влюблена;
				elseif ( $value['relation'] == '8'  ) {	$array['8'][]  = $value['id']; } // в гражданском браке;
				elseif ( $value['relation'] == '0'  ) {	$array['0'][]  = $value['id']; } // не указано.
			}

			// Фильтр по закрытой ЛС
			if  ( $value['can_write_private_message'] == "0" )  {
				$ls_close++;
			}

			// Фильтр по полу
			if  ( $value['sex'] == "2" )  {	$mans++;	}

			if  ( $value['sex'] == "1" )  {	$womens++;	}

			$total++;
		}


		$offset = $offset + 1000;
		$slp++;

		$url = "https://api.vk.com/method/groups.getMembers?group_id=" . $user_id . "&fields=sex,has_photo,can_write_private_message,relation,is_closed,bdate&offset=" . $offset . "&v=5.131&access_token=" . session('token');

		$result = json_decode(file_get_contents($url),true); // Делаем запрос к ВК АПИ
    	$result = $result['response']['items'];  // В переменную result получаем весь массив данных о пользователях

				// Если это третья итерация, делаем паузу в 1 секунду перед следующим запросом к ВК АПИ
				if ( $slp >= 3)
					{
					sleep(1);
					$slp = 0;
					}
		}


		return view('analiz', [
			'request' => $request->all(),
			'people' => $people,
			'ban_count' => $ban_count,
			'mans' => $mans,
			'womens' => $womens,
			'ls_close' => $ls_close,
			'array_age' => $array_age,
			'array' => $array,
		]);
	}





}
