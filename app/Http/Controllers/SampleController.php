<?php

namespace App\Http\Controllers;

use App\Sample;
use App\sample_work;
use App\mywork;
use App\Cloud;
use App\WorkType;
use Illuminate\Http\Request;
use App\Http\Exec\Process;

class SampleController extends Controller
{

    public function index()
    {
        $samples = Sample::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        return view('samples.index', ['data' => $samples]);
    }

    public function sampleshow($id)
    {
        $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $works = mywork::where('vk_id', '=', session('id'))->get();

        $lists = Cloud::where('vk_id', '=', session('id'))->get();

       // if (!$sample) return redirect()->route('myworks')->with('error', 'Не найдено');
        $dataFrom = $sample->getWorks->first()->data_from;

        $dataFrom = explode('_', $dataFrom);
  
        $source = null;

        if ($dataFrom[0] == 0){
            $source = mywork::where('id', '=', $dataFrom[1])->first();
        }elseif ($dataFrom[0] == 1){
            $source = Cloud::where('id', '=', $dataFrom[1])->first();
        }

        return view('samples.show', ['data' => $sample, 'works' => $works, 'lists' => $lists, 'source' => $source]);

    }

    public function sharesample($id)
    {
        $data = Sample::select('shared_users', 'share')->where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $share = $data->share;
        $data = $data->shared_users; 
        $data = unserialize($data);

        return view('samples.sharesample', ['id' => $id, 'share' => $share, 'data' => $data]);
    }

    public function sharesamplesave(Request $request)
    {
        if (isset($request->share)) $share = 1; else $share = null;

        $users = $request->get('data');
        $users = serialize(explode("\r\n", $users));
        
        Sample::where('vk_id', '=', session('id'))->where('id', '=', $request->get('id'))->update(['shared_users' => $users, 'share' => $share]);

        return redirect()->route('mysamples')->with('success', 'Успешно');
    }

    public function sharedsampleshow($id)
    {
        $sample = Sample::where('id', '=', $id)->where('share', '=', 1)->first();
        if (!$sample) return redirect()->route('myworks')->with('error', 'Шаблон не найден либо закрыт для просмотра');

        $users = unserialize($sample->shared_users);
        if (!in_array(session('id'), $users)) dd('no');

  
        return view('samples.sharedsampleshow', ['data' => $sample]);
   
    }

    public function savesamplerequest(Request $request)
    {
        $id = $request['id'];
        unset($request['id']);
        unset($request['_token']);

        $work = sample_work::where('id', '=', $id)->update(['request' => serialize($request->all())]);

        return redirect()->back()->with('success', 'Успешно');
    }

    


    public function SaveSampleTimer(Request $request, $id)
    {
        if (isset($request->status)) $status = 1; else $status = 0;

        Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->update([
            'status' => $status,
            'timer' => $request->timer,
        ]);

        return redirect()->back()->with('success', 'Успешно');
    }

    public function deletestep($id)
    {
        $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        $sample->getWorks->first()->delete();

        return redirect()->back()->with('success', 'Успешно');
    }


    public function savesampledatafrom(Request $request)
    {


        if($request->data_from == 0){
            $dataFrom = $request->data_from.'_'.$request->works;
        }elseif($request->data_from == 1){
            $dataFrom = $request->data_from.'_'.$request->lists;
        }elseif($request->data_from == 2){
            $data = $request->data;
        }

        $sample = Sample::where('id', '=', $request->id)->where('vk_id', '=', session('id'))->first();
        
        if(!$sample)
            return redirect()->back()->with('error', 'Ошибка');

        $sample->getWorks->first()->update(['data_from' => $dataFrom]);

        //$filename = $sample->id.'_start.txt';

       //Storage::copy($oldfile, 'samples/'.$filename);


        return redirect()->back()->with('success', 'Сохранено');
    }

    

    public function sampledelete($id)
    {
        $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        if (!$sample) dd('not found');
        

        // Удаляем streams
        sample_work::where('sample_id', '=', $sample['id'])->delete();

        // Удаляем саму задачу
        $sample->delete();

        return redirect()->route('mysamples')->with('success', 'Удалено');
    }

    public function createsample($uri = null)
    {

        $works = WorkType::all();

        if (!$uri)
            return view('samples.create', ['works' => $works]);


        $workType = WorkType::where('type', 'like', '%'.$uri.'%')->first();

        dd($workType);
    }

    public function create(Request $request, $id)
    {
        $worksChain = [];

        $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        if (!$work) return redirect()->route('myworks')->with('error', 'Не найдено');
        $parentId = $work->parent_id;
        
        ($request->name == '') ? $request->name = 'Без названия' : $request->name = $request->name;

        $worksChain[] = $work;
        
        $sample = Sample::create([
            'name' => $request->name,
            'vk_id' => session('id'),
            'views' => 0,
            'status' => 0,
        ]);

        // Сперва находим все предыдущие задачи
        do {
            $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $parentId)->first();
            if (!$work) break;
            $parentId = $work->parent_id;

            $worksChain[] = $work;

        } while($work->parent_id !== null);

        $worksChain = array_reverse($worksChain);

        $data_from = null;

        foreach($worksChain as $work)
        {
            $result = sample_work::create([
                'sample_id' => $sample->id,
                'type_id' => $work->WorkType->id,
                'name' => $work->name,
                'data_from' => $data_from,
                'request' => $work->request,
            ]);

            $data_from = 'file-' . $result['id'] . '.txt';
            $result->update(['data_to' => $data_from]);
        }
        
        return redirect()->route('mysamples');
    }


    public function SampleDownload($id)
    {
        $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();

        foreach($sample->getWorks as $work){
            
        }

        $file = '../storage/app/samples/'.$work['data_to'];

        return response()->download($file, $sample->name);   
    }

    public function SampleStoreInCloud($id)
    {
        dd($id);
    }

    public function startCron($id = null)
    {
        $samples = Sample::where('status', '=', '1')->get();

        // Если передан id конкретного шаблона, то берем задачи из него
        if ($id)  $samples = Sample::where('id', '=', $id)->get();


        foreach($samples as $sample)
        {

            $sample->update(['state' => 'Выполняется']);

            // Берем первую задачу
            foreach($sample->getWorks as $work)
            {
                if ($work->status !== 'wait') continue;

                // Запускаем задачу
                $logs = 'logs/'. $work->id . '-'. $id .'.txt ';
        
                $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Execute/'.$work->WorkType->type.'.php '.escapeshellarg($work['id']);
                $process = new Process($command, $logs);
                $pid = $process->getPid();
        
                sample_work::where('id', '=', $work['id'])->update([
                    'pid' => $pid,
                    'percent' => 1,
                    'error' => null,
                ]);

                break;
            }
             
        }



    }
}
