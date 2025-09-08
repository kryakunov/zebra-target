<?php

require 'db.php';

require 'Exec.php';

class execTest extends Exec
{

    public function set()
    {
        $token = $this->getToken();
        file_put_contents('exec.txt', $token);
    }

}


$class = new execTest();

$class->set();
