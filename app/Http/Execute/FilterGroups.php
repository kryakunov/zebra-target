<?php

namespace App\Http\Execute;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

use App\Http\Execute\Execute;

$id = $argv[1];

require 'Execute.php';


class FilterGroups extends Execute
{
    public $file;
    public $id;
    public $access_token;
    public $countMembers;
    public $data = array();
    public $token;
    public $limit;

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
        $this->limit = $this->limit + 1;
    }

    public function parse($request)
    {
        $this->setLog('Получил данные');

        $request_params = array(
			'v'            => '5.126',
			'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
			'access_token' => $this->access_token,
		);


		function filterGroup($data, $property, $param)
		{

			$temp = array();
			foreach($data as $value) {
				if ($value[$property] == $param)
					$temp[] = $value;
			}

			return $temp;
		}

        $res = $this->checkRN();
        $groups = explode($res, $this->getData());

        // Удаляем пробелы и пустые элементы из массива
		$groups = array_diff($groups, array('',' ',"\r\n", "\r", "\n"));
        $count = count($groups);

		//$groups = Functions::clearGroupName($groups);
		$temp = array_chunk($groups, 300);
		$data = array();
        $iter = 0;
        $iter2 = 0;
        $allCount = count($temp);
        $slp = 0;

		$this->setLog('Начинаю парсинг');

		// В цикле собираем инф-цию о сообществах
		foreach($temp as $value)
        {
            // Вычисляем процент
            $percent = round((++$iter2 / $allCount) * 100);
            if ($percent >= 100) $percent = 99;
            $this->setPercent($percent);

			$ids = implode(",", $value);

			$request_params['group_ids'] = $ids;
			$get_params = http_build_query($request_params);
            $result     = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?' . $get_params), true);
            $slp++; if ($slp > 2) { sleep(1); $slp = 0; }

            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {
                    if (++$i > 5) {
                        $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($result['error'])) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result));

                    $this->pause();
                    $request_params['access_token'] = $this->getToken();
                    $this->setLog('Сменил токен');
                    $params = http_build_query($request_params);
                    $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.wallGet?' . $params), true);

                    if (isset($result['response'])) $error = false;

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }


            if (!isset($result['response']) or !($result)) continue;
			$result     = $result['response'];
			$data = $result;

            if (is_numeric($request['ot'])) $people_ot = $request['ot'];
            if (is_numeric($request['do'])) $people_do = $request['do'];
            $temp = array();

            // Если пользователь решил отсортировать по кол-ву участников
            if (isset($people_ot)  or isset($people_do) ) {
                foreach ($data as $value) {
                    if (!isset($value['members_count'])) continue;
                    if (isset($people_ot) and isset($people_do)) {
                        if ($value['members_count'] > $people_ot  and $value['members_count'] < $people_do) $temp[] = $value;
                    } elseif (isset($people_ot)) {
                        if ($value['members_count'] > $people_ot)  $temp[] = $value;
                    } elseif (isset($people_do)) {
                        if ($value['members_count'] < $people_do)  $temp[] = $value;
                    }
                }
            $data = $temp;
            $temp = array();
            }


            // Стоп-слова
            if (!empty($request['stop_words']))
            {

                // Чистим массив от всяких симолов
                $stop_words = explode("\r\n", $request['stop_words']);
                $stop_words = array_diff($stop_words, array('',' '));

                // Собственно, фильтруем
                if (isset($stop_words))
                {
                    $count = count($data);
                    for($i = 0; $i < $count; $i++)
                    {
                        foreach($stop_words as $stop)
                        {
                            $stop = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($stop));
                            $pos = strpos(mb_strtolower($data[$i]['name']), mb_strtolower($stop));
                            if ($pos !== false)
                            {
                                unset($data[$i]);
                                break;
                            }
                        }
                    }

                // Перебираем массив
                $temp = array();
                foreach($data as $value) $temp[] = $value;
                $result = $temp;
                $temp = array();

                }
            }


            // Сообщества с товарами
            if (isset($request['market']))
            {
                $temp = array();
                foreach($data as $value) {
                    if ($value['market']['enabled'] == 1)
                        $temp[] = $value;
                }

                $data = $temp;
                unset($temp);
            }


            // Верифицированные сообщества
            if (isset($request['verified']))
                $data = filterGroup($data, 'verified', 1);

            // Сообщества с огоньком
            if (isset($request['trending']))
                $data = filterGroup($data, 'trending', 1);

            // Сообщества с открытыми ЛС
            if (isset($request['can_message']))
                $data = filterGroup($data, 'can_message', 1);

            // Сообщества с открытыми ЛС
            if (isset($request['can_post']))
                $data = filterGroup($data, 'can_post', 1);

            // Стена
            if (isset($request['wall']))
                if ($request['wall'] !== '9') $data = filterGroup($data, 'wall', $request['wall']);


            // Закрытая или открытая
            if (isset($request['closed']) && $request['closed'] == '1')
                $data = filterGroup($data, 'is_closed', 0);
            elseif (isset($request['closed']) && $request['closed'] == '2')
                $data = filterGroup($data, 'is_closed', 1);

            // Тип сообщества
            if (isset($request['type']))
                if ($request['type'] !== 'all') $data = filterGroup($data, 'type', $request['type']);

            $temp = [];
            foreach($data as $value){
                $temp[] = $value['id'];
            }
            $data = $temp;
            unset($temp);

            // Фильтр по дате последнего поста
            if (!empty($request['date']) and isset($data))
            {
                $date = strtotime($request['date']);
                $temp = array();
                $slp = 0;
                $iter = 0;

                $data = array_chunk($data, 24);
                $countData = count($data);

                foreach ($data as $value)
                {
                    $countIds = count($value);
                    $ids = implode(',', $value);

                    $request_params = array(
                        'v'            => '5.126',
                        'ids'          => $ids,
                        //'owner_id'     => '-'.$value['id'],
                        'offset'       => 0,
                        'count'        => $countIds,
                        'access_token' => $this->access_token,
                    );

                    $get_params = http_build_query($request_params);
                    $result     = json_decode(file_get_contents('https://api.vk.ru/method/execute.getLastPostGroup?' . $get_params), true);
                    $slp++; if ($slp > 2) { sleep(1); $slp = 0; }

                    // Обрабатываем ошибки
                    if (!isset($result['response']))
                    {
                        $this->setLog('Возникла ошибка. Пытаюсь разобраться');
                        $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                        $error = true;
                        $i = 0;
                        do {
                            if (++$i > 5) {
                                $this->setLog('5 ошибок подряд. Останавливаю скрипт');
                                die;
                            }

                            if (isset($result['error'])) {
                                $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                                $this->setLog('Ошибка: '. $msg);
                            } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result));

                            $this->pause();
                            $request_params['access_token'] = $this->getToken();
                            $this->setLog('Сменил токен');
                            $params = http_build_query($request_params);
                            $result     = json_decode(file_get_contents('https://api.vk.ru/method/execute.getLastPostGroup?' . $get_params), true);

                            if (isset($result['response'])) $error = false;

                        } while($error == true);

                        $this->setLog('Ошибка устранена');
                    }

                    foreach($result['response'] as $value){
                        if (!isset($value['items'])) continue;
                        foreach ($value['items'] as $id)
                        if ($id['date'] >= $date) {
                            $temp[] = str_replace("-", "", $id['from_id']);
                            break;
                        }
                    }

                }

                $data = $temp;
                unset($temp);
            }


            foreach($data as $value){
              //  $groups[] = $value;
                file_put_contents($this->file, $value . "\n", FILE_APPEND);
            }

		}



    }
}

$class = new FilterGroups($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setLog('Успешно');
$class->setState('Завершено');

$class->setStatus();


die;
