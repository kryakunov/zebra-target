<?php

namespace App\Http\Controllers;

use App\Cloud;
use App\Functions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class toProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $lists = Cloud::where('vk_id', '=', session('id'))->get()->toArray();
        
        if (count($lists) == 0) {
            return redirect()->back()->with('error', 'У вас нет списков в облаке');
        }

        return view('toprofile', ['lists' => $lists]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $path = 'cloud/' . session('id') . '_' . $request->list . '.txt';
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
            'access_token' => session('token')
        );

        $result = Functions::vkapi('users.get', $request_params);
        $lists = Cloud::where('vk_id', '=', session('id'))->get()->toArray();

        return view('toprofile', ['data' => $result, 'lists' => $lists, 'sid' => $request->list, 'count' => $count]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
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

        return view('toprofile', ['lists' => $lists, 'sid' => $request->id]);
    }

    public function getBlackList(){

        $path = 'cloud/' . session('id') . '_blacklist.txt';

        if (Storage::disk('local')->exists($path)) {

            $users = Storage::disk('local')->get($path);
            $users = explode("\r\n", $users);
            return view('cloud.getblacklist', ['users' => $users]);
  
        } 

        return view('cloud.getblacklist', ['users' => []]);


   }

}
