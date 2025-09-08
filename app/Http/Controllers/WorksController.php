<?php

namespace App\Http\Controllers;

use App\sample_work;
use App\mywork;
use App\Sample;
use App\stream;
use App\WorkType;
use App\Cloud;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Http\Exec\Process;
use App\Functions;
use Illuminate\Http\Request;

class WorksController extends ExecController
{

    public $time;

    public function __construct()
    {
        $this->time = time();
    }

    public function getMyWorks($page)
    {

        $works = mywork::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        $works = $works->filter(function ($value, $key ) use ($page){
            return $value->WorkType->type_desc == $page->type_input;
          });

        return $works;
    }

    public function getMyLists($page)
    {

        $lists = Cloud::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        $lists = $lists->filter(function ($value, $key ) use ($page){
            return $value->WorkType->type_desc == $page->type_input;
          });

        return $lists;
    }

    public function show()
    {
        // Определяем текущий uri
        $route = Route::current()->uri();

        // Вытаскиваем из таблицы данные для формирования страницы
        $page = WorkType::where('type', '=', $route)->first();

        if (!$page) return 404;

        // Смотрим есть ли исходные данные "из задачи"
        $work = $this->getWork();

        if($work)
            return view('forms.layout', ['page' => $page, 'work' => $work]);

        $works = $this->getMyWorks($page);

        $lists = $this->getMyLists($page);

        return view('forms.layout', ['page' => $page, 'work' => $work, 'works' => $works, 'lists' => $lists]);
    }


    public function handler(Request $request, $route = null, $fromChain = null)
    {
        // Определяем текущий uri
        if (!$route)
            $route = Route::current()->uri();

        // Вытаскиваем из таблицы данные для формирования страницы
        $page = WorkType::where('type', '=', $route)->first();

        $this->type = $page->id;
        $this->execScriptName = 'exec' . $page->type;

        $this->parentId = $request->get('parentId');

        // Смотрим откуда пришли данные
        if ($request->data_from == 'form')
        {
            $this->validate($request, ['form' => 'required']);
            $data = explode("\r\n", $request->form);
            $data = array_unique($data);

            if ($page->type_input == 'users') {
                $data = Functions::clearUserName($data);
                if (count($data) < 300) {
                    $data = Functions::getUserIds($data);
                }
            }

            elseif($page->type_input == 'groups') {
               $data = Functions::clearGroupName($data);
                $count = count($data);
               if ($count < 25) {
                $ids = implode(',', $data);
                $data = json_decode(file_get_contents('https://api.vk.ru/method/execute.getGroupsId?ids='.$ids.'&count='.$count.'&access_token='.session('token').'&v=5.131'), true);
                if (!$data['response']) return redirect()->back()->with('error', 'Проверьте правильность введенных ID')->withInput();
                $data = $data['response'];
               }
            }

        } elseif ($request->data_from == 'works')
        {
            $data = mywork::where('vk_id', '=', session('id'))->where('id', '=', $request->works)->first();
            if (!$data) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();
            $file = 'works/' . $data['vk_id'] . '_' . $data['date'] . '.txt';


        } elseif ($request->data_from == 'lists')
        {
            $data = Cloud::where('vk_id', '=', session('id'))->where('id', '=', $request->lists)->first();
            if (!$data) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();
            $file = 'cloud/' . $data['vk_id'] . '_' . $data['track_id'] . '.txt';

        }

        // Если данные пришли из родительской задачи
        if ($this->parentId)
        {
            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();

            // Для расшаренных задач
            if (!$work) {
                $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
                $this->parentId = null;
            }

            $file = 'works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            // Если это моя задача и у меня бесплатный доступ, то обрезать результат
            if ($work->vk_id == session('id'))
            if (!Functions::isFullAccess())
            {
                if ($page->type_desc == 'users')
                    $data = $this->readLine($file, 50);
                elseif($page->type_desc == 'groups')
                    $data = $this->readLine($file, 15);
                elseif($page->type_desc == 'posts')
                    $data = $this->readLine($file, 15);
            }
        }

            $newFile = 'sourceworks/'.session('id').'_'.$this->time.'.txt';

            // Переносим данные из исходной задачи в новый файл
            if (isset($file))
                Storage::disk('local')->copy($file, $newFile);
            elseif(!isset($file) && isset($data)) {
                $data = implode("\n", $data);
                Storage::disk('local')->put($newFile, $data);
            }

            $this->setRequest($request->all());

            $this->sourceFile = session('id') . '_' . $this->time . '.txt';
            $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

            $this->streams = 1;//count($groups);

            ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | '.$page->type_name;

            // Создаем очередь
            $work = $this->createWork($name);

            if ($fromChain === true) {
                return $this->workId;
            }

            // Создаем потоки
            $this->createStream($this->streams);

            return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');

    }


    public function WorkRun($id)
    {
        if(!$id) dd("not found ID");
        $work = mywork::where('id', '=', $id)->first();

        $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

        $dateStart = time();

        $unlink = false;
        foreach($work->stream as $val){
            if($val->last_id != null) $unlink = true;
           // $dateStart = $work->date_start;
        }

        if (file_exists($file) && !$unlink) { unlink($file); }


        $file = '../storage/app/sourceworks/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $data = file_get_contents($file);

        $streams = stream::where('mywork_id', '=', $id)->get()->toArray();

        mywork::where('id', '=', $id)->update([
            'date_start' => $dateStart,
            'status' => 0,
            'percent' => 1,
            'error' => null,
        ]);

        // Запускаем потоки
        foreach($streams as $stream)
        {
            $logs = 'logs/'. $work->id . '-'. $id .'.txt ';

            $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Exec/exec'.$work->WorkType->type.'.php '.escapeshellarg($stream['id']);
            $process = new Process($command, $logs);
            $pid = $process->getPid();

            stream::where('id', '=', $stream['id'])->update([
                'pid' => $pid,
                'percent' => 1,
                'error' => null,
                'status' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Успешно');
    }


    public function SampleRunCron()
    {
        $samples = sample_work::where('percent', '>', 0)->where('percent', '<', 100)->get();

        foreach($samples as $sample)
        {
            $status = Process::checkStatusById($sample->pid);

            if($status) continue;

            if (!isset($sample->data_from)) continue;


            $logs = 'logs/sample-id-'. $sample->id . '.txt ';

            $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Execute/'.$sample->WorkType->type.'.php '.escapeshellarg($sample->id);
            $process = new Process($command, $logs);
            $pid = $process->getPid();

            sample_work::where('id', '=', $sample->id)->update([
                'pid' => $pid,
            ]);

            echo 'Шаблон ' . $sample->name . ' запущен. pid: ' . $pid . '<br>';
        }

        echo 'end';
    }

    public function SampleRun($id)
    {


        $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $sample->update(['state' => 'Выполняется', 'date_start' => time(), 'date_end' => null]);

        //if(time() - $sample->date_update < 3600)
            //return redirect()->back()->with('error', 'Нельзя запускать шаблон чаще чем 1 раз в час');

        // Запускаем первую задачу из цепочки, которая сама запустит остальные
        $work = $sample->getWorks->first();

        if (!isset($work->data_from))
            return redirect()->back()->with('error', 'Укажите исходные данные для шаблона');

        // Меняем статус у всех задач на "ожидает запуска"
        foreach($sample->getWorks as $work){
            $work->update(['status' => 'wait', 'state' => 'Ожидает запуска', 'percent' => 0, 'error' => null, 'count' => 0, 'date_start' => null, 'date_end' => null]);
        }

        $work = $sample->getWorks->first();

        $logs = 'logs/semyon.txt ';

        $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Execute/'.$work->WorkType->type.'.php '.escapeshellarg($work['id']);
        $process = new Process($command, $logs);
        $pid = $process->getPid();

        sample_work::where('id', '=', $work['id'])->update([
            'pid' => $pid,
            'percent' => 1,
            'error' => null,
        ]);

        return redirect()->back()->with('success', 'Шаблон запущен');
    }

    public function SaveWorkTimer(Request $request, $id)
    {
        if (isset($request->timer)) $status = 1; else $status = 0;

        mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->update([
            'timer' => $status,
            'timer_count' => $request->timer_count,
        ]);

        return redirect()->back()->with('success', 'Успешно');
    }

    // Эта функция запускает задачи по крону
    public function cronStartWorks()
    {
        $works = mywork::where('timer', '=', 1)->get();

        $file = '../storage/app/cronworkslog/cronstartworks.txt';
        if (!file_exists($file)) file_put_contents($file, '');

        foreach($works as $work)
        {
            // Проверяем на полный доступ
            if($work->user->access < time()) continue;

            // Сколько времени прошло с момента последнего запуска
            $passedTime = time() - $work->date_start;

            $days = 86400 * $work->timer_count;

            // проверяем, прошло ли n суток
            if ($passedTime < $days) continue;

            $this->workRun($work->id);

            // Пишем лог
            $msg = '[' . date('d.m.y H:i', time()) . '] Задача '. $work->id . ' запущена';

            file_put_contents($file, $msg . "\n", FILE_APPEND);

        }


        $log = file_get_contents($file);

        dd($log);
    }
}
