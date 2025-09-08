<?php

namespace App\Http\Exec;

ini_set('max_execution_time', 0);

//$id = $argv[1];
require '/home/host1380688/zebra-target.ru/htdocs/www/app/Http/Exec/Exec.php';

use App\Token;
use \App\Http\Exec\Exec;

class OpinionLiders extends Exec
{
    public $pdo;
    public $file;
    public $id;

    public function say()
    {
        $file  = __FILE__ . "\r\n" . __NAMESPACE__;
        file_put_contents('executable2.txt',  $file);
    }

}

$class = new OpinionLiders();
$class->test();
$class->say();