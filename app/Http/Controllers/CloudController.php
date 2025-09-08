<?php

namespace App\Http\Controllers;

use App\mywork;
use App\Functions;
use App\Cloud;
use App\Sample;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CloudController extends Controller
{

    const FILE = 'works'; // Папки где хранятся выполненные задачи (которые будем сохранять в облако)
    const SAMPLE = 'samples'; // Папки где хранятся выполненные шаблоны (которые будем сохранять в облако)

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Cloud::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        return view('cloud.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (session('access') < time()) return redirect()->route('cloud.index')->with('pay', 'Вам недоступна эта функция');

        return view('cloud.create');
    }

    // Сохранение выполненный задачи
    public function workStore($id)
    {
        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        if (session('access') < time()) return redirect()->route('cloud.index')->with('pay', 'Вам недоступна эта функция');

        switch (session('package')) {
            case null:
                return redirect()->route('cloud.index')->with('error', 'Вам недоступна эта функция');
                break;
            case 1:
                $max_added = 5;
                break;
            case 2:
                $max_added = 10;
                break;
            case 3:
                $max_added = 20;
                break;
            case 4:
                $max_added = 50;
                break;
        }

        $lists = Cloud::where('vk_id', '=', session('id'))->count();

        if ($lists >= $max_added) {
            return redirect()->route('cloud.index')->withInput()->with('pay', 'В вашем тарифе вы хранить максимум '.$max_added.' списков.');
            die;
        }

        $track_id = time();
        $workFile = self::FILE.'/'.$work['vk_id'].'_'.$work['date'] . '.txt';
        $cloudFile = 'cloud/' . session('id') . '_' . $track_id . '.txt';

        Storage::disk('local')->copy($workFile, $cloudFile);
        
        Cloud::create([
            'name' => $work->name,
            'vk_id' => session('id'),
            'track_id' => $track_id,
            'count' => $work->count,
            'type_id' => $work->WorkType->id,
        ]);

        return redirect()->route('cloud.index')->with('success', 'Сохранено');
    }

     // Сохранение шаблона
     public function sampleStore($id)
     {
         $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
 
         if (session('access') < time()) return redirect()->route('cloud.index')->with('pay', 'Вам недоступна эта функция');
 
         switch (session('package')) {
             case null:
                 return redirect()->route('cloud.index')->with('error', 'Вам недоступна эта функция');
                 break;
             case 1:
                 $max_added = 5;
                 break;
             case 2:
                 $max_added = 10;
                 break;
             case 3:
                 $max_added = 20;
                 break;
             case 4:
                 $max_added = 50;
                 break;
         }
 
         $lists = Cloud::where('vk_id', '=', session('id'))->count();
 
         if ($lists >= $max_added) {
             return redirect()->route('cloud.index')->withInput()->with('pay', 'В вашем тарифе вы хранить максимум '.$max_added.' списков.');
             die;
         }
 
         foreach($sample->getWorks as $work){

         }

         $track_id = time();
         $sampleFile = self::SAMPLE.'/'.$work['data_to'];
         $cloudFile = 'cloud/' . session('id') . '_' . $track_id . '.txt';
 
         Storage::disk('local')->copy($sampleFile, $cloudFile);
         
         Cloud::create([
             'name' => $sample->name,
             'vk_id' => session('id'),
             'track_id' => $track_id,
             'count' => $work->count,
             'type_id' => $work->WorkType->id,
         ]);
 
         return redirect()->route('cloud.index')->with('success', 'Сохранено');
     }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'data' => 'required',
        ]);

        if (session('access') < time()) return redirect()->route('cloud.index')->with('pay', 'Вам недоступна эта функция');

        switch (session('package')) {
            case null:
                return redirect()->route('cloud.index')->with('error', 'Вам недоступна эта функция');
                break;
            case 1:
                $max_added = 5;
                break;
            case 2:
                $max_added = 10;
                break;
            case 3:
                $max_added = 20;
                break;
            case 4:
                $max_added = 50;
                break;
        }

        $lists = Cloud::where('vk_id', '=', session('id'))->count();

        if ($lists >= $max_added) {
            return redirect()->route('cloud.index')->withInput()->with('pay', 'В вашем тарифе вы хранить максимум '.$max_added.' списков.');
            die;
        }

        $track_id = time();

        $path = 'cloud/' . session('id') . '_' . $track_id . '.txt';
        $data = $request->get('data');
        Storage::disk('local')->put($path, $data);

        $data = explode("\r\n", $data);
        
        Cloud::create([
            'name' => $request->name,
            'vk_id' => session('id'),
            'track_id' => $track_id,
            'count' => count($data),
            'type_id' => $request->type,
        ]);

        return redirect()->route('cloud.index')->with('success', 'Список успешно создан');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Cloud::where('vk_id', '=', session('id'))->where('track_id', '=', $id)->first();
        $name = $data->name;

        $path = 'cloud/' . session('id') . '_' . $id . '.txt';
        $data = Storage::disk('local')->get($path);
        $data = explode("\r\n", $data);
        $allCount = count($data);

        return view('cloud.show', ['name' => $name, 'id' => $id, 'allCount' => $allCount]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cloud = Cloud::where('vk_id', '=', session('id'))->where('track_id', '=', $id)->first();

        $path = 'cloud/' . session('id') . '_' . $id . '.txt';
        $data = Storage::disk('local')->get($path);
        $data = explode("\r\n", $data);

        return view('cloud.edit', ['id' => $id, 'data' => $data, 'name' => $cloud->name]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required',
            'data' => 'required',
        ]);

        $path = 'cloud/' . session('id') . '_' . $id . '.txt';

        $data = $request->get('data');
        Storage::disk('local')->put($path, $data);

        $data = explode("\r\n", $data);

        Cloud::where('vk_id', '=', session('id'))
            ->where('track_id', '=', $id)
            ->update([
                'name' => $request->name,
                'count' => count($data)
            ]);
            
        return redirect()->route('cloud.index')->with('success', 'Список успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Storage::disk('local')->delete('cloud/'. session('id') . '_' . $id . '.txt');

        Cloud::where('vk_id', '=', session('id'))->where('track_id', '=', $id)->delete();

        return redirect()->route('cloud.index')->with('success', 'Список удален');
   
    }

    public function showusers(Request $request)
    {
        $path = 'cloud/' . session('id') . '_' . $request->id . '.txt';
        $users = Storage::disk('local')->get($path);
        $users = explode("\n", $users);
        $count = $request->count;

        // Убираем лишние символы
        $users = Functions::clearUserName($users);

        // Разбиваем массив на части
        $users = array_slice($users, 0, $count);

        $ids = implode(",", $users);
        $ids = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($ids));

        $request_params = array(
            'v'            => '5.126',
            'fields'       => 'photo_50',
            'user_ids'     => $ids,
            'count'        => 300,
            'fields'       => 'status, photo_50',
            'access_token' => session('token')
        );

        $result = Functions::vkapi('users.get', $request_params);
        $users = Cloud::where('vk_id', '=', session('id'))->where('track_id', '=', $request->id)->get()->toArray();
        $allCount = $users[0]['count'];
        $name = $users[0]['name'];

        return view('cloud.show', [
                'data' => $result, 
                'id' => $request->id, 
                'allCount' => $allCount,
                'count' => $request->count,
                'name' => $name,
            ]);
    }

    public function showusersdelete(Request $request)
    {
        $path = 'cloud/' . session('id') . '_' . $request->id . '.txt';
        $users = Storage::disk('local')->get($path);
        $users = explode("\n", $users);

        $deleteUsers = array_slice($users, 0, $request->count);
        $users = array_diff($users, $deleteUsers);
        $count = count($users);

        $users = implode("\n", $users);
        Storage::disk('local')->put($path, $users);

        //black list
        $deleteUsers = implode("\n", $deleteUsers);
        $path = 'cloud/' . session('id') . '_blacklist.txt';
        Storage::disk('local')->append($path, $deleteUsers);


        $lists = Cloud::where('vk_id', '=', session('id'))->where('track_id', '=', $request->id)->update(['count' => $count]);
        $lists = Cloud::where('vk_id', '=', session('id'))->get()->toArray();

        $data = Cloud::where('vk_id', '=', session('id'))->where('track_id', '=', $request->id)->first();
        $name = $data->name;
        $allCount = $data->count;

        return view('cloud.show', ['name' => $name, 'id' => $request->id, 'allCount' => $allCount]);
    }


    public function cloudshowusers()
    {
        $data = Cloud::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get(); 

        $temp = [];
        foreach($data as $value){
            if($value->WorkType->type_desc == 'users'){
                $temp[] = $value;
            }
        }
        return view('cloud.index', ['data' => $temp]);
    }

    public function cloudshowgroups()
    {
        $data = Cloud::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get(); 

        $temp = [];
        foreach($data as $value){
            if($value->WorkType->type_desc == 'groups'){
                $temp[] = $value;
            }
        }
        return view('cloud.index', ['data' => $temp]);
    }

    public function cloudshowposts()
    {
        $data = Cloud::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get(); 

        $temp = [];
        foreach($data as $value){
            if($value->WorkType->type_desc == 'posts'){
                $temp[] = $value;
            }
        }
        return view('cloud.index', ['data' => $temp]);
    }
}
