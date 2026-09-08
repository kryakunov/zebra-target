<?php

namespace App\Services\Works;

use App\Cloud;
use App\Functions;
use App\WorkType;
use App\mywork;
use App\stream;
use App\Http\Exec\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkCreationService
{
    /** @var int */
    public $time;

    /** @var int|null */
    public $type;

    /** @var string|null */
    public $execScriptName;

    /** @var mixed */
    public $parentId;

    /** @var int */
    public $streams = 1;

    /** @var int */
    public $membersCount = 0;

    /** @var mixed */
    public $fromWork = null;

    /** @var string|null */
    public $requestPayload;

    /** @var int|null */
    public $workId;

    public function __construct()
    {
        $this->time = time();
    }

    /**
     * @param Request $request
     * @param string|null $route
     * @param bool|null $fromChain
     * @return WorkCreationResult
     */
    public function create(Request $request, $route = null, $fromChain = null)
    {
        $page = WorkType::where('type', '=', $route)->first();

        if (!$page) {
            return WorkCreationResult::fail('Страница не найдена');
        }

        $this->type = $page->id;
        $this->execScriptName = 'exec' . $page->type;
        $this->parentId = $request->get('parentId');

        $data = null;
        $file = null;

        if ($request->data_from == 'form') {
            $prepared = $this->prepareFormData($request, $page);
            if ($prepared instanceof WorkCreationResult) {
                return $prepared;
            }
            $data = $prepared;
        } elseif ($request->data_from == 'works') {
            $source = mywork::where('vk_id', '=', session('id'))->where('id', '=', $request->works)->first();
            if (!$source) {
                return WorkCreationResult::fail('Исходные данные не найдены');
            }
            $file = 'works/' . $source['vk_id'] . '_' . $source['date'] . '.txt';
        } elseif ($request->data_from == 'lists') {
            $source = Cloud::where('vk_id', '=', session('id'))->where('id', '=', $request->lists)->first();
            if (!$source) {
                return WorkCreationResult::fail('Исходные данные не найдены');
            }
            $file = 'cloud/' . $source['vk_id'] . '_' . $source['track_id'] . '.txt';
        }

        if ($this->parentId) {
            $parentResult = $this->applyParentWork($page, $file, $data);
            if ($parentResult instanceof WorkCreationResult) {
                return $parentResult;
            }
            $file = $parentResult['file'];
            $data = $parentResult['data'];
        }

        $newFile = 'sourceworks/' . session('id') . '_' . $this->time . '.txt';

        if (isset($file)) {
            Storage::disk('local')->copy($file, $newFile);
        } elseif (!isset($file) && isset($data)) {
            $data = implode("\n", $data);
            Storage::disk('local')->put($newFile, $data);
        }

        $this->setRequest($request->all());
        $this->streams = 1;

        $name = $request->get('name') ? $request->get('name') : 'Без названия | ' . $page->type_name;

        $this->createWork($name);

        if ($fromChain === true) {
            return WorkCreationResult::success($this->workId);
        }

        $this->createStream($this->streams);

        return WorkCreationResult::success($this->workId);
    }

    /**
     * @param Request $request
     * @param WorkType $page
     * @return array|WorkCreationResult
     */
    private function prepareFormData(Request $request, WorkType $page)
    {
        $data = explode("\r\n", $request->form);
        $data = array_unique($data);

        if ($page->type_input == 'users') {
            $data = Functions::clearUserName($data);
            if (count($data) < 300) {
                $data = Functions::getUserIds($data);
            }
        } elseif ($page->type_input == 'groups') {
            $data = Functions::clearGroupName($data);
            $count = count($data);
            if ($count < 25) {
                $ids = implode(',', $data);
                $data = json_decode(file_get_contents('https://api.vk.ru/method/execute.getGroupsId?ids=' . $ids . '&count=' . $count . '&access_token=' . session('token') . '&v=5.131'), true);
                if (!$data['response']) {
                    return WorkCreationResult::fail('Проверьте правильность введенных ID');
                }
                $data = $data['response'];
            }
        }

        return $data;
    }

    /**
     * @param WorkType $page
     * @param string|null $file
     * @param mixed $data
     * @return array<string, mixed>|WorkCreationResult
     */
    private function applyParentWork(WorkType $page, $file, $data)
    {
        $work = mywork::where('id', '=', $this->parentId)->where('vk_id', '=', session('id'))->first();

        if (!$work) {
            $work = mywork::where('id', '=', $this->parentId)->where('share', '=', 1)->first();
            $this->parentId = null;
        }

        if (!$work) {
            return WorkCreationResult::fail('Исходные данные не найдены');
        }

        $file = 'works/' . $work['vk_id'] . '_' . $work['date'] . '.txt';

        if ($work->vk_id == session('id')) {
            if (!Functions::isFullAccess()) {
                if ($page->type_desc == 'users') {
                    $data = $this->readLine($file, 50);
                } elseif ($page->type_desc == 'groups') {
                    $data = $this->readLine($file, 15);
                } elseif ($page->type_desc == 'posts') {
                    $data = $this->readLine($file, 15);
                }
            }
        }

        return [
            'file' => $file,
            'data' => $data,
        ];
    }

    /**
     * @param array $request
     * @return void
     */
    private function setRequest($request)
    {
        unset($request['_token']);
        unset($request['groups']);
        unset($request['users']);
        unset($request['posts']);
        unset($request['works']);
        unset($request['lists']);
        unset($request['form']);

        $this->requestPayload = serialize($request);
    }

    /**
     * @param string $name
     * @return int
     */
    private function createWork($name)
    {
        $work = mywork::create([
            'vk_id' => session('id'),
            'status' => 0,
            'percent' => 0,
            'name' => $name,
            'parent_id' => $this->parentId,
            'type_id' => $this->type,
            'date' => $this->time,
            'date_start' => $this->time,
            'streams' => $this->streams,
            'source_count' => $this->membersCount,
            'from_work' => $this->fromWork,
            'request' => $this->requestPayload,
        ]);

        $this->workId = $work['id'];

        return $work['id'];
    }

    /**
     * @param int $streams
     * @return void
     */
    private function createStream($streams)
    {
        for ($i = 0; $i < $streams; $i++) {
            $stream = stream::create([
                'mywork_id' => $this->workId,
                'status' => 0,
                'percent' => 0,
                'stream_id' => $i,
                'pid' => 0,
            ]);

            $this->createExec($stream['id']);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    private function createExec($id)
    {
        $logPath = 'logs/' . $this->workId . '-' . $id . '.txt ';
        $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Exec/' . $this->execScriptName . '.php ' . escapeshellarg($id);

        $process = new Process($command, $logPath);
        $pid = $process->getPid();
        stream::where('id', '=', $id)->update(['pid' => $pid]);
    }

    /**
     * @param string $file
     * @param int $amount
     * @return array
     */
    private function readLine($file, $amount)
    {
        $i = 0;
        $data = [];
        $file = '../storage/app/' . $file;

        $res = $this->detectLineEnding($file);

        if ($handle = fopen($file, 'r')) {
            while (!feof($handle)) {
                $line = fgets($handle);
                $line = str_replace($res, '', $line);
                $data[] = $line;
                if (++$i >= $amount) {
                    break;
                }
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * @param string $file
     * @return string
     */
    private function detectLineEnding($file)
    {
        $handleFile = fopen($file, 'r');
        $line = fgets($handleFile);
        fclose($handleFile);

        if (strpos($line, "\r\n") !== false) {
            return "\r\n";
        }

        return "\n";
    }
}
