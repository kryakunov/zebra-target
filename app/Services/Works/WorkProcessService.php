<?php

namespace App\Services\Works;

use App\Http\Exec\Process;
use App\Sample;
use App\sample_work;
use App\stream;
use App\mywork;
use Illuminate\Http\Request;

class WorkProcessService
{
    /**
     * @param int $id
     * @return string|null
     */
    public function rerun($id)
    {
        if (!$id) {
            return 'not found ID';
        }

        $work = mywork::where('id', '=', $id)->first();
        $file = '../storage/app/works/' . $work['vk_id'] . '_' . $work['date'] . '.txt';
        $dateStart = time();

        $unlink = false;
        foreach ($work->stream as $val) {
            if ($val->last_id != null) {
                $unlink = true;
            }
        }

        if (file_exists($file) && !$unlink) {
            unlink($file);
        }

        $file = '../storage/app/sourceworks/' . $work['vk_id'] . '_' . $work['date'] . '.txt';
        $data = file_get_contents($file);

        $streams = stream::where('mywork_id', '=', $id)->get()->toArray();

        mywork::where('id', '=', $id)->update([
            'date_start' => $dateStart,
            'status' => 0,
            'percent' => 1,
            'error' => null,
        ]);

        foreach ($streams as $item) {
            $logs = 'logs/' . $work->id . '-' . $id . '.txt ';
            $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Exec/exec' . $work->WorkType->type . '.php ' . escapeshellarg($item['id']);
            $process = new Process($command, $logs);
            $pid = $process->getPid();

            stream::where('id', '=', $item['id'])->update([
                'pid' => $pid,
                'percent' => 1,
                'error' => null,
                'status' => 0,
            ]);
        }

        return null;
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function runSample($id)
    {
        $sample = Sample::where('vk_id', '=', session('id'))->where('id', '=', $id)->first();
        $sample->update(['state' => 'Выполняется', 'date_start' => time(), 'date_end' => null]);

        $work = $sample->getWorks->first();

        if (!isset($work->data_from)) {
            return 'Укажите исходные данные для шаблона';
        }

        foreach ($sample->getWorks as $item) {
            $item->update([
                'status' => 'wait',
                'state' => 'Ожидает запуска',
                'percent' => 0,
                'error' => null,
                'count' => 0,
                'date_start' => null,
                'date_end' => null,
            ]);
        }

        $work = $sample->getWorks->first();
        $logs = 'logs/semyon.txt ';
        $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Execute/' . $work->WorkType->type . '.php ' . escapeshellarg($work['id']);
        $process = new Process($command, $logs);
        $pid = $process->getPid();

        sample_work::where('id', '=', $work['id'])->update([
            'pid' => $pid,
            'percent' => 1,
            'error' => null,
        ]);

        return null;
    }

    /**
     * @return string
     */
    public function restartStuckSamples()
    {
        $samples = sample_work::where('percent', '>', 0)->where('percent', '<', 100)->get();
        $output = '';

        foreach ($samples as $sample) {
            $status = Process::checkStatusById($sample->pid);

            if ($status) {
                continue;
            }

            if (!isset($sample->data_from)) {
                continue;
            }

            $logs = 'logs/sample-id-' . $sample->id . '.txt ';
            $command = '/usr/local/php/php-8.0/bin/php ../app/Http/Execute/' . $sample->WorkType->type . '.php ' . escapeshellarg($sample->id);
            $process = new Process($command, $logs);
            $pid = $process->getPid();

            sample_work::where('id', '=', $sample->id)->update([
                'pid' => $pid,
            ]);

            $output .= 'Шаблон ' . $sample->name . ' запущен. pid: ' . $pid . '<br>';
        }

        return $output . 'end';
    }

    /**
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function saveTimer(Request $request, $id)
    {
        $status = isset($request->timer) ? 1 : 0;

        mywork::where('vk_id', '=', session('id'))->where('id', '=', $id)->update([
            'timer' => $status,
            'timer_count' => $request->timer_count,
        ]);
    }

    /**
     * @return string
     */
    public function cronStartWorks()
    {
        $works = mywork::where('timer', '=', 1)->get();

        $file = '../storage/app/cronworkslog/cronstartworks.txt';
        if (!file_exists($file)) {
            file_put_contents($file, '');
        }

        foreach ($works as $work) {
            if ($work->user->access < time()) {
                continue;
            }

            $passedTime = time() - $work->date_start;
            $days = 86400 * $work->timer_count;

            if ($passedTime < $days) {
                continue;
            }

            $this->rerun($work->id);

            $msg = '[' . date('d.m.y H:i', time()) . '] Задача ' . $work->id . ' запущена';
            file_put_contents($file, $msg . "\n", FILE_APPEND);
        }

        return file_get_contents($file);
    }
}
