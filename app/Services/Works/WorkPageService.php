<?php

namespace App\Services\Works;

use App\Cloud;
use App\WorkType;
use App\mywork;
use Illuminate\Support\Collection;

class WorkPageService
{
    /**
     * @param string $uri
     * @return WorkType|null
     */
    public function findByUri($uri)
    {
        return WorkType::where('type', '=', $uri)->first();
    }

    /**
     * @param WorkType $page
     * @return Collection
     */
    public function getMyWorks(WorkType $page)
    {
        $works = mywork::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        return $works->filter(function ($value) use ($page) {
            return $value->WorkType->type_desc == $page->type_input;
        });
    }

    /**
     * @param WorkType $page
     * @return Collection
     */
    public function getMyLists(WorkType $page)
    {
        $lists = Cloud::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->get();

        return $lists->filter(function ($value) use ($page) {
            return $value->WorkType->type_desc == $page->type_input;
        });
    }

    /**
     * @param WorkType $page
     * @param mixed $sourceWork
     * @return array<string, mixed>
     */
    public function viewData(WorkType $page, $sourceWork = null)
    {
        if ($sourceWork) {
            return [
                'page' => $page,
                'work' => $sourceWork,
            ];
        }

        return [
            'page' => $page,
            'work' => $sourceWork,
            'works' => $this->getMyWorks($page),
            'lists' => $this->getMyLists($page),
        ];
    }
}
