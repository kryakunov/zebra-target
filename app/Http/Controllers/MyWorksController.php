<?php

namespace App\Http\Controllers;

use App\Token;
use App\Functions;
use App\Cloud;
use App\stream;
use App\Sample;
use App\mywork;
use App\User;
use App\Http\Exec\Process;
use Illuminate\Http\Request;

class MyWorksController extends Controller
{

    const FILE = 'works';
    const SOURCEFILE = 'sourceworks';

    public $file;
    public $sourcefile;

    public function index()
    {
        $works = mywork::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();
        session()->forget('chain');


        return view('myworks.index', ['data' => $works]);
    }

    public function clearWorks()
    {
        $time = time() - 2505600; // Месяц назад

        $works = mywork::where('date_start', '<', $time)->get();
        $i = 0;

        foreach($works as $work){

            $work = mywork::where('id', '=', $work['id'])->first();

            $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
            $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            // Удаляем файлы
            if(file_exists($file)) unlink($file);
            if(file_exists($sourceFile)) unlink($sourceFile);

            // Удаляем streams
            stream::where('mywork_id', '=', $work['id'])->delete();

            // Удаляем саму задачу
            $work->delete();

            $i++;
        }

        echo 'Удалено ' . $i . ' задач';
    }

    public function ajax()
    {
        $id = session('id');
        $work = mywork::where('vk_id', '=', $id)->where('status', '=', '0')->get()->toArray();
        $work2 = mywork::where('vk_id', '=', $id)->where('status', '=', '1')->orderBy('id', 'DESC')->first();

        if (isset($work2))
            $work[] = $work2;

        if (!$work) return null;

        foreach($work as &$value){

            if ($value['percent'] == 100) {
                $value['live'] = false;
                continue;
            }
            $value['live'] = mywork::checkStatusWork($value['id']);
        }


        return json_encode($work);
    }


    public function ajaxSamples()
    {
        $sample = sample::where('vk_id', '=', session('id'))->where('status','=','inwork')->get();
      dd($sample);
        if (!$works) return null;

        return json_encode($works);
    }


    public function ajaxleft()
    {

        $works = mywork::select('id', 'percent')->where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->take(3)->get()->toArray();


        if (!$works) return null;
        return json_encode($works);
    }

    public function ajaxgetworks()
    {
        $id = session('id');
        $works = mywork::select('name')->where('vk_id', '=', $id)->where('status', '=', '1')->get()->toArray();

        if (!$works) return null;
        return json_encode($works);
    }


    public function ajaxgetuser($id)
    {
        $id = explode(' ', $id);
        $user = User::select('id', 'first_name', 'last_name', 'photo')
            ->where('first_name', '=', $id[0])
            ->where('last_name', '=', $id[1])
            ->first()
            ->toArray();

       //$user = User::select('vk_id', 'photo', 'first_name', 'last_name')->where('vk_id', '=', $id)->first();

       $arr = ['first_name' => 'Не найдено'];
       if (!$user) return json_encode($arr);

        return json_encode($user);
    }


    public function ajaxgetcloud()
    {
        $id = $_GET['id'];
        $works = Cloud::select('name', 'id')->where('vk_id', '=', $id )->get()->toArray();

        if (!$works) return null;
        return json_encode($works);
    }

    public function ajaxgetcity()
    {
        $city = $_GET['city'];
        $id = session('id');



        $request_params = array(
            'v'            => '5.126',
            'q'       => $city,
            'access_token' => session('token'),
        );

        $params = http_build_query($request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/database.getCities?' . $params), true);
$result = $result['response']['items'];

        return json_encode($result);

        $works = mywork::select('name')->where('vk_id', '=', $id)->where('status', '=', '1')->get()->toArray();

        if (!$works) return null;
        return json_encode($works);
    }


    public function ajaxgetcountries()
    {
var_dump(session('token'));
        $request_params = array(
            'v'            => '5.131',
            'need_all'       => 1,
            'code' => 'RU',
            'access_token' => session('token'),
            'count' => 100,
        );

        $params = http_build_query($request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/database.getCountries?' . $params), true);
        $result = $result['response']['items'];
dd($result);
        return json_encode($result);

        $works = mywork::select('name')->where('vk_id', '=', $id)->where('status', '=', '1')->get()->toArray();

        if (!$works) return null;
        return json_encode($works);
    }


    public function download($id)
    {
        $work = mywork::where('id', '=', $id)->first();
        if (!$work) return redirect()->back()->with('error', 'Данные не найдены');

        // Если это моя задача и у меня бесплатный доступ, то обрезать результат
        if ($work->vk_id == session('id'))
        if (!Functions::isFullAccess()) return redirect()->back()->with('pay', 'Эта функция недоступна на бесплатном доступе ');

        if($work->share == null  && $work->vk_id != session('id')) return redirect()->back()->with('error', 'Нет доступа');

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $name = $work->name . '.txt';

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        // Если это сбор соц. сетей
        if ($work->WorkType->id == 11)
        {
            $data = unserialize(file_get_contents($file));
            $file = '../storage/app/tempfiles/'.$work['vk_id'].'_'.time() . '.txt';

            foreach($data as $key => $value){
                $value = implode("\n", $value);
                file_put_contents($file, "\n----------\n".$key.":\n----------\n\n".$value."\n", FILE_APPEND);
            }

            return response()->download($file, $name);
            unlink($file); // Не успевает удалить :(
        }

        // Если файл сериализован
        $line = fgets(fopen($file, 'r'));
        if (strlen($line) > 20)
        {
            $data = unserialize(file_get_contents($file));
            $this->checkAccess($work, $data);
            $file = '../storage/app/tempfiles/'.$work['vk_id'].'_'.time() . '.txt';

            foreach($data as $key => $value){
                file_put_contents($file, $key."\n", FILE_APPEND);
            }

            return response()->download($file, $name);
            unlink($file); // Не успевает удалить :(
        }

        return response()->download($file, $name);
    }

    public function sharework(Request $request, $id)
    {
        if (isset($request['share']))
        {
            mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->update(['share' => 1]);
       } else {
            mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->update(['share' => null]);
       }

       return redirect()->route('myworks')->with('success', 'Изменения сохранены');

    }

    public function sharechain(Request $request, $id)
    {
        if (isset($request['share_parent']))
        {
            $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->update(['share_parent' => 1, 'share' => 1]);
            $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
            $parentId = $work->parent_id;


            // Собираем предыдущих родителей
            do {
                $work = mywork::where('id', '=', $parentId)->first();
                $parentId = $work->parent_id;
                mywork::where('vk_id', '=', session('id'))->where('id', '=', $work->id)->update(['share' => 1]);
            } while($work->parent_id !== null);

           /* $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
            $parentId = $work->parent_id;
            echo $id . '<br>';
            // Собираем предыдущих родителей
            do {
                $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $parentId)->first();
                $parentId = $work->parent_id;
                $i = mywork::where('id', '=', $parentId)->update(['share' => 1]);
                var_dump($parentId, $i);
            } while($work->parent_id !== null);
            die;*/
        } else {
            mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->update(['share_parent' => null, 'share' => null]);

            $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
            $parentId = $work->parent_id;

            // Собираем предыдущих родителей
            do {
                $work = mywork::where('id', '=', $parentId)->first();
                $parentId = $work->parent_id;
                mywork::where('vk_id', '=', session('id'))->where('id', '=', $work->id)->update(['share' => null]);
            } while($work->parent_id !== null);

       }


       return redirect()->route('myworks')->with('success', 'Изменения сохранены');

    }

    public function getChainShare($id)
    {
        $work = mywork::where('id', '=', $id)->where('share', '=', 1)->where('share_parent', '=', 1)->first();

        if(!$work) {
            session(['error' => 'Ветка не найдена или закрыта для общего просмотра']);
            session()->forget('chain');
            return view('myworks.share', ['data' => []]);
        }

        $name = $work->name;

        // Прибавляем к задаче просмотр
        $views = $work->views += 1;
        mywork::where('id', '=', $id)->where('share', '=', 1)->update(['views' => $views]);

        // Находим цепочку задач
        $worksChain = [];
        $parentId = $work->parent_id;
        $worksChain[] = $work;

        // Собираем предыдущих родителей
        do {
            $work = mywork::where('id', '=', $parentId)->first();
            $parentId = $work->parent_id;
            array_push($worksChain, $work);
        } while($work->parent_id !== null);


        session(['chain' => 'Показана цепочка заданий для задачи: '.$name]);
        asort($worksChain);
        return view('myworks.share', ['data' => $worksChain]);
    }

    public function getWorkChain($id)
    {
        $worksChain = [];

        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $name = $work->name;
        if (!$work) return redirect()->route('myworks')->with('error', 'Не найдено');
        $parentId = $work->parent_id;
        $worksChain[] = $work;

        // Сперва находим все предыдущие задачи
        do {
            $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $parentId)->first();
            if (!$work) break;
            $parentId = $work->parent_id;
            array_push($worksChain, $work);

        } while($work->parent_id !== null);

        $lastItem = $worksChain[0]['id'];

        $worksChain = array_reverse($worksChain);

        // Потом последующие
       /* do {

            $work = mywork::where('vk_id', '=', session('id'))->where('parent_id', '=', $id)->first();
            if (!$work) break;
            $id = $work->id;
            array_unshift($worksChain, $work);

        } while($work); */


        session([
            'chain' => 'Показана цепочка заданий для задачи: '.$name,
            'chainId' => $lastItem
        ]);

        return view('myworks.index', ['data' => $worksChain]);
    }

    public function works($id)
    {
        $work = mywork::where('id', '=', $id)->where('share', '=', 1)->first();
        session()->forget('chain');

        if(!$work) {
            session(['error' => 'Задача не найдена или закрыта для общего просмотра']);

            return view('myworks.share', ['data' => []]);
        }

        // Прибавляем к задаче просмотр
        $views = $work->views += 1;
        mywork::where('id', '=', $id)->where('share', '=', 1)->update(['views' => $views]);
        $work = mywork::where('id', '=', $id)->where('share', '=', 1)->get();

        return view('myworks.share', ['data' => $work]);
    }

    public function getWorkAllMembers($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = explode("\n", $data);

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только 50 пользователей из ' . count($data));
            $data = array_slice($data, 0, 50);
        }

       // $users = explode("\n", $request['users']);

        return view('myworks.getusers', [
            'data' => $data,
           // 'sourceData' => $sourceData,
            'request' => $request,
          //  'users' => $users,
           // 'name' => $work['name'],
            'work' => $work,
        ]);
    }


    public function getWorkUsersFilter($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);
        $sourceData = explode("\r\n", $request['users']);
        unset($request['users']);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = explode("\r\n", $data);

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только 50 пользователей из ' . count($data));
            $data = array_slice($data, 0, 50);
        }

        return view('myworks.usersfilter', [
            'data' => $data,
            'sourceData' => $sourceData,
            'request' => $request,
            'work' => $work,
        ]);
    }

    public function getWorkLiders($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);
        //$sourceData = explode("\r\n", $request['users']);
        unset($request['users']);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = unserialize($data);

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только 10 пользователей из ' . count($data));
            $data = array_slice($data, 0, 10);
        }

        return view('myworks.getworkliders', [
            'data' => $data,
            //'sourceData' => $sourceData,
            'request' => $request,
            'work' => $work,
        ]);
    }

    public function getWorkUsersGroups($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);
        //$sourceData = explode("\r\n", $request['users']);
        unset($request['users']);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = unserialize($data);

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только 10 сообществ из ' . count($data));
            $data = array_slice($data, 0, 10);
        }

        return view('myworks.getworkusersgroups', [
            'data' => $data,
            //'sourceData' => $sourceData,
            'request' => $request,
            'work' => $work,
        ]);
    }


    public function getWorkSocialNetworks($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);
        //$sourceData = explode("\r\n", $request['users']);
        unset($request['users']);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = unserialize($data);

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только 5 контактов из ' . $work->count);
            if(isset($data['instagram'])) $data['instagram'] = array_slice($data['instagram'], 0, 5);
            if(isset($data['facebook'])) $data['facebook'] = array_slice($data['facebook'], 0, 5);
            if(isset($data['skype'])) $data['skype'] = array_slice($data['skype'], 0, 5);
            if(isset($data['twitter'])) $data['twitter'] = array_slice($data['twitter'], 0, 5);
        }

        return view('myworks.getworksocialnetworks', [
            'data' => $data,
            //'sourceData' => $sourceData,
            'request' => $request,
            'work' => $work,
        ]);
    }

    public function getWorkShare($id)
    {
        $work = mywork::where('share', '=', '1')->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = explode("\n", $data);

        return view('myworks.searchgroups', [
            'data' => $data,
            'request' => $request,
            'work' => $work,

        ]);
    }

    public function getWorkSearchGroups($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $request = file_get_contents($sourceFile);
        $request = unserialize($request);

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);
        $data = explode("\n", $data);

        if (!Functions::isFullAccess()){
            session()->flash('pay', 'У вас бесплатный доступ. Вам доступны только 10 групп из ' . count($data));
            $data = array_slice($data, 0, 10);
        }

        return view('myworks.searchgroups', [
            'data' => $data,
            'request' => $request,
            'work' => $work,
        ]);
    }

    public function moveWork($id, $scriptName)
    {
/*
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';

        if (!file_exists($file)) return redirect()->back()->with('error', 'Данные не найдены');

        $data = file_get_contents($file);


        $request['users'] = $data;

       // return redirect()->route($scriptName)->with(['id' => $id]);
        return view($scriptName, ['request' => $request]);
  */
    }

    public function delete($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        if (!$work) dd('not found');

        $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $logFile = '../storage/app/logs/'.$work['vk_id'].'_'.$work['date'] . '.txt';

        // Удаляем файлы
        if(file_exists($file)) unlink($file);
        if(file_exists($sourceFile)) unlink($sourceFile);
        if(file_exists($logFile)) unlink($logFile);

        // Удаляем streams
        stream::where('mywork_id', '=', $work['id'])->delete();

        // Удаляем саму задачу
        $work->delete();

        return redirect()->route('myworks')->with('success', 'Успешно удалено');
    }


    public function deleteallworks()
    {

        $works = mywork::where('vk_id', '=', session('id'))->get();

        if (!$works) dd('not found');

        foreach($works as $work)
        {
            $file = '../storage/app/'.self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
            $sourceFile = '../storage/app/'.self::SOURCEFILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
            $logFile = '../storage/app/logs/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            // Удаляем файлы
            if(file_exists($file)) unlink($file);
            if(file_exists($sourceFile)) unlink($sourceFile);
            if(file_exists($logFile)) unlink($logFile);

            // Удаляем streams
            stream::where('mywork_id', '=', $work['id'])->delete();

            // Удаляем саму задачу
            $work->delete();
        }

        return redirect()->route('myworks')->with('success', 'Успешно удалено');
    }

    public function kill($id)
    {
        $item = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        foreach($item->stream->toArray() as $value)
        {
            $pid = $value['pid'];
            $tokenId = $value['token_id'];

           // Token::where('id', '=', $tokenId)->update(['status' => 'free']);
            Process::kill($pid);
        }

        mywork::where('id', '=', $id)->update(['status' => 9]);

        return redirect()->route('myworks')->with('success', 'Остановлено');

    }
}
