<?php



ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$token = 'vk1.a.kKJ3-j7NfOIp7M3aRuC-QRquQoo_TUFuyqP0CJs8-Ga8XBs41ggCYWO8ltK7NB7njAGVSlYLWqzdyL35Is-T1W2J850ot1DgWNH0mIhb9dc-x_7dUW4XWkyhN4u5wwRakxd1O5DmUObsZcj1NkEPEzH3rU88Ff0KwpKIC9mDOo1FPd5O4pD30mN0qgpXcdq63xIeOuiHNszFqpgWQPbZcA';

$request_params = array(
    'v'            => '5.131',
    'owner_id'     => '-131101936',
    'offset'       => 0,
    'count'        => 100,
   // 'ids'          => $ids,
    'access_token' => $token,
);


$params = http_build_query($request_params);



$n = 0;
$error = 0;
for($i = 0; $i < 5000; $i++)
{
    $result = json_decode(file_get_contents('https://api.vk.com/method/execute.wallGet?' . $params), true);

    sleep(2);

    if (isset($result['response'])) {
        $count = count($result['response']);
        $str = 'i: ' . $i . '; count: ' . $count . "\n";
    } else {
        $count = 0;
        $error++;
        $str = 'i: ' . $i . '; ' . serialize($result)  . "\n";
        if($error > 10) die;
    }


    file_put_contents('procedure8.txt', $str, FILE_APPEND);
}



file_put_contents('procedure8.txt', ' USE ', FILE_APPEND);
