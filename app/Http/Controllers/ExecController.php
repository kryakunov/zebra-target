<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');



use App\mywork;
use App\stream;
use App\Http\Exec\Process;
use App\Functions;
use Illuminate\Http\Request;

class ExecController extends Controller
{
    public $streams;
    public $workId;
    public $execScriptName;
    public $membersCount = 0;
    public $parentId;
    public $workType;
    public $fromWork = null;
    public $request = null;

    public function setRequest($request)
    {
        
        unset($request['_token']);
        unset($request['groups']);
        unset($request['users']);
        unset($request['posts']);
        unset($request['works']);
        unset($request['lists']);
        unset($request['form']);

        $this->request = serialize($request);
    }

    public function getParentWork($parentId)
    {
        $work = mywork::where('id', '=', $parentId)->where('vk_id', '=', session('id'))->first();
        $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

        if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();



        // Читаем файл построчно
        $data = $this->readLine($file, 5);
        dd($data);

        $data = file_get_contents($file);
        dd($data);
        $handleFile = fopen($file, "r");
        $line = fgets($handleFile);
        fclose($handleFile);
        dd($line);
    }

    public function getType()
    {
        $type = null;

        if (isset($_GET['id'])) 
            $type = 'work';
        elseif(isset($_GET['cloud_id'])) 
            $type = 'cloud';

        return $type; 
    }

    public function readLine($file, $amount)
    {
        $i = 0;
        $data = [];
        $file = '../storage/app/' . $file;

        // Здесь находим какой разделитель в файле \n or \r\n
        $res = $this->checkRNnew($file);
  
        if ($file = fopen($file, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                $line = str_replace($res, "", $line);	
                $data[] = $line;
                // Если достигли лимита
                if (++$i >= $amount) 
                    break;
            }
            fclose($file);
            return $data;
        }
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
    
    public function createWork($name)
    {

        $work = mywork::create([
            'vk_id' => session('id'),
            'status' => 0,
            'percent' => 0,
            'name' => $name,
            'parent_id' => $this->parentId,
            'type_id' => $this->type,
            'date' => $this->time,
            'date_start' => $this->time,
            'streams' => $this->streams,
            'source_count' => $this->membersCount,
            'from_work' => $this->fromWork,
            'request' => $this->request,
        ]);

        $this->workId = $work['id'];

        return $work['id'];

    }

    public function createStream($streams)
    {
        for($i = 0; $i < $streams; $i++)
        {
            $stream = stream::create([
                'mywork_id' => $this->workId,
                'status' => 0,
                'percent' => 0,
                'stream_id' => $i,
                'pid' => 0,
               // 'streams' => $streams,
            ]);

            $this->createExec($stream['id']);
        }

    }
    
    public function createExec($id)
    {
        $logPath = 'logs/'. $this->workId . '-'. $id .'.txt ';

        $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Exec/'.$this->execScriptName.'.php '.escapeshellarg($id);

        $process = new Process($command, $logPath);
        $pid = $process->getPid();
        stream::where('id', '=', $id)->update(['pid' => $pid]);
    }
}
