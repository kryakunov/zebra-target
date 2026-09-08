<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\mywork;
use App\Chain;
use App\WorkType;
use App\ChainWorks;
use App\Services\Works\WorkCreationService;
use App\Services\Works\WorkPageService;

class ChainController extends Controller
{

    public function index()
    {
        $chains = Chain::all();

        foreach($chains as &$chain)
        {
            $chain['desc'] = $this->getChain($chain['id']);
            $chain['desc'] = '<span class=chain-test>' . implode('</span> > <span class="chain-test">', $chain['desc']) . '</span>';
        }

        return view('chains.index', ['chains' => $chains]);
    }

    public function show($id, WorkPageService $pages)
    {
        $chain = Chain::where('id', '=', $id)->first();

        $steps = $this->getChainModels($chain->id);

        $page = $pages->findByUri($steps[0]['type']);

        return view('chains.show', [
            'data' => $chain,
            'steps' => $steps,
            'works' => $pages->getMyWorks($page),
            'lists' => $pages->getMyLists($page),
        ]);
    }

    // Сохраняет цепочку задач 

    public function storeChain($id)
    {

        $work = mywork::where('id', '=', $id)->where('vk_id', '=', session('id'))->first();

        $worksChain = $this->getWorksChain($id);


       // $chain = ChainWorks::create

        $chainId = Chain::create([
            'name' => 'Без названия',
            'vk_id' => session('id'),
            'likes' => 0,
            'views' => 0,
        ]);


        foreach($worksChain as $work)
        {
            ChainWorks::create([
                'type_id' => $work->type_id,
                'chain_id' => $chainId->id,
                'request' => $work->request,
            ]);
        }

        return redirect()->route('chains')->with('success', 'Сохранено');
    }


    public function getChain($id)
    {
        $chain = ChainWorks::where('chain_id', '=', $id)->get();

        $works = [];
        foreach ($chain as $work) 
        {
            $work = WorkType::where('id', '=', $work->type_id)->first();
            $works[] = $work->type_name;
        }

        return $works;
    }



    public function getChainModels($id)
    {
        $chain = ChainWorks::where('chain_id', '=', $id)->get();

        $works = [];
        foreach ($chain as $work) 
        {
            $workType = WorkType::where('id', '=', $work->type_id)->first();
            $workType['request'] = $work->request;
           // $request = ChainWork::where('chain_id', '=', $work->)
            $works[] = $workType;
        }

        return $works;
    }


    public function getWorksChain($id)
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

        return array_reverse($worksChain);
    }


    // Отдает всю цепочку задач

    public function getChainById($id)
    {
        $work = mywork::where('id', '=', $id)->where('vk_id', '=', session('id'))->first();

        if (!$work or !$work->parent_id) return redirect()->route('myworks')->with('error', 'Ошибка');
        
        $worksChain[] = $work->type_id;
        $parentId = $work->parent_id;
        
        // Сперва находим все предыдущие задачи
        do {
            $work = mywork::where('vk_id', '=', session('id'))->where('id', '=', $parentId)->first();
            if (!$work) break;
            $parentId = $work->parent_id;
            array_push($worksChain, $work->type_id);

        } while($work->parent_id !== null);

        $lastItem = $worksChain[0];

        $worksChain = implode('_', array_reverse($worksChain));
        
        return $worksChain;
    }


    public function run(Request $request, $id, WorkCreationService $workCreation)
    {
        $chain = Chain::where('id', '=', $id)->first();

        $works = $this->getChainModels($chain->id);
        $parentId = null;

        foreach ($works as $work) {
            $req = unserialize($work->request);
            foreach ($req as $key => $value) {
                $request[$key] = $value;
            }
            $request['parent_id'] = $parentId;

            $result = $workCreation->create($request, $work->type, true);
            $parentId = $result->workId;
        }
    }
}
