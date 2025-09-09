
<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Exec.php';


class GetAllMembers extends Exec
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $i = 0;
    public $findedMembers = 0;
    public $limit = 0;
    public $countGroups;
    public $min = 0;

    public function pause()
    {
        if (++$this->i >= 10) { $this->i = 0; sleep(2); }
        sleep(1);
        $this->limit = $this->limit + 1;
    }

    public function parse($request)
    {

        $this->setLog('start');

        $this->min = $request['min'];

        // Открываем файл со списком групп которые будем парсить
        $res = $this->checkRN();
        $groups = explode($res, $this->getData());
        $iter = 0;

        $allCount = count($groups);
        $this->countGroups = $allCount;

        $continue = false;
        $lastId = $this->getLastId();
        if ($lastId) $continue = true;

        foreach($groups as $key => $group)
        {
            $iter++;

            if($continue) {
                if ($group != $lastId) continue;

                $continue = false;
                $this->setLog('Стартуем сразу с группы '. $group);
            }

            $this->setLastId($group);

            $this->setLog($group);
            $this->tempFile = '../storage/app/tempfiles/' . $this->work['id'] . '_' . $group . '.txt';

            // Высчитываем процент
            $percent = round($iter / $allCount * 100);

            if ($percent < 1) $percent = 1; elseif($percent >= 100) $percent = 99;
            $this->setPercent($percent);

            // Сперва узнаем сколько в группе участников и не скрыты ли они
            $this->pause();

            $request_params = array(
                'v'            => '5.131',
                'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
                'access_token' => $this->access_token,
                'group_id'     => $group,
                'offset'       => 0,
                'count'        => 1,
            );


            $result = $this->vkapi('groups.getMembers', $request_params);


            // Обрабатываем ошибки
            if (!isset($result['response']))
            {
                $this->setLog('Возникла ошибка при попытке получить кол-во участников сообщества. Group: '. $group);

                // Если участники сообщества скрыты или истек лимит токена, продолжаем итерацию
                if (isset($result['error']['error_code']))
                if ($result['error']['error_code'] == 15  or $result['error']['error_code'] == 125 or $result['error']['error_code'] == 100 or $result['error']['error_code'] == 203) {
                    $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                    $this->setLog('Ошибка: ' . $msg . ' Продолжаю скрипт '.$i);
                    unset($groups[$key]);
                    continue;
                }

                $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
                $error = true;
                $i = 0;
                do {



                    if (++$i > 25) {
                        $this->setLog('25 ошибок подряд. Останавливаю скрипт');
                        die;
                    }

                    if (isset($result['error'])) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: '. $msg);
                    } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result). "\n\n");

                    $this->pause();
                    $this->access_token = $this->getToken();
                    $request_params['access_token'] = $this->access_token;
                    $this->setLog('Сменил токен');
                  //  $result = json_decode(file_get_contents('https://api.vk.com/method/groups.getMembers?group_id='.$group.'&offset=0&count=1&v=5.131&access_token='.$this->access_token), true);
                    $result = $this->vkapi('groups.getMembers', $request_params);

                    if (isset($result['response'])) $error = false;

                    // Если участники сообщества скрыты или истек лимит токена, продолжаем итерацию
                    if (isset($result['error']['error_code']))
                    if ($result['error']['error_code'] == 15  or $result['error']['error_code'] == 125 or $result['error']['error_code'] == 100 or $result['error']['error_code'] == 203) {
                        $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                        $this->setLog('Ошибка: ' . $msg . ' Продолжаю скрипт '.$i);
                        unset($groups[$key]);
                        $error = false;
                    }

                } while($error == true);

                $this->setLog('Ошибка устранена');
            }

            if (!isset($result['response']['count'])) {
                $this->setLog('not found response - count ' . serialize($result));
            }

            $count = $result['response']['count'];

            // Парсим участников и пишем в файл
            $this->getMembersGroup($group, $count);

            $this->setLog('ok');
        }

        // Если указано в скольких сообществах он должен минимально состоять
        if ($this->min > 1) {

            $this->setLog('Подсчитываю в скольких сообществах должен состоять');

            $this->intersectionCounting($this->min, $groups);

            /*
            if ($this->countGroups > 6)
                $this->countValuesNew($request['min'], $groups);
            else
                $this->countValues($request['min'], $groups);
                */
        }
    }



public function readLineNew($file, $startId, $stopId)
{
    $i = 0;
    $data = [];

    $line = '';
    $file = fopen($file, "r");

    // Сначала находим стартовый id
    do {

        $id = fgets($file);

        if (!$id) { return false; break; }

    } while((int)$id <= $startId);

    // Потом собираем все значения до конечного id
    for($id; $id <= $stopId; $id = fgets($file) )
    {

        $line .= $id;

        if (!$id) break;
    }
    fclose($file);

    return $line;
}

public function countValuesNew($min, $groups)
{

    $startId = 0;
    $count = count($groups);
    $groupsLastId = []; // здесь будем хранить последний id в файле (чтобы удалить группу из цикла когда там закончатся id)
    $itog = [];
    $step = 10000000; // на сколько id будем увеличивать offset при чтении файла

    // Находим последний id в каждом файле
    foreach($groups as $group)
    {
        $this->tempFile = '../storage/app/tempfiles/' . $this->work['id'] . '_' . $group . '.txt';
        $file = escapeshellarg($this->tempFile); // for the security concious (should be everyone!)
        $line = `tail -n 1 $file`;
        $groupsLastId[$group] = (int)$line;
    }

    do {
        $data = [];

        foreach($groups as $key => $group)
        {

            $this->tempFile = '../storage/app/tempfiles/' . $this->work['id'] . '_' . $group . '.txt';
            if (!file_exists($this->tempFile)) continue;

           // $count = $this->getCountStr($this->tempFile);
           // $offset = 0;

            // Здесь находим какой разделитель в файле \n or \r\n
            $res = $this->checkRNnew($this->tempFile);

            $stopId = $startId + $step;


            $arr = $this->readLineNew($this->tempFile, $startId, $stopId);

            // Если в каждой группе не нашлось новых id, прерываем цикл
            if (!$arr) {
                if ($startId >= $groupsLastId[$group]) {
                    $this->setLog('Группа ' . $group . ' собрана');
                    unset($groups[$key]);
                    unlink($this->tempFile);
                }
                continue;
            }

            $arr = explode($res, $arr);
            array_pop($arr);

            $arr = array_count_values($arr);

            // Объединяем с предыдущими данными из темп файла и подсчитываем сколько нашлось повторяющихся ид
            if (is_array($arr))
            foreach($arr as $key=>$value) {
                if (key_exists($key, $data)) {
                    $data[$key] = $data[$key] + $value;
                } else $data[$key] = $value;
            }
        }

        arsort($data);
        $cc = 0;
        // Делаем подсчет элементов
        foreach($data as $key => $value)
        {
            if($value < $min) break;
            $cc++;
            file_put_contents($this->file, $key . "\n", FILE_APPEND);
        }
        if ($cc > 0)
        $this->setLog('Записал ' . $cc . ' элементов ');
        $startId += $step;

    } while (count($groups) > 0);
}


public function getMembersGroup($group, $count)
{
    if(file_exists($this->tempFile)) unlink($this->tempFile);
    $offset = 0;
    $data = [];
    do {
        $this->pause();

        $request_params2 = array(
            'v'            => '5.131',
            'fields'       => 'wall,verified,trending,status,site,members_count,market,can_message,can_post,city,contacts,description',
            'access_token' => $this->access_token,
            'group_id'     => $group,
            'offset'       => $offset,
        );


        $result = $this->vkapi('execute.getMembersNew', $request_params2);

        // Обрабатываем ошибки
        if (!isset($result['response']))
        {
            $this->setLog('Возникла ошибка при парсинге. Group: '. $group.'; offset: '.$offset);

            // Если участники сообщества скрыты или истек лимит токена, продолжаем итерацию
            if (isset($result['error']['error_code']))
            if ($result['error']['error_code'] == 15 or $result['error']['error_code'] == 125 or $result['error']['error_code'] == 100 or $result['error']['error_code'] == 203) {
                $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                $this->setLog('Ошибка: ' . $msg . ' Продолжаю сkрипт '.$i);
                unset($groups[$key]);
            }

            $this->setLog('Было совершено запросов к АПИ: '. $this->limit);
            $error = true;
            $i = 0;
            do {

                if (++$i > 25) {
                    $this->setLog('25 ошибок подряд. Останавливаю скрипт');
                    die;
                }

                if (isset($result['error'])) {
                    $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                    $this->setLog('Ошибка: '. $msg);
                } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($result). "\n\n");

                $this->pause();
                $this->access_token = $this->getToken();
                $request_params2['access_token'] = $this->access_token;
                $this->setLog('Сменил токен');
                $result = $this->vkapi('execute.getMembersNew', $request_params);

                if (isset($result['response'])) $error = false;

                // Если участники сообщества скрыты или истек лимит токена, продолжаем итерацию
                if (isset($result['error']['error_code']))
                if ($result['error']['error_code'] == 15 or $result['error']['error_code'] == 125 or $result['error']['error_code'] == 100 or $result['error']['error_code'] == 203) {
                    $msg = $result['error']['error_code'] . ' > ' . $result['error']['error_msg'];
                    $this->setLog('Ошибка: ' . $msg . ' Продолжаю сkрипт '.$i);
                    unset($groups[$key]);
                    $error = false;
                }

            } while($error == true);

            $this->setLog('Ошибка устранена');
        }

        $offset += 25000;

        // Пишем в файл
        if ($this->min > 1) {
            $file = '../storage/app/tempfiles/' . $this->work['id'] . '_' . $group . '.txt';
            file_put_contents($file, implode("\n", $result['response']) . "\n", FILE_APPEND);
        } else {
            file_put_contents($this->file, implode("\n", $result['response']) . "\n", FILE_APPEND);
        }

    } while($offset <= $count);


}

}

$class = new GetAllMembers($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

$class->changeStatus($class->access_token, 'free');

$class->setPercent(100);




die;
