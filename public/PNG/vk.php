<?php
session_start();


if (!$_GET['code']) {
	exit('error code');
}

include 'config.php';

// Запрашиваем токен
$data = json_decode(file_get_contents('https://oauth.vk.ru/access_token?client_id='.ID.'&client_secret='.SECRET.'&redirect_uri='.URL.'&v=5.101&client_secret='.SECRET.'&code='.$_GET['code']), true);



if (!$data) {
	exit('error token');
}


$_SESSION['token']   = $data['access_token'];
$_SESSION['user_id'] = $data['user_id'];


// Запрашиваем основную информацию о юзере
$url  = 'https://api.vk.ru/method/users.get?user_id='.$data['user_id'].'&v=5.101&access_token='.$data['access_token'].'&fields=uid,first_name,last_name,photo_50';
$data = json_decode(file_get_contents($url),true);
$data = $data['response'][0];


// Записываем имя юзера в сессию
$_SESSION['first_name'] =  $data["first_name"];
$_SESSION['last_name']  =  $data["last_name"];
$_SESSION['photo']      = $data['photo_50'];

 // Записываем в файл его ИД
$str = '---> oath. id: ' . $_SESSION['user_id'] . ' > ' . date("d.m.y") . PHP_EOL;
$fd = fopen("log.txt", 'a+');
fwrite($fd, $str);
fclose($fd);


 				$filename = "users/".$_SESSION['user_id'].".txt";

 				// Если файл уже существует, тогда считываем с него данные
                if (file_exists($filename) == true ) {

                $_SESSION['access'] = file_get_contents($filename);

                } else {

                $_SESSION['access'] = '0';

				// А иначе создаем этот файл
				$str = '0';
				$put = "users/".$_SESSION['user_id'].".txt";
				$fd = fopen($put, 'w+');
				fwrite($fd, $str);
				fclose($fd);


				}


     if (isset($data)) {
         header('Location: http://zebra-target.ru');
     }


?>
