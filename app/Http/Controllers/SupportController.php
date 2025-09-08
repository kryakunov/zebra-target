<?php

namespace App\Http\Controllers;

use App\support;
use App\Topic;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Topic::where('vk_id', '=', session('id'))->orderBy('id', 'desc')->get()->toArray();
        return view('support', ['data' => $data]);
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
        $this->validate($request, [
            'question' => 'required',
            'topic' => 'required',
        ]);

        $topic = Topic::create([
            'status' => false,
            'vk_id' => session('id'),
            'topic' => $request->get('topic'),
        ]);

        support::create([
            'topic_id' => $topic->id,
            'role' => 0,
            'text' => $request->get('question'),
        ]);

        return redirect()->route('support')->with('success', 'Ваш вопрос успешно отправлен');
    }

    public function questionStore($id, Request $request)
    {        

        $this->validate($request, [
            'question' => 'required',
        ]);

        support::create([
            'topic_id' => $id,
            'role' => 0,
            'text' => $request->get('question'),
        ]);

        return redirect()->route('supportShow', $id);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $topic = Topic::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        if (!$topic) {
            return redirect()->back()->with('error', 'Не найдено');
        }

        return view('supportShow', ['item' => $topic]);
    }

    public function supportReply(Request $request, $id)
    {

        $this->validate($request, [
            'reply' => 'required'
        ]);

        support::create([
            'topic_id' => $id,
            'role' => 1,
            'text' => $request->get('reply'),
        ]);

        if ($request->get('closed') == 1){

            $topic = Topic::where('id', '=', $id)->first();

            $topic->update([
                'status' => true,
            ]);
        }

        return redirect()->route('admin2')->with('success', 'Успешно');
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
    public function destroy($id)
    {
        //
    }
}
