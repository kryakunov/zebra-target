<?php

namespace App\Http\Controllers;

use App\Http\Exec\Process;
use App\stream;
use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

use App\Jobs\GetMembers;

class GetMembersController extends Controller
{

    public $pause = 0;
    public $items = [];
    public $exceptions = [];
    public $workId;
    public $min;
    public $max;
    public $membersCount;
    public $workName;
    public $request;

    public $streams = 1;

    public function show()
    {
        return view('getmembers');
    }

    public function showWork($id)
    {
        $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $request = unserialize($data->request);
        //dd($request);
        $file = $_SERVER["DOCUMENT_ROOT"] . 'storage/app/'.$data->type.'/'.$data->vk_id.'__'.$data->date.'.txt';
        $data = file_get_contents($file);
        $data = explode("\n", $data);
        //$data = unserialize($file);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Нет данных');
        }

        return view('workgetmembers', ['data' => $data, 'request' => $request]);
    }


    public function pause()
    {
        if ($this->pause >= 2) {
            sleep(1);
            $this->pause = 0;
        }
    }

    public function handler(Request $request)
    {

        $this->validate($request, ['groups' => 'required']);

        $this->request = $request->all();
        unset($this->request['_token']);
        $this->request = serialize($this->request);


        $this->min = ($request->input('min') !== null) ? $request->input('min') : 0;
        $this->max = ($request->input('max') !== null) ? $request->input('max') : 999;

        $this->workName = ($request->input('name') !== null) ? $request->input('name') : 'Сбор участников сообществ';


        $groups = explode("\r\n", $request->get('groups'));
        $groups = Functions::clearGroupName($groups);

        $this->membersCount = $this->howManyMembers($groups);

        if($this->membersCount == 0) {
            return redirect()->back()->with('error', 'Сообществ не найдено')->withInput();
        }

        if ($this->membersCount > 100000) {
            $this->streams = round($this->membersCount / 100000);
        }


        if ($this->membersCount > 3000000) {
            return redirect()->route('getmembers')->with('error', 'Суммарное кол-во подписчиков групп не должно превышать 3 000 000')->withInput($request->all());
            die;
        }


        $this->createExec();

        return redirect()->route('profile')->with('success', 'Задача успешно добавлена в работу');

        /*


        $this->parse($groups);

        $this->items = collect($this->items)->countBy();

        $this->items = $this->items->filter(function($value, $key) use ($min, $max) {
            return $value >= $min and $value <= $max;
        })->map(function($value, $key){
			return $key;
		})->toArray();


		if (!Functions::isFullAccess()){
			session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только первые 100 значений из ' . count($this->items));
			$this->items = array_slice($this->items, 0, 100);
		}

        return view('getmembers', ['items' => $this->items, 'exceptions' => $this->exceptions, 'request' => $request->all()]);
        */
    }



    public function createExec()
    {
        $count = count($this->groupIds);
        $groups = implode("\r\n", $this->groupIds);
        $time = time();
        $file = session('id') . '_' . $time . '.txt';
        $path = '../storage/app/getmembers/' . $file;

        file_put_contents($path, $groups);

        $work = mywork::create([
            'vk_id' => session('id'),
            'status' => 0,
            'percent' => 0,
            'name' => $this->workName,
            'type' => 'getmembers',
            'date' => $time,
            'count' => $this->membersCount,
            'pid' => 0,
            'request' => $this->request,
        ]);

        $this->workId = $work['id'];

        $this->createStream($this->streams);

        mywork::where('id', '=', $work['id'])->update(['streams' => $this->streams]);

        session(['success' => 'Задача успешно добавлена в работу']);

        return redirect()->route('profile')->with('success', 'Задача успешно добавлена в работу');


/*


        $command = 'php ../app/Http/Exec/execGetMembers.php '.escapeshellarg($work['id']).' '.escapeshellarg($this->min).' '.escapeshellarg($this->max);
        $process = new Process($command);
        $pid = $process->getPid();

        mywork::where('id', '=', $work['id'])->update(['pid' => $pid]);

        return true;*/
    }



    public function parse($groups)
    {
        foreach($groups as $group)
        {
            if (empty($group)) continue;

            $result = Functions::getMembersGroup($group);

            if(!empty($result['exceptions'])) {
                $this->exceptions[] = $result['exceptions'][0];
            }

            $this->items = array_merge($result['items'], $this->items);

            $this->pause();
        }
    }

    public function howManyMembers($groups)
    {
        $ids = implode(",", $groups);
        $result = json_decode(file_get_contents('https://api.vk.ru/method/groups.getById?v=5.126&fields=members_count,type&group_ids='.$ids.'&access_token='.session('token')), true);

        $membersCount = 0;

        if (isset($result['response'][0]['members_count'])) {
            foreach ($result['response'] as $value){
                $membersCount += $value['members_count'];
                $this->groupIds[] = $value['id'];
            }
        } else return 0;

        return $membersCount;
    }


    public function createStream()
    {
        for($i = 0; $i < $this->streams; $i++)
        {
            $stream = stream::create([
                'mywork_id' => $this->workId,
                'status' => 0,
                'percent' => 0,
                'stream_id' => $i,
                'pid' => 0,
                'offset' => $this->streams,
            ]);

            $command = 'php ../app/Http/Exec/execGetMembersOffset.php '.escapeshellarg($stream['id']).' '.escapeshellarg($this->min).' '.escapeshellarg($this->max);
            $process = new Process($command.' > rrr.txt');
            $pid = $process->getPid();
            stream::where('id', '=', $stream['id'])->update(['pid' => $pid]);
            //sleep(1);
        }

    }
}
