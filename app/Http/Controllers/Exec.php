<?php


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

require 'CurlPost.php';

class Exec
{
    const FILE = 'works';
    const SOURCEFILE = 'sourceworks';
    const TEMPFILE = 'tempfiles';
    const LOGFILE = 'logs';
    const URL = 'https://api.vk.com/method/';

    public $PDO;
    public $file;
    public $sourceFile;
    public $id;
    public $access_token;
    public $countMembers;
    public $data = array();
    public $token;
    public $work;
    public $stream;
    public $fromWork;
    public $logFile;

    public function __construct($id)
    {
        $driver = 'mysql'; // тип базы данных, с которой мы будем работать
        $host = '127.0.0.1';// альтернатива '127.0.0.1' - адрес хоста, в нашем случае локального
        $db_name = 'host1380688_zebranew'; // имя базы данных
        $db_user = 'host1380688_root'; // имя пользователя для базы данных
        $db_password = 'banlieve'; // пароль пользователя
        $charset = 'UTF-8'; // кодировка по умолчанию
        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION); // массив с дополнительными настройками подключения. В данном примере мы установили отображение ошибок, связанных с базой данных, в виде исключений
        $dsn = "$driver:host=$host;dbname=$db_name";

        $this->PDO = new PDO($dsn, $db_user, $db_password, $options);

        $this->id = $id;
        $this->work = $this->getById($id);
        $this->stream = $this->getStreamById($id);

        sleep($this->stream['stream_id']);

        $this->file = '../storage/app/'.self::FILE.'/'.$this->work['vk_id'].'_'.$this->work['date'] . '.txt';
        $this->logFile = '../storage/app/'.self::LOGFILE.'/'.$this->work['vk_id'].'_'.$this->work['date'] . '.txt';
        $this->sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$this->work['vk_id'].'_'.$this->work['date'] . '.txt';
        $this->tempFile = '../storage/app/'.self::TEMPFILE.'/'.$this->work['id'].'_'.$this->stream['stream_id'] . '.txt';


        //if (file_exists($this->logFile)) unlink($this->logFile);
        if (file_exists($this->tempFile)) unlink($this->tempFile);

        $this->setLog('------------------');
        $this->setLog('Запущен скрипт  ' . $this->work['name']);
        $this->setLog('------------------');

        $this->access_token = $this->getToken();
        $this->setLog('В изначальном списке: ' . $this->work['source_count']);
    }

    public function setLastId($id)
    {
        $sql = 'UPDATE streams SET last_id=:last_id WHERE id=:id';
        $values = array("last_id" => $id, "id" => $this->stream['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);
    }

    public function vkapi($method, $params)
    {
        $response = $this->tryRequest($method, $params);

        // Если словили исключение
        if (!$response)
        {
            dd($response);
            $error = true;

            do {
                // Повторяем запрос
                $this->pause();
                $response = $this->tryRequest($method, $params);

                if ($response) $error = false;

            } while ($error == true);
        }

         // Если словили ошибку
        if (isset($response['error']))
        {

            $errorCode = $response['error']['error_code'];
            $errorMsg = $response['error']['error_msg'];
            $this->setLog('Ошибка: '. $errorCode . ' > ' . $errorMsg);

            $error = true;

            // Ошибки которые игнорируем
            $codes = [15, 30, 125, 100, 203];

            if (in_array($errorCode, $codes))
            {
                $this->setLog('Ошибка: ' . $errorCode . ' > ' . $errorMsg . ' Продолжаю скрипт ');
                $error = false;
            }

            if ($error)
            do {
                dd('error', $response);
                // Меняем токен
                $this->access_token = $this->getToken();
                $params['access_token'] = $this->access_token;

                // Повторяем запрос
                $this->pause();
                $response = $this->tryRequest($method, $params);

                if (in_array($errorCode, $codes))
                {
                    $this->setLog('Ошибка: ' . $errorCode . ' > ' . $errorMsg . ' Продолжаю скрипт ');
                    $error = false;
                }

                if (isset($response['response'])) $error = false;

            } while ($error == true);

            $this->setLog('Ошибка устранена');

        }

        return $response;
    }

    public function tryRequest($method, $params)
    {
        // create curl object
        $curl = new CurlPost(self::URL . $method);

        try {
            // execute the request
            return json_decode($curl($params), true);

        } catch (\RuntimeException $ex) {
            // catch errors
            $this->setLog(sprintf('Http error %s with code %d', $ex->getMessage(), $ex->getCode()));

            return false;
        }
    }



    public function getLastId()
    {
        $sql = "SELECT last_id FROM streams WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $this->stream['id']);
        $statement->execute();
        $res = $statement->fetch(\PDO::FETCH_ASSOC);

        return $res['last_id'];
    }



    public function getMyToken()
    {
        $sql = "SELECT * FROM tokens WHERE vk_id = ? AND status=?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $this->work['vk_id']);
        $statement->bindValue(2, 'free');
        $statement->execute();
        $token = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            $this->setError('my token busy');

            // xm....
            $sql = "SELECT * FROM tokens WHERE vk_id = ? AND status=?";

            $statement = $this->PDO->prepare($sql);
            $statement->bindValue(1, $this->work['vk_id']);
            $statement->bindValue(2, 'busy');
            $statement->execute();
            $token = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$token) die;

            return $token['token'];
        }

        return $token['token'];

    }


    public function getToken()
    {
        $sql = "SELECT * FROM tokens WHERE status = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, 'free');
        $statement->execute();
        $token = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$token)
        {
            $this->setError('end tokens');
            $this->setLog('Закончились токены');
            die;
        }

        $this->changeStatus($token['token'], 'busy');

        // Проверяем токен на валидность
       $result = json_decode(file_get_contents('https://api.vk.com/method/execute.opinionLeaders?v=5.131&access_token='.$token['token']), true);

        if(!isset($result['response']))
        {
            $this->changeStatus($token['token'], 'no valid');
            $this->getToken();
            $this->setLog('Токен N ' . $token['id'] . ' не прошел авторизацию');
            sleep(1);
        }

        $this->setLog('Получил токен N ' . $token['id']);

        $sql = 'UPDATE streams SET token_id=:token_id WHERE id=:id';
        $values = array("token_id" => $token['id'], "id" => $this->id);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

        $sql = 'UPDATE tokens SET date=:date, status=:status WHERE id=:id';
        $values = array("date" => time(), "status" => "busy", "id" => $token['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

        $this->request_params['access_token'] = $token['token'];
        $this->access_token = $token['token'];

        return $token['token'];
    }



    public function setToken($token)
    {
        $this->token = $token;
    }

    public function checkRN()
    {
        $handleFile = fopen($this->sourceFile, "r");
        $line = fgets($handleFile);
        fclose($handleFile);

        $pos = strpos($line, "\r\n");

        if ($pos !== false) {
            $res = "\r\n";
        } else {
            $res = "\n";
        }

        return $res;
    }

    public function changeStatus($token, $status)
    {
        $sql = 'UPDATE tokens SET status=:status WHERE token=:token';
        $values = array("status" => $status, "token" => $token);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM myworks WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $res['mywork_id']);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res;
    }

    public function getStreamById($id)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res;
    }

    public function getWorkById($id)
    {
        $sql = "SELECT * FROM myworks WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return $res;
    }


    public function getRequest($id)
    {
        $sql = "SELECT * FROM myworks WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $this->work['id']);
        $statement->execute();
        $res = $statement->fetch(PDO::FETCH_ASSOC);

        return unserialize($res['request']);
    }

    public function getData()
    {
        $item = $this->getWorkById($this->work['id']);
        $path = '../storage/app/sourceworks/' . $item['vk_id'] . '_' . $item['date'] . '.txt';
        $data = file_get_contents($path);

        return $data;
    }


    public function openFile($file)
    {
        $data = file_get_contents($file);
		$data = unserialize($data);

        return $data;
    }

    public function setPercent($percent)
    {

        // Сколько процентов в таблицы streams
        $sql = 'UPDATE streams SET percent=:percent WHERE id=:id';
        $values = array("percent" => $percent, "id" => $this->id);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

        $this->setPercentInMyworkTable();
    }

    public function setPercentInMyworkTable()
    {
        $sql = "SELECT * FROM streams WHERE mywork_id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $this->work['id']);
        $statement->execute();
        $res = $statement->fetchAll(PDO::FETCH_ASSOC);

        $percent = 0;
        foreach($res as $value)
        {
            $percent += $value['percent'];
        }

        $percent = $percent / $this->work['streams'];

        $sql = 'UPDATE myworks SET percent=:percent WHERE id=:id';
        $values = array("percent" => $percent, "id" => $this->work['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

        if ($percent >= 100) {
            $this->setStatus();
        }

    }

    public function setStatus()
    {
        if (file_exists($this->file)) {
            $count = $this->getCountStr();

            if ($count < 3) {
                $line = fgets(fopen($this->file, 'r'));
                if (strlen($line) < 3) $count = 0;
            }
        }
        else {
            $count = 0;
            //file_put_contents($this->file, '');
        }

        $date_end = time();

        $sql = 'UPDATE myworks SET status=:status, count=:count, date_end=:date_end WHERE id=:id';
        $values = array("status" => 1, "count" => $count, "date_end" => $date_end, "id" => $this->work['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

        $sql = 'UPDATE streams SET status=:status, last_id=:last_id WHERE id=:id';
        $values = array("status" => 1, "last_id" => null, "id" => $this->stream['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

        $this->setLog('Задача выполнена');
    }

    public function ifError($data, $url)
    {
        // Обрабатываем ошибки
        if (!isset($data['response']))
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

                if (isset($data['error'])) {
                    $msg = $data['error']['error_code'] . ' > ' . $data['error']['error_msg'];
                    $this->setLog('Ошибка: '. $msg);
                } else $this->setLog('Возникла ошибка, но error_msg не найдено '.serialize($data));

                $this->pause();
                $request_params['access_token'] = $this->getToken();
                $this->setLog('Сменил токен');
                $params = http_build_query($request_params);
                $data = json_decode(file_get_contents($url), true);

                if (!isset($data['response'])) $error = false;

            } while($error == true);

            $this->setLog('Ошибка устранена');

            return $data;
        }
    }


    public function setThisStatus()
    {
        $sql = 'UPDATE streams SET status=:status, percent=:percent WHERE id=:id';
        $values = array("status" => 1, "percent" => "100", "id" => $this->stream['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);
    }

    public function setCount($count)
    {
        $sql = 'UPDATE myworks SET count=:count WHERE id=:id';
        $values = array( "count" => $count, "id" => $this->work['id']);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);
    }

    public function setLog($msg){
        $time = date('h:i:s', time());
        $data = $time . ': stream: ' . $this->stream['stream_id'] . ' msg: ' . $msg . "\n";
        file_put_contents($this->logFile, $data, FILE_APPEND);
    }

    public function writeFile($path, $data)
    {
        $data = serialize($data);
		file_put_contents($path, $data);
    }

    public function getCountStr()
    {
        $linecount = 0;
        $handleFile = fopen($this->file, "r");
        while(!feof($handleFile)){
          $line = fgets($handleFile);
          if (strlen($line) > 1) $linecount++;
        }

        fclose($handleFile);
       // $linecount--;

        return $linecount;
    }


    public function setError($msg)
    {
        $sql = "SELECT * FROM streams WHERE id = ?";

        $statement = $this->PDO->prepare($sql);
        $statement->bindValue(1, $this->id);
        $statement->execute();
        $stream = $statement->fetch(PDO::FETCH_ASSOC);

        $msg = $stream['error'] . ' / ' . $msg;
        $sql = 'UPDATE streams SET error=:error WHERE id=:id';
        $values = array("error" => $msg, "id" => $this->id);
        $statement = $this->PDO->prepare($sql);
        $statement->execute($values);

    }


    public static function clearUserName($name)
    {
        $delete = array(
            "vk.com",
            "http://",
            "https://",
            " ",
            "id",
            "/");

        $replace = "";

        // Делаем проверку на массив
        if (is_array($name))
        {
            $name = array_diff($name, array('',' '));
            $count = count($name);
            for($i = 0; $i < $count; $i++)
            {
                $name[$i] = str_replace($delete, $replace, $name[$i]);
                $name[$i] = trim($name[$i]);
                // Удаляем пустые элементы массива
                $name = array_diff($name, array('',' '));
            }

            return $name;
        }

        $name = str_replace($delete, $replace, $name);
        $name = trim($name);

        return $name;
    }



public function countValues($min)
{
    $count = $this->getCountStr($this->file);
    $offset = 0;

    // Здесь находим какой разделитель в файле \n or \r\n
    $res = $this->checkRNnew($this->file);
    $data = [];

    // В цикле считываем по 300000
    while($offset <= $count)
    {
        $start = $offset;
        $stop = $offset + 300000;
        $arr = $this->readLine($this->file, $start, $stop);
        $arr = explode($res, $arr);
        array_pop($arr);

        $arr = array_count_values($arr);
       // arsort($arr);

        // Объединяем с предыдущими данными из темп файла и подсчитываем сколько нашлось повторяющихся ид
        if (is_array($arr))
        foreach($arr as $key=>$value) {
            if (key_exists($key, $data)) {
                $data[$key] = $data[$key] + $value;
            } else $data[$key] = $value;
        }

       // arsort($data);
        $offset += 300000;
    }

    $temp = [];
    arsort($data);

    // Делаем подсчет элементов
   foreach($data as $key => $value)
   {
        if($value < $min) break;
        $temp[] = $key;
   }

   unset($data);


   // Перезаписываем файл
   $temp = implode("\n", $temp);
   file_put_contents($this->file, $temp);

}

public function readLine($file, $start, $stop)
{
    $i = 0;
    $data = [];

    $line = '';
    $file = fopen($file, "r");
    for($i = 0; $i <= $stop; $i++)
    {
        $str = fgets($file);

        if (!$str) { return $line; break; }

        if ($i >= $start){
            $line .= $str;
        }

    }
    fclose($file);

    return $line;
}



public function checkRNnew($file)
{
    $handleFile = fopen($file, "r");
    $line = fgets($handleFile);
    fclose($handleFile);

    $pos = strpos($line, "\r\n");

    if ($pos !== false) {
        $res = "\r\n";
    } else {
        $res = "\n";
    }

    return $res;
}





public function intersectionCounting($countMin, $groups)
{
    $start = 0;
    $step = 300;
    $data = [];

    // 1. Проходим по всем группам и берем N значений
    foreach($groups as $key => $group)
    {
        $file = '../storage/app/tempfiles/' . $this->work['id'] . '_' . $group . '.txt';
        if (!file_exists($file)) continue;

        // Берем N значений ID
        $arr = $this->readFileToN($file, $start, $step);

        if (!$arr) { unset($groups[$key]); echo 'Группа '.$group.' пустая <br>'; continue; }

        $data[$group]['items'] = $arr;

        $data[$group]['last_row'] = 0;
        $data[$group]['last_id'] = array_pop($arr);
    }

    $this->setLog('Шаг 0. Взял по '.$step.' строк из '.count($groups).' групп');


    while(count($groups) > $countMin)
    {
        //  Находим группу с самым минимальным ID
        $min = $this->getMinId($data);
        $minIdGroup = $min[0];
        $minId = $min[1];

        //  Берем элементы до минимального ID
        $result = [];
        foreach($data as $key => $value)
        {
            $i = 0;
            foreach($value['items'] as $k => $val)
            {
                if ($val > $minId) break;
                unset($data[$key]['items'][$k]);
                $result[] = $val;
                $i++;
            }
            $data[$key]['last_row'] += $i;
        }

        // Подсчитываем
        $result = array_count_values($result);
        arsort($result);

        // Пишем найденные пересечения в файл
        foreach($result as $key => $value)
        {
            if ($value < $countMin) break;
            file_put_contents($this->file, $key . "\n", FILE_APPEND);
        }

        // Берем еще N значений в минимальной группе
        $file = '../storage/app/tempfiles/' . $this->work['id'] . '_' . $minIdGroup . '.txt';
        $arr = $this->getLines($file, $data[$minIdGroup]['last_row'], $step);

        // Если группа закончилась
        if (!$arr) {
            $groups = array_diff($groups, [$minIdGroup]);
            unset($data[$minIdGroup]);
            $this->setLog('Группа '. $minIdGroup . ' подсчитана');
            continue;
        }

        // Прибавляем N значений к минимальной группе
        $data[$minIdGroup]['items'] = array_merge($data[$minIdGroup]['items'], $arr);
        $data[$minIdGroup]['last_id'] = array_pop($arr);

    }

    $this->setLog('Алгоритм подсчета завершен');
}



public function readFileToN($file, $start, $step)
{
    $i = 0;
    $data = [];

    $line = '';
    $file = fopen($file, "r");
    $str = 0;

    for($i = 0; $i < $step; $i++)
    {
        $str = fgets($file);

        if ($i < $start) continue;

        if (!$str) {
            $arr = explode("\n", $line);
            $arr = array_diff($arr, ['', "\"\"\""]);
            return $arr;
        }

        $line .= $str;
    }

    fclose($file);


    $arr = explode("\n", $line);
    $arr = array_diff($arr, ['', "\"\"\""]);

    return $arr;
}




public function getMinId($data)
{
    $min = 99999999999;
    $res = '';

    foreach($data as $key => $value){

        if ($value['last_id'] < $min) {
            $min = $value['last_id'];
            $res = $key . ':' . $value['last_id'];
        }
    }

    $res = explode(':', $res);

    return $res;
}


public function getLines($file, $lineNumber, $step)
{
    $i = 0;
    $data = [];

    $line = '';
    $file = fopen($file, "r");
    $str = 0;

    $stop = $lineNumber + $step;

    for($i = 0; $i < $stop; $i++)
    {
        $str = fgets($file);

        if ($i < $lineNumber) continue;

        if (!$str) {
            $arr = explode("\n", $line);
            $arr = array_diff($arr, ['', "\"\"\""]);

            return $arr;
        }

        $line .= $str;
    }

    fclose($file);


    $arr = explode("\n", $line);
    $arr = array_diff($arr, ['', "\"\"\""]);

    return $arr;
}


}


