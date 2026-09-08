<?php

namespace App\Jobs;

use App\Functions;
use App\Jobs\BaseJob;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetMembers extends BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $access_token;
    public $request;

    public function __construct($request)
    {

echo 'fdf'; sleep(3); echo 'free';
       // $this->access_token = $this->getToken();

        //$this->request = $request;
        //$this->parse();

    }


    public function handle()
    {

        file_put_contents('file.txt', 'fd'); sleep(5); die;
    }

    public function parse()
    {
        $min = ($this->request->input('min') !== null) ? $this->request->input('min') : 0;
        $max = ($this->request->input('max') !== null) ? $this->request->input('max') : 999;

        $groups = explode("\r\n", $this->request->get('groups'));
        $groups = Functions::clearGroupName($groups);

        $data = array();
        $i = 0;
        $n = 24;
        $offsetDo = 10000;
        $offset = 1000;
        do{

           // $this->setPercentInMyworkTable();

            // Вычисляем count, чтобы парсил ровное число пользователей
            if (($offsetDo - $offset) < 25000)
            {
                $count = $offsetDo - $offset;
                if ($count > 1000){
                    $n = round($count / 1000);
                    $n++;
                    $count = $count % 1000;
                }else{
                    $n = 1;
                }
            } else
                $count = 1000;

            // Парсим
            $result = json_decode(file_get_contents('https://api.vk.ru/method/execute.getMembers?group_id='.$group.'&n='.$n.'&offset='.$offset.'&count='.$count.'&v=5.131&access_token='.$this->access_token), true);
dd($result);
            if ($i >= 3) { $i = 0; sleep(1); }

            // В случае ошибки
            if (isset($result['error']))
            {
                $error = $result['error']['error_code'] . ' > ' .$result['error']['error_msg'];
                $this->setError($error);

                if ($result['error']['error_code'] == 6 or $result['error']['error_code'] == 29 or $result['error']['error_code'] == 5)
                {
                    if ($this->getToken())
                    {
                        $this->access_token = $this->getToken();
                    } else
                    {
                        $this->setError('End tokens');

                        die;
                    }
                }
                continue;
            }

            if(!isset($result['response'])) continue;

            $offset += 25000;

            $this->loading += 10000;
            $percent = round(($this->loading / $this->countMembers) * 100);
            $this->setPercent($percent);

            $data = implode("\n", $result['response'])."\n";

            file_put_contents($this->file, $data, FILE_APPEND);

        }while($offset <= $offsetDo);

        file_put_contents('otchet.txt', "group: $group, offset: $offset, offsetDo: $offsetDo, count: $cc \n", FILE_APPEND);

    }
}
