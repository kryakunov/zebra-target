<?php


namespace App\Http\Controllers;


use App\mywork;
use App\Functions;
use Illuminate\Http\Request;

class FilterGroupsController extends ExecController
{

	public function __construct()
    {
        $this->time = time();
        $this->type = 4;
        $this->execScriptName = 'execFilterGroups';
    } 


    public function show() 
	{
        $work = $this->getWork();

		if ($work) {
			return view('filtergroups', ['work' => $work]);
		}
        
        return view('filtergroups');
    }

    public function store(Request $request)
	{
        $this->parentId = $request->get('parentId');
        $this->workType = $request->get('workType');

        if (!($this->parentId)) {
            $this->validate($request, [
                'groups' => 'required'
            ]);

            $groups = explode("\r\n", $request['groups']);

        } else {
        
            $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();
            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';
            
            // Для расшаренных задач
            if (!$work) {
                $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
                $this->parentId = null;
            }
            
            $file = '../storage/app/works/'.$work['vk_id'].'_'.$work['date'] . '.txt';

            if (!file_exists($file)) return redirect()->back()->with('error', 'Исходные данные не найдены')->withInput();

            // Здесь находим какой разделитель в файле \n or \r\n
            $res = $this->checkRNnew($file);

            // Если это моя задача и у меня бесплатный доступ, то обрезать результат
            if ($work->vk_id == session('id'))
            if (!Functions::isFullAccess())
            { 
                $groups = $this->readLine($file, 15);
            } else $groups = file_get_contents($file);
            
            $groups = explode($res, $groups);
        }
 
        $groups = array_unique($groups);

		$this->sourceFile = session('id') . '_' . $this->time . '.txt';
        $this->path = '../storage/app/sourceworks/' . $this->sourceFile;

        $this->streams = 1;
        $this->membersCount = count($groups);

        ($request->get('name')) ? $name = $request->get('name') : $name = 'Без названия | Фильтр сообществ';

        $this->setRequest($request->all());

        $groups = implode("\n", $groups);

        // Сохраняем данные в файл
        file_put_contents($this->path, $groups);

        // Создаем очередь
        $work = $this->createWork($name);

        // Создаем потоки
        $this->createStream($this->streams);

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
    
    }

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
    }

}
