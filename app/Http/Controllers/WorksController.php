<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkRequest;
use App\Services\Works\WorkCreationService;
use App\Services\Works\WorkPageService;
use App\Services\Works\WorkProcessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WorksController extends ExecController
{
    /** @var WorkPageService */
    private $pages;

    /** @var WorkCreationService */
    private $creator;

    /** @var WorkProcessService */
    private $processes;

    public function __construct(WorkPageService $pages, WorkCreationService $creator, WorkProcessService $processes)
    {
        $this->time = time();
        $this->pages = $pages;
        $this->creator = $creator;
        $this->processes = $processes;
    }

    /**
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show()
    {
        $page = $this->pages->findByUri(Route::current()->uri());

        if (!$page) {
            abort(404);
        }

        if (!$this->isVkAuthenticated()) {
            return view('forms.layout', ['page' => $page]);
        }

        return view('forms.layout', $this->pages->viewData($page, $this->getWork()));
    }

    /**
     * @param StoreWorkRequest $request
     * @param string|null $route
     * @param bool|null $fromChain
     * @return \Illuminate\Http\RedirectResponse|int
     */
    public function handler(StoreWorkRequest $request, $route = null, $fromChain = null)
    {
        if (!$route) {
            $route = Route::current()->uri();
        }

        $result = $this->creator->create($request, $route, $fromChain);

        if (!$result->ok) {
            return redirect()->back()->with('error', $result->error)->withInput();
        }

        if ($fromChain === true) {
            return $result->workId;
        }

        return redirect()->route('myworks')->with('success', 'Задача успешно добавлена в работу');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function WorkRun($id)
    {
        $error = $this->processes->rerun($id);

        if ($error === 'not found ID') {
            dd('not found ID');
        }

        return redirect()->back()->with('success', 'Успешно');
    }

    /**
     * @return void
     */
    public function SampleRunCron()
    {
        echo $this->processes->restartStuckSamples();
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function SampleRun($id)
    {
        $error = $this->processes->runSample($id);

        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        return redirect()->back()->with('success', 'Шаблон запущен');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function SaveWorkTimer(Request $request, $id)
    {
        $this->processes->saveTimer($request, $id);

        return redirect()->back()->with('success', 'Успешно');
    }

    /**
     * @return void
     */
    public function cronStartWorks()
    {
        dd($this->processes->cronStartWorks());
    }
}
